<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Enums\SaleStatus;
use App\Models\CashierShift;
use App\Models\Product;
use App\Models\Sale;
use App\Services\ShiftService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly ShiftService $shifts) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->isOwner()) {
            return $this->ownerDashboard($user);
        }

        $shift = $user->openShift()->first();
        $summary = $shift ? $this->shifts->summary($shift) : null;
        $today = [now()->startOfDay(), now()->endOfDay()];

        $sales = Sale::query()
            ->where('cashier_id', $user->id)
            ->completed()
            ->whereBetween('created_at', $today);

        $pendingQris = Sale::query()
            ->where('cashier_id', $user->id)
            ->pendingPayment()
            ->where('payment_method', PaymentMethod::Qris->value)
            ->count();

        return view('dashboard.cashier', [
            'shift' => $shift,
            'summary' => $summary,
            'todayTotal' => (string) (clone $sales)->sum('total_amount'),
            'todayCount' => (clone $sales)->count(),
            'pendingQris' => $pendingQris,
            'recentSales' => Sale::query()
                ->where('cashier_id', $user->id)
                ->latest()
                ->limit(6)
                ->get(),
        ]);
    }

    private function ownerDashboard($user): View
    {
        $today = [now()->startOfDay(), now()->endOfDay()];
        $completed = Sale::query()->completed()->whereBetween('created_at', $today);

        $todayRevenue = (string) (clone $completed)->sum('total_amount');
        $todayCount = (clone $completed)->count();
        $todayCash = (string) (clone $completed)->where('payment_method', PaymentMethod::Cash->value)->sum('total_amount');
        $todayQris = (string) (clone $completed)->where('payment_method', PaymentMethod::Qris->value)->sum('total_amount');

        $refundsToday = (string) Sale::query()
            ->where('status', SaleStatus::Voided->value)
            ->whereBetween('cancelled_at', $today)
            ->sum('refund_amount');

        $pendingQris = Sale::query()
            ->pendingPayment()
            ->where('payment_method', PaymentMethod::Qris->value)
            ->count();

        $lowStock = Product::query()
            ->active()
            ->tracked()
            ->with('category')
            ->whereColumn('stock_qty', '<=', 'minimum_stock')
            ->orderBy('stock_qty')
            ->limit(5)
            ->get();

        $recentSales = Sale::query()
            ->with('cashier')
            ->latest()
            ->limit(7)
            ->get();

        $topProduct = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', SaleStatus::Completed->value)
            ->whereBetween('sales.created_at', $today)
            ->select('sale_items.product_name', DB::raw('SUM(sale_items.quantity) as qty'))
            ->groupBy('sale_items.product_name')
            ->orderByDesc('qty')
            ->first();

        $trend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $trend[] = [
                'label' => $date->translatedFormat('D'),
                'date' => $date->format('d/m'),
                'value' => (float) Sale::query()
                    ->completed()
                    ->whereBetween('created_at', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])
                    ->sum('total_amount'),
            ];
        }
        $trendMax = max(array_column($trend, 'value')) ?: 1;

        $recentShifts = CashierShift::query()
            ->with('user')
            ->latest('opened_at')
            ->limit(4)
            ->get();

        return view('dashboard.owner', [
            'todayRevenue' => $todayRevenue,
            'todayCount' => $todayCount,
            'todayCash' => $todayCash,
            'todayQris' => $todayQris,
            'refundsToday' => $refundsToday,
            'pendingQris' => $pendingQris,
            'lowStock' => $lowStock,
            'recentSales' => $recentSales,
            'topProduct' => $topProduct,
            'trend' => $trend,
            'trendMax' => $trendMax,
            'recentShifts' => $recentShifts,
            'openShift' => $user->openShift()->first(),
        ]);
    }
}
