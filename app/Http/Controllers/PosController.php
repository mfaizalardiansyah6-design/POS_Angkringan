<?php

namespace App\Http\Controllers;

use App\Enums\OrderType;
use App\Enums\PaymentMethod;
use App\Models\Category;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PosController extends Controller
{
    public function __construct(private readonly CheckoutService $checkout) {}

    public function index(Request $request): View|RedirectResponse
    {
        $shift = $request->user()->openShift()->first();

        if (! $shift) {
            return redirect()->route('shifts.index')
                ->with('error', 'Buka shift terlebih dahulu sebelum mulai berjualan.');
        }

        $products = Product::query()
            ->active()
            ->with('category')
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->selling_price,
                'stock' => $product->stock_qty,
                'minimum_stock' => $product->minimum_stock,
                'track_stock' => $product->track_stock,
                'unit' => $product->unit,
                'category_id' => $product->category_id,
                'category' => $product->category?->name,
                'image' => $product->imageUrl(),
                'initial' => initials($product->name),
            ])
            ->values();

        $qrisImagePath = StoreSetting::getValue('qris_image_path');
        $oldItems = [];
        $oldItemsJson = old('items');

        if (is_string($oldItemsJson)) {
            $decoded = json_decode($oldItemsJson, true);
            $oldItems = is_array($decoded) ? $decoded : [];
        }

        return view('pos.index', [
            'shift' => $shift,
            'products' => $products,
            'categories' => Category::query()->active()->ordered()->get(['id', 'name']),
            'qrisInfo' => StoreSetting::getValue('qris_info', ''),
            'qrisImage' => $qrisImagePath ? Storage::disk('public')->url($qrisImagePath) : null,
            'oldItems' => $oldItems,
            'oldOrderType' => old('order_type', OrderType::DineIn->value),
            'oldPaymentMethod' => old('payment_method', PaymentMethod::Cash->value),
        ]);
    }

    public function checkout(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'json', 'max:50000'],
            'order_type' => ['required', Rule::enum(OrderType::class)],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'amount_paid' => ['nullable', 'numeric', 'min:0', 'required_if:payment_method,cash'],
            'payment_reference' => ['nullable', 'string', 'max:100'],
            'idempotency_key' => ['required', 'string', 'max:64'],
        ]);

        $items = json_decode($validated['items'], true);

        if (! is_array($items) || ! array_is_list($items)) {
            throw ValidationException::withMessages([
                'items' => 'Isi keranjang tidak valid.',
            ]);
        }

        $shift = $request->user()->openShift()->first();

        if (! $shift) {
            throw ValidationException::withMessages([
                'shift' => 'Shift tidak aktif. Buka shift terlebih dahulu.',
            ]);
        }

        [$sale, $created] = $this->checkout->checkout($request->user(), $shift, [
            'items' => $items,
            'order_type' => $validated['order_type'],
            'payment_method' => $validated['payment_method'],
            'amount_paid' => $validated['amount_paid'] ?? 0,
            'payment_reference' => $validated['payment_reference'] ?? null,
            'idempotency_key' => $validated['idempotency_key'],
        ]);

        $message = $created
            ? ($sale->isPendingQris()
                ? 'Transaksi QRIS dibuat. Verifikasi pembayaran sebelum menyelesaikan.'
                : 'Pembayaran tunai berhasil dicatat.')
            : 'Transaksi ini sudah tercatat sebelumnya. Tidak ada transaksi baru yang dibuat.';

        return redirect()->route('sales.show', $sale)->with('success', $message);
    }
}
