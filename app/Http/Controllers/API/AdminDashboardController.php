<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Models\LaundryOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        return response()->json([
            'today_orders' => LaundryOrder::whereDate('created_at', $today)->count(),
            'pending_orders' => LaundryOrder::where('status', 'Pending')->count(),
            'completed_orders' => LaundryOrder::where('status', 'Completed')->count(),
            'revenue_today' => LaundryOrder::whereDate('created_at', $today)->sum('total_price'),
            'revenue_this_month' => LaundryOrder::whereBetween('created_at', [$thisMonth, now()])->sum('total_price'),
        ]);
    }

    public function revenueReport(): JsonResponse
    {
        $dailyRevenue = LaundryOrder::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total_price) as total')
        )
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get();

        $monthlyRevenue = LaundryOrder::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('SUM(total_price) as total')
        )
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        return response()->json([
            'daily' => $dailyRevenue,
            'monthly' => $monthlyRevenue,
        ]);
    }
}
