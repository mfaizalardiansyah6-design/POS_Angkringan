<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Enums\SaleStatus;
use App\Models\CashierShift;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = Carbon::parse($request->query('from', now()->startOfMonth()->toDateString()))->startOfDay();
        $to = Carbon::parse($request->query('to', now()->toDateString()))->endOfDay();

        $completedSales = Sale::query()
            ->with('cashier')
            ->completed()
            ->whereBetween('created_at', [$from, $to]);

        $revenue = (string) (clone $completedSales)->sum('total_amount');
        $transactionCount = (clone $completedSales)->count();
        $cash = (string) (clone $completedSales)->where('payment_method', PaymentMethod::Cash->value)->sum('total_amount');
        $qris = (string) (clone $completedSales)->where('payment_method', PaymentMethod::Qris->value)->sum('total_amount');

        $grossProfit = (string) SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', SaleStatus::Completed->value)
            ->whereBetween('sales.created_at', [$from, $to])
            ->sum(DB::raw('(sale_items.unit_price - sale_items.unit_cost) * sale_items.quantity'));

        $expenses = (string) DB::table('business_expenses')
            ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');

        $refunds = (string) Sale::query()
            ->where('status', SaleStatus::Voided->value)
            ->whereBetween('cancelled_at', [$from, $to])
            ->sum('refund_amount');

        $topProducts = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', SaleStatus::Completed->value)
            ->whereBetween('sales.created_at', [$from, $to])
            ->select(
                'sale_items.product_name',
                DB::raw('SUM(sale_items.quantity) as quantity'),
                DB::raw('SUM(sale_items.line_total) as revenue'),
            )
            ->groupBy('sale_items.product_name')
            ->orderByDesc('quantity')
            ->limit(8)
            ->get();

        $dailySales = Sale::query()
            ->completed()
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as sale_date, SUM(total_amount) as total')
            ->groupBy('sale_date')
            ->orderBy('sale_date')
            ->pluck('total', 'sale_date');

        $trend = [];
        $cursor = $from->copy()->startOfDay();
        $endCursor = $to->copy()->startOfDay();

        while ($cursor->lte($endCursor)) {
            $key = $cursor->format('Y-m-d');
            $trend[] = [
                'label' => $cursor->format('d/m'),
                'value' => (float) ($dailySales[$key] ?? 0),
            ];
            $cursor->addDay();
        }

        $trendMax = max(array_column($trend, 'value') ?: [1]) ?: 1;

        $shifts = CashierShift::query()
            ->with('user')
            ->whereBetween('opened_at', [$from, $to])
            ->latest('opened_at')
            ->get();

        $lowStock = Product::query()
            ->active()
            ->tracked()
            ->whereColumn('stock_qty', '<=', 'minimum_stock')
            ->orderBy('stock_qty')
            ->limit(10)
            ->get();

        $avgTransaction = $transactionCount > 0 ? (float) $revenue / $transactionCount : 0;

        return view('reports.index', [
            'from' => $from,
            'to' => $to,
            'revenue' => $revenue,
            'transactionCount' => $transactionCount,
            'cash' => $cash,
            'qris' => $qris,
            'grossProfit' => $grossProfit,
            'expenses' => $expenses,
            'netProfit' => (string) ((float) $grossProfit - (float) $expenses),
            'refunds' => $refunds,
            'avgTransaction' => $avgTransaction,
            'topProducts' => $topProducts,
            'trend' => $trend,
            'trendMax' => $trendMax,
            'shifts' => $shifts,
            'lowStock' => $lowStock,
            'topProduct' => $topProducts->first(),
        ]);
    }
}
