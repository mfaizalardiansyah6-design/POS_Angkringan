<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Enums\SaleStatus;
use App\Models\Sale;
use App\Models\StoreSetting;
use App\Models\User;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function __construct(private readonly CheckoutService $checkout) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $query = Sale::query()->with(['cashier', 'shift'])->latest();

        if ($user->isCashier()) {
            $query->where('cashier_id', $user->id);
        }

        if ($search = trim((string) $request->query('q'))) {
            $query->where('sale_number', 'like', '%'.$search.'%');
        }

        if ($status = $request->query('status')) {
            if (SaleStatus::tryFrom($status)) {
                $query->where('status', $status);
            }
        }

        if ($method = $request->query('method')) {
            if (PaymentMethod::tryFrom($method)) {
                $query->where('payment_method', $method);
            }
        }

        if ($from = $request->query('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->query('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        return view('sales.index', [
            'sales' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only(['q', 'status', 'method', 'from', 'to']),
        ]);
    }

    public function show(Request $request, Sale $sale): View
    {
        $this->authorizeView($request->user(), $sale);
        $sale->load(['items', 'cashier', 'shift', 'cancelledBy']);

        return view('sales.show', [
            'sale' => $sale,
            'storeName' => StoreSetting::getValue('store_name', 'Angkringan'),
            'storeAddress' => StoreSetting::getValue('store_address', ''),
            'storePhone' => StoreSetting::getValue('store_phone', ''),
            'receiptFooter' => StoreSetting::getValue('receipt_footer', ''),
            'paperWidth' => StoreSetting::getValue('receipt_paper_width', '80'),
        ]);
    }

    public function receipt(Request $request, Sale $sale): View
    {
        $this->authorizeView($request->user(), $sale);
        $sale->load(['items', 'cashier', 'shift']);

        return view('sales.receipt', [
            'sale' => $sale,
            'storeName' => StoreSetting::getValue('store_name', 'Angkringan'),
            'storeAddress' => StoreSetting::getValue('store_address', ''),
            'storePhone' => StoreSetting::getValue('store_phone', ''),
            'receiptFooter' => StoreSetting::getValue('receipt_footer', ''),
            'paperWidth' => StoreSetting::getValue('receipt_paper_width', '80'),
        ]);
    }

    public function confirmQris(Request $request, Sale $sale): RedirectResponse
    {
        $this->checkout->confirmQris($sale, $request->user());

        return redirect()->route('sales.show', $sale)
            ->with('success', 'Pembayaran QRIS dikonfirmasi. Transaksi selesai.');
    }

    public function void(Request $request, Sale $sale): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $this->checkout->void($sale, $request->user(), $validated['reason']);

        return redirect()->route('sales.show', $sale)
            ->with('success', 'Transaksi dibatalkan dan pembalikan stok/kas dicatat.');
    }

    private function authorizeView(User $user, Sale $sale): void
    {
        abort_unless($user->isOwner() || $sale->cashier_id === $user->id, 403, 'Anda tidak berwenang melihat transaksi ini.');
    }
}
