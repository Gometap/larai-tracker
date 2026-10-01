<?php

namespace Gometap\LaraiTracker\Http\Controllers;

use Carbon\Carbon;
use Gometap\LaraiTracker\Models\LaraiBudget;
use Gometap\LaraiTracker\Models\LaraiLog;
use Gometap\LaraiTracker\Models\LaraiModelPrice;
use Gometap\LaraiTracker\Models\LaraiSetting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\Factory as ViewFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class LaraiDashboardController extends Controller
{
    public function index(Request $request)
    {
        [$startDate, $endDate, $startAt, $endAt] = $this->validatedDateRange($request);
        [$thisMonthStart, $thisMonthEnd] = $this->monthRange(now());
        [$lastMonthStart, $lastMonthEnd] = $this->monthRange(now()->subMonthNoOverflow());

        $thisMonthCost = LaraiLog::whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])->sum('cost_usd');
        $lastMonthCost = LaraiLog::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->sum('cost_usd');

        $momChangePct = $lastMonthCost > 0
            ? (($thisMonthCost - $lastMonthCost) / $lastMonthCost) * 100
            : ($thisMonthCost > 0 ? 100 : 0);

        $budget = LaraiBudget::where('is_active', true)->first();
        $budgetPct = ($budget && $budget->amount > 0)
            ? min(($thisMonthCost / (float) $budget->amount) * 100, 100)
            : null;

        $stats = [
            'total_cost' => LaraiLog::sum('cost_usd'),
            'unknown_cost_count' => LaraiLog::whereNull('cost_usd')->count(),
            'total_tokens' => LaraiLog::sum('total_tokens'),
            'today_cost' => LaraiLog::whereBetween('created_at', [today(), today()->endOfDay()])->sum('cost_usd'),
            'this_month_cost' => $thisMonthCost,
            'mom_change_pct' => $momChangePct,
            'budget' => $budget,
            'budget_pct' => $budgetPct,
            'recent_logs' => LaraiLog::latest()->limit(10)->get(),
            'costs_by_model' => LaraiLog::select('model', DB::raw('SUM(cost_usd) as cost'))
                ->whereBetween('created_at', [$startAt, $endAt])
                ->whereNotNull('cost_usd')
                ->groupBy('model')
                ->get(),
            'costs_over_time' => LaraiLog::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(cost_usd) as cost'))
                ->whereBetween('created_at', [$startAt, $endAt])
                ->whereNotNull('cost_usd')
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
            'currency_symbol' => '$',
            'currency_code' => 'USD',
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];

        return $this->render('larai::dashboard', compact('stats'));
    }

    public function chartData(Request $request)
    {
        [, , $startAt, $endAt] = $this->validatedDateRange($request);

        $costsOverTime = LaraiLog::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(cost_usd) as cost'))
            ->whereBetween('created_at', [$startAt, $endAt])
            ->whereNotNull('cost_usd')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $costsByModel = LaraiLog::select('model', DB::raw('SUM(cost_usd) as cost'))
            ->whereBetween('created_at', [$startAt, $endAt])
            ->whereNotNull('cost_usd')
            ->groupBy('model')
            ->get();

        return response()->json([
            'costs_over_time' => $costsOverTime,
            'costs_by_model' => $costsByModel,
            'currency_symbol' => '$',
            'currency_code' => 'USD',
        ]);
    }

    public function logs(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'provider' => ['nullable', 'string', 'max:100'],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
            'sort' => ['nullable', Rule::in([
                'id', 'provider', 'model', 'prompt_tokens', 'completion_tokens',
                'total_tokens', 'cost_usd', 'created_at',
            ])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        if (isset($validated['start_date'], $validated['end_date'])
            && $validated['start_date'] > $validated['end_date']) {
            throw ValidationException::withMessages([
                'start_date' => 'The start date must be before or equal to the end date.',
            ]);
        }

        $query = LaraiLog::query();

        if (! empty($validated['q'])) {
            $search = $validated['q'];
            $query->where(function ($query) use ($search) {
                $query->where('model', 'like', "%{$search}%")
                    ->orWhere('provider', 'like', "%{$search}%");

                if (ctype_digit($search)) {
                    $query->orWhere('id', (int) $search);
                }
            });
        }

        if (! empty($validated['provider']) && $validated['provider'] !== 'all') {
            $query->where('provider', $validated['provider']);
        }

        if (! empty($validated['start_date'])) {
            $query->where('created_at', '>=', Carbon::createFromFormat('Y-m-d', $validated['start_date'])->startOfDay());
        }
        if (! empty($validated['end_date'])) {
            $query->where('created_at', '<=', Carbon::createFromFormat('Y-m-d', $validated['end_date'])->endOfDay());
        }

        $query->orderBy($validated['sort'] ?? 'created_at', $validated['direction'] ?? 'desc');

        $logs = $query->paginate(20)->withQueryString();
        $providers = LaraiLog::select('provider')->distinct()->pluck('provider');
        $currency_symbol = '$';

        return $this->render('larai::logs', compact('logs', 'providers', 'currency_symbol'));
    }

    public function export(string $format): StreamedResponse
    {
        abort_unless(in_array($format, ['json', 'csv', 'txt'], true), 404);

        return match ($format) {
            'json' => response()->streamDownload(function () {
                echo '[';
                $first = true;
                foreach (LaraiLog::latest()->cursor() as $log) {
                    echo $first ? '' : ',';
                    echo $log->toJson();
                    $first = false;
                }
                echo ']';
            }, 'larai_logs.json', ['Content-Type' => 'application/json; charset=UTF-8']),
            'csv' => response()->streamDownload(function () {
                $file = fopen('php://output', 'wb');
                fputcsv($file, ['ID', 'User ID', 'Provider', 'Model', 'Prompt Tokens', 'Completion Tokens', 'Total Tokens', 'Cost USD', 'Timestamp']);

                foreach (LaraiLog::latest()->cursor() as $log) {
                    fputcsv($file, [
                        $log->id,
                        $log->user_id,
                        $this->safeSpreadsheetValue($log->provider),
                        $this->safeSpreadsheetValue($log->model),
                        $log->prompt_tokens,
                        $log->completion_tokens,
                        $log->total_tokens,
                        $log->cost_usd,
                        $log->created_at?->toIso8601String(),
                    ]);
                }

                fclose($file);
            }, 'larai_logs.csv', ['Content-Type' => 'text/csv; charset=UTF-8']),
            'txt' => response()->streamDownload(function () {
                echo "Larai Tracker Log Export\n".str_repeat('=', 50)."\n\n";
                foreach (LaraiLog::latest()->cursor() as $log) {
                    $provider = $this->safePlainText(strtoupper($log->provider));
                    $model = $this->safePlainText($log->model);
                    $cost = $log->cost_usd === null ? 'unavailable' : '$'.$log->cost_usd.' USD';
                    echo "[{$log->created_at}] #{$log->id} | {$provider} | {$model} | Tokens: {$log->total_tokens} | Cost: {$cost}\n";
                }
            }, 'larai_logs.txt', ['Content-Type' => 'text/plain; charset=UTF-8']),
        };
    }

    public function settings()
    {
        $budget = LaraiBudget::first() ?? new LaraiBudget([
            'amount' => 100,
            'alert_threshold' => 80,
            'is_active' => false,
            'currency_code' => 'USD',
        ]);

        $customPrices = LaraiModelPrice::all();
        $currency = ['code' => 'USD', 'symbol' => '$'];
        $logRetentionDays = (int) LaraiSetting::get('log_retention_days', 0);

        return $this->render('larai::settings', compact('budget', 'customPrices', 'currency', 'logRetentionDays'));
    }

    public function updateSettings(Request $request)
    {
        $this->normalizeNonFiniteNumbers($request);

        $validated = $request->validate([
            'budget' => ['sometimes', 'array'],
            'budget.amount' => ['required_with:budget', 'numeric', 'min:0', 'max:99999999999'],
            'budget.threshold' => ['required_with:budget', 'integer', 'between:1,100'],
            'budget.email' => ['nullable', 'email:rfc', 'max:254'],
            'budget.active' => ['nullable', 'boolean'],
            'currency' => ['sometimes', 'array'],
            'currency.code' => ['required_with:currency', Rule::in(['USD'])],
            'currency.symbol' => ['nullable', Rule::in(['$'])],
            'prices' => ['sometimes', 'array', 'max:500'],
            'prices.*.input' => ['required', 'numeric', 'min:0', 'max:999999'],
            'prices.*.output' => ['required', 'numeric', 'min:0', 'max:999999'],
            'new_prices' => ['sometimes', 'array', 'max:100'],
            'new_prices.*.provider' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z0-9._-]+$/'],
            'new_prices.*.model' => ['required', 'string', 'max:255'],
            'new_prices.*.input' => ['required', 'numeric', 'min:0', 'max:999999'],
            'new_prices.*.output' => ['required', 'numeric', 'min:0', 'max:999999'],
            'log_retention_days' => ['sometimes', 'integer', 'between:0,3650'],
            'security' => ['sometimes', 'array'],
            'security.current_password' => ['nullable', 'string', 'max:255'],
            'security.new_password' => ['nullable', 'string', 'min:8', 'max:255', 'confirmed'],
        ]);

        $newPassword = $validated['security']['new_password'] ?? null;
        if ($newPassword !== null) {
            $currentPassword = $this->getEffectivePassword();
            $inputCurrent = $validated['security']['current_password'] ?? '';

            if ($currentPassword !== null && ($inputCurrent === '' || ! $this->verifyPassword($inputCurrent, $currentPassword))) {
                return redirect()->back()->with('password_error', 'Current password is incorrect.');
            }
        }

        DB::transaction(function () use ($request, $validated, $newPassword) {
            if (isset($validated['budget'])) {
                $budget = LaraiBudget::first() ?? new LaraiBudget;
                $budget->fill([
                    'amount' => $validated['budget']['amount'],
                    'alert_threshold' => $validated['budget']['threshold'],
                    'recipient_email' => $validated['budget']['email'] ?? null,
                    'is_active' => $request->boolean('budget.active'),
                    'currency_code' => 'USD',
                ])->save();
            }

            foreach ($validated['prices'] ?? [] as $id => $data) {
                if (ctype_digit((string) $id) && ($price = LaraiModelPrice::find((int) $id))) {
                    $price->update([
                        'input_price_per_1m' => $data['input'],
                        'output_price_per_1m' => $data['output'],
                        'is_custom' => true,
                        'source_url' => null,
                        'effective_date' => null,
                    ]);
                }
            }

            foreach ($validated['new_prices'] ?? [] as $data) {
                LaraiModelPrice::updateOrCreate(
                    [
                        'provider' => strtolower(trim($data['provider'])),
                        'model' => strtolower(trim($data['model'])),
                    ],
                    [
                        'input_price_per_1m' => $data['input'],
                        'output_price_per_1m' => $data['output'],
                        'is_custom' => true,
                        'source_url' => null,
                        'effective_date' => null,
                    ]
                );
            }

            if (array_key_exists('log_retention_days', $validated)) {
                LaraiSetting::set('log_retention_days', $validated['log_retention_days']);
            }

            LaraiSetting::set('currency_code', 'USD');
            LaraiSetting::set('currency_symbol', '$');

            if ($newPassword !== null) {
                LaraiSetting::set('dashboard_password', Hash::make($newPassword));
            }
        });

        if ($newPassword !== null) {
            return redirect()->back()->with('password_success', 'Password updated successfully.');
        }

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }

    public function deletePrice(string $id)
    {
        abort_unless(ctype_digit($id), 404);
        LaraiModelPrice::findOrFail((int) $id)->delete();

        return redirect()->route('larai.settings')->with('success', 'Price entry deleted.');
    }

    public function syncPrices()
    {
        try {
            $url = config('larai-tracker.price_catalog_url');
            if (! is_string($url) || ! str_starts_with($url, 'https://')) {
                throw new \RuntimeException('Invalid price catalog URL.');
            }

            $response = Http::acceptJson()
                ->timeout((int) config('larai-tracker.price_catalog_timeout', 5))
                ->get($url);

            if (! $response->successful()) {
                throw new \RuntimeException('Price catalog request failed.');
            }

            $catalog = $response->json();
            $validator = Validator::make(is_array($catalog) ? $catalog : [], [
                'version' => ['required', 'integer', 'min:1'],
                'source' => ['required', 'url', 'max:2048'],
                'effective_date' => ['required', 'date_format:Y-m-d'],
                'models' => ['required', 'array', 'max:1000'],
                'models.*.provider' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z0-9._-]+$/'],
                'models.*.model' => ['required', 'string', 'max:255'],
                'models.*.input_price_per_1m' => ['required', 'numeric', 'min:0', 'max:999999'],
                'models.*.output_price_per_1m' => ['required', 'numeric', 'min:0', 'max:999999'],
            ]);

            if ($validator->fails()) {
                throw new \UnexpectedValueException('Invalid price catalog.');
            }

            $data = $validator->validated();
            DB::transaction(function () use ($data) {
                foreach ($data['models'] as $item) {
                    $attributes = [
                        'provider' => strtolower($item['provider']),
                        'model' => strtolower($item['model']),
                    ];
                    $existing = LaraiModelPrice::where($attributes)->first();

                    if ($existing?->is_custom) {
                        continue;
                    }

                    LaraiModelPrice::updateOrCreate($attributes, [
                        'input_price_per_1m' => $item['input_price_per_1m'],
                        'output_price_per_1m' => $item['output_price_per_1m'],
                        'is_custom' => false,
                        'source_url' => $data['source'],
                        'effective_date' => $data['effective_date'],
                        'catalog_version' => $data['version'],
                    ]);
                }
            });

            return redirect()->back()->with('success', 'Prices synchronized successfully.');
        } catch (Throwable) {
            return redirect()->back()->with('error', 'Failed to synchronize prices. The catalog was not changed.');
        }
    }

    protected function validatedDateRange(Request $request): array
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:end_date'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
        ]);

        $startDate = $validated['start_date'] ?? now()->subDays(6)->toDateString();
        $endDate = $validated['end_date'] ?? now()->toDateString();
        $startAt = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
        $endAt = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();

        if ($startAt->diffInDays($endAt) > 366) {
            throw ValidationException::withMessages([
                'start_date' => 'The selected date range may not exceed 366 days.',
            ]);
        }

        return [$startDate, $endDate, $startAt, $endAt];
    }

    protected function monthRange(Carbon $date): array
    {
        return [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()];
    }

    protected function render(string $name, array $data)
    {
        $views = app(ViewFactory::class);

        return $views->file($views->getFinder()->find($name), $data);
    }

    protected function safeSpreadsheetValue(?string $value): string
    {
        $value ??= '';

        return preg_match('/^[\s]*[=+\-@]/u', $value) ? "'{$value}" : $value;
    }

    protected function safePlainText(?string $value): string
    {
        return trim((string) preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $value ?? ''));
    }

    protected function normalizeNonFiniteNumbers(Request $request): void
    {
        $input = $request->all();
        array_walk_recursive($input, function (&$value) {
            if (is_float($value) && ! is_finite($value)) {
                $value = 'invalid-number';
            }
        });
        $request->replace($input);
    }

    protected function getEffectivePassword(): ?string
    {
        try {
            $dbPassword = LaraiSetting::get('dashboard_password');
            if (is_string($dbPassword) && $dbPassword !== '') {
                return $dbPassword;
            }
        } catch (Throwable) {
            // The package may be used before its settings table is installed.
        }

        return config('larai-tracker.password');
    }

    protected function verifyPassword(string $input, string $stored): bool
    {
        if (str_starts_with($stored, '$2') || str_starts_with($stored, '$argon2')) {
            return Hash::check($input, $stored);
        }

        return hash_equals($stored, $input);
    }
}
