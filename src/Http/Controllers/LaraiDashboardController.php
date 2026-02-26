<?php

namespace Gometap\LaraiTracker\Http\Controllers;

use Gometap\LaraiTracker\Models\LaraiLog;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class LaraiDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_cost' => LaraiLog::sum('cost_usd'),
            'total_tokens' => LaraiLog::sum('total_tokens'),
            'today_cost' => LaraiLog::whereDate('created_at', today())->sum('cost_usd'),
            'recent_logs' => LaraiLog::latest()->limit(10)->get(),
            'costs_by_model' => LaraiLog::select('model', DB::raw('SUM(cost_usd) as cost'))
                ->groupBy('model')
                ->get(),
            'costs_over_time' => LaraiLog::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(cost_usd) as cost'))
                ->groupBy('date')
                ->orderBy('date')
                ->limit(30)
                ->get(),
            'currency_symbol' => \Gometap\LaraiTracker\Models\LaraiSetting::get('currency_symbol', '$'),
        ];

        return view('larai::dashboard', compact('stats'));
    }

    public function logs(Request $request)
    {

        $query = LaraiLog::query();

        // Search
        if ($request->has('q')) {
            $search = $request->get('q');
            $query->where(function ($q) use ($search) {
                $q->where('model', 'like', "%{$search}%")
                    ->orWhere('provider', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%");
            });
        }

        // Filter
        if ($request->has('provider') && $request->get('provider') !== 'all') {
            $query->where('provider', $request->get('provider'));
        }

        // Sort
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');
        $query->orderBy($sort, $direction);

        $logs = $query->paginate(20)->withQueryString();
        $providers = LaraiLog::select('provider')->distinct()->pluck('provider');
        $currency_symbol = \Gometap\LaraiTracker\Models\LaraiSetting::get('currency_symbol', '$');

        return view('larai::logs', compact('logs', 'providers', 'currency_symbol'));
    }

    public function export($format)
    {

        $logs = LaraiLog::latest()->get();

        switch ($format) {
            case 'json':
                return response()->json($logs)
                    ->header('Content-Disposition', 'attachment; filename="larai_logs.json"');

            case 'csv':
                $headers = [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => 'attachment; filename="larai_logs.csv"',
                ];

                $callback = function () use ($logs) {
                    $file = fopen('php://output', 'w');
                    fputcsv($file, ['ID', 'User ID', 'Provider', 'Model', 'Prompt Tokens', 'Completion Tokens', 'Total Tokens', 'Cost USD', 'Timestamp']);

                    foreach ($logs as $log) {
                        fputcsv($file, [
                            $log->id,
                            $log->user_id,
                            $log->provider,
                            $log->model,
                            $log->prompt_tokens,
                            $log->completion_tokens,
                            $log->total_tokens,
                            $log->cost_usd,
                            $log->created_at,
                        ]);
                    }
                    fclose($file);
                };

                return response()->stream($callback, 200, $headers);

            case 'txt':
                $content = "Larai Tracker Log Export\n" . str_repeat('=', 50) . "\n\n";
                foreach ($logs as $log) {
                    $content .= "[{$log->created_at}] #{$log->id} | " . strtoupper($log->provider) . " | {$log->model} | Tokens: {$log->total_tokens} | Cost: \${$log->cost_usd}\n";
                }

                return response($content)
                    ->header('Content-Type', 'text/plain')
                    ->header('Content-Disposition', 'attachment; filename="larai_logs.txt"');

            default:
                abort(404);
        }
    }

    /**
     * Display the settings page.
     */
    public function settings()
    {

        $budget = \Gometap\LaraiTracker\Models\LaraiBudget::first() ?? new \Gometap\LaraiTracker\Models\LaraiBudget([
            'amount' => 100,
            'alert_threshold' => 80,
            'is_active' => false
        ]);

        $customPrices = \Gometap\LaraiTracker\Models\LaraiModelPrice::all();
        $currency = [
            'code' => \Gometap\LaraiTracker\Models\LaraiSetting::get('currency_code', 'USD'),
            'symbol' => \Gometap\LaraiTracker\Models\LaraiSetting::get('currency_symbol', '$'),
        ];

        return view('larai::settings', compact('budget', 'customPrices', 'currency'));
    }

    /**
     * Update budget and cost settings.
     */
    public function updateSettings(Request $request)
    {

        // Budget
        $budgetData = $request->input('budget', []);
        $budget = \Gometap\LaraiTracker\Models\LaraiBudget::first() ?? new \Gometap\LaraiTracker\Models\LaraiBudget();
        $budget->fill([
            'amount' => $budgetData['amount'] ?? 0,
            'alert_threshold' => $budgetData['threshold'] ?? 80,
            'recipient_email' => $budgetData['email'] ?? null,
            'is_active' => isset($budgetData['active']),
        ])->save();

        // General Settings (Currency)
        if ($request->has('currency')) {
            \Gometap\LaraiTracker\Models\LaraiSetting::set('currency_code', $request->input('currency.code', 'USD'));
            \Gometap\LaraiTracker\Models\LaraiSetting::set('currency_symbol', $request->input('currency.symbol', '$'));
        }

        // Custom Prices
        $pricesData = $request->input('prices', []);
        foreach ($pricesData as $id => $data) {
            $price = \Gometap\LaraiTracker\Models\LaraiModelPrice::find($id);
            if ($price) {
                $price->update([
                    'input_price_per_1m' => $data['input'],
                    'output_price_per_1m' => $data['output'],
                    'is_custom' => true,
                ]);
            }
        }

        // Security: Password Change
        $security = $request->input('security', []);
        if (!empty($security['new_password'])) {
            $currentPassword = $this->getEffectivePassword();

            // If a password exists, verify the current one
            if (!is_null($currentPassword)) {
                if (empty($security['current_password'])) {
                    return redirect()->back()->with('password_error', 'Current password is required.');
                }

                $verified = $this->verifyPassword($security['current_password'], $currentPassword);
                if (!$verified) {
                    return redirect()->back()->with('password_error', 'Current password is incorrect.');
                }
            }

            // Validate new password
            if (strlen($security['new_password']) < 6) {
                return redirect()->back()->with('password_error', 'New password must be at least 6 characters.');
            }

            if ($security['new_password'] !== ($security['new_password_confirmation'] ?? '')) {
                return redirect()->back()->with('password_error', 'New password confirmation does not match.');
            }

            \Gometap\LaraiTracker\Models\LaraiSetting::set('dashboard_password', \Illuminate\Support\Facades\Hash::make($security['new_password']));

            return redirect()->back()->with('password_success', 'Password updated successfully.');
        }

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }

    /**
     * Sync prices from Gometap's central registry.
     */
    public function syncPrices()
    {

        try {
            $response = \Illuminate\Support\Facades\Http::get('https://raw.githubusercontent.com/gometap/larai-tracker/main/resources/data/prices.json');
            
            if ($response->successful()) {
                $prices = $response->json();
                foreach ($prices as $item) {
                    \Gometap\LaraiTracker\Models\LaraiModelPrice::updateOrCreate(
                        ['provider' => $item['provider'], 'model' => $item['model']],
                        [
                            'input_price_per_1m' => $item['input_price_per_1m'],
                            'output_price_per_1m' => $item['output_price_per_1m'],
                            'is_custom' => false,
                        ]
                    );
                }
                return redirect()->back()->with('success', 'Prices synchronized successfully.');
            }
        } catch (\Exception $e) {}

        return redirect()->back()->with('error', 'Failed to synchronize prices.');
    }

    /**
     * Get the effective password (DB > ENV > Config).
     */
    protected function getEffectivePassword(): ?string
    {
        try {
            $dbPassword = \Gometap\LaraiTracker\Models\LaraiSetting::get('dashboard_password');
            if (!is_null($dbPassword) && $dbPassword !== '') {
                return $dbPassword;
            }
        } catch (\Exception $e) {}

        return config('larai-tracker.password');
    }

    /**
     * Verify a plain text password against a stored password.
     */
    protected function verifyPassword(string $input, string $stored): bool
    {
        if (str_starts_with($stored, '$2y$') || str_starts_with($stored, '$2a$')) {
            return \Illuminate\Support\Facades\Hash::check($input, $stored);
        }

        return $input === $stored;
    }
}
