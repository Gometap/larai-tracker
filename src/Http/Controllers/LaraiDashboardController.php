<?php

namespace Gometap\LaraiTracker\Http\Controllers;

use Gometap\LaraiTracker\Models\LaraiLog;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class LaraiDashboardController extends Controller
{
    public function index()
    {
        if (Gate::denies('viewLaraiTracker')) {
            abort(403);
        }

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
        ];

        return view('larai::dashboard', compact('stats'));
    }

    public function logs(Request $request)
    {
        if (Gate::denies('viewLaraiTracker')) {
            abort(403);
        }

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

        return view('larai::logs', compact('logs', 'providers'));
    }

    public function export($format)
    {
        if (Gate::denies('viewLaraiTracker')) {
            abort(403);
        }

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
}
