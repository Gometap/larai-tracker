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
}
