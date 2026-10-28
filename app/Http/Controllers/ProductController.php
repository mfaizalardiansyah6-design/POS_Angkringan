<?php

namespace App\Http\Controllers;

use App\Enums\MovementType;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::query()->with('category')->latest();

        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('sku', 'like', '%'.$search.'%');
            });
        }

        if ($category = $request->query('category')) {
            $query->where('category_id', $category);
        }

        if ($status = $request->query('status')) {
            $query->where('is_active', $status === 'active');
        }

        if ($request->boolean('low')) {
            $query->tracked()->whereColumn('stock_qty', '<=', 'minimum_stock');
        }

        return view('products.index', [
            'products' => $query->paginate(12)->withQueryString(),
            'categories' => Category::query()->ordered()->get(),
            'filters' => $request->only(['q', 'category', 'status', 'low']),
        ]);
    }

    public function create(): View
    {
        return view('products.create', [
            'categories' => Category::query()->ordered()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProduct($request);
        $trackStock = $request->boolean('track_stock');
        $stock = $trackStock ? (int) ($validated['stock_qty'] ?? 0) : 0;

        $product = DB::transaction(function () use ($request, $validated, $trackStock, $stock) {
            $imagePath = $request->hasFile('image')
                ? $request->file('image')->store('products', 'public')
                : null;

            $product = Product::create([
                'category_id' => $validated['category_id'],
                'sku' => $validated['sku'] ?: null,
                'name' => $validated['name'],
                'unit' => $validated['unit'],
                'selling_price' => $validated['selling_price'],
                'cost_price' => $validated['cost_price'],
                'stock_qty' => $stock,
                'minimum_stock' => $validated['minimum_stock'] ?? 0,
                'track_stock' => $trackStock,
                'image_path' => $imagePath,
                'is_active' => $request->boolean('is_active', true),
            ]);

            if ($trackStock && $stock > 0) {
                InventoryMovement::create([
                    'product_id' => $product->id,
                    'user_id' => $request->user()->id,
                    'movement_type' => MovementType::Initial,
                    'quantity_change' => $stock,
                    'stock_before' => 0,
                    'stock_after' => $stock,
                    'source_key' => 'product:'.$product->id.':initial',
                    'note' => 'Stok awal produk.',
                ]);
            }

            ActivityLogger::log('product.created', $product, 'Produk dibuat: '.$product->name.'.', null, [
                'name' => $product->name,
                'selling_price' => (string) $product->selling_price,
                'cost_price' => (string) $product->cost_price,
                'stock_qty' => $product->stock_qty,
                'is_active' => $product->is_active,
            ]);

            return $product;
        });

        return redirect()->route('products.index')->with('success', 'Produk "'.$product->name.'" ditambahkan.');
    }

    public function edit(Product $product): View
    {
        return view('products.edit', [
            'product' => $product->load('category'),
            'categories' => Category::query()->ordered()->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $this->validateProduct($request, $product);
        $old = $product->only(['name', 'category_id', 'selling_price', 'cost_price', 'minimum_stock', 'track_stock', 'is_active']);

        DB::transaction(function () use ($request, $product, $validated, $old) {
            $imagePath = $product->image_path;

            if ($request->hasFile('image')) {
                if ($imagePath) {
                    Storage::disk('public')->delete($imagePath);
                }
                $imagePath = $request->file('image')->store('products', 'public');
            } elseif ($request->boolean('remove_image') && $imagePath) {
                Storage::disk('public')->delete($imagePath);
                $imagePath = null;
            }

            $product->update([
                'category_id' => $validated['category_id'],
                'sku' => $validated['sku'] ?: null,
                'name' => $validated['name'],
                'unit' => $validated['unit'],
                'selling_price' => $validated['selling_price'],
                'cost_price' => $validated['cost_price'],
                'minimum_stock' => $validated['minimum_stock'] ?? 0,
                'track_stock' => $request->boolean('track_stock'),
                'image_path' => $imagePath,
                'is_active' => $request->boolean('is_active'),
            ]);

            $new = $product->only(['name', 'category_id', 'selling_price', 'cost_price', 'minimum_stock', 'track_stock', 'is_active']);

            if ($old !== $new) {
                ActivityLogger::log('product.updated', $product, 'Produk diperbarui: '.$product->name.'.', $old, $new);
            }
        });

        return redirect()->route('products.index')->with('success', 'Produk diperbarui.');
    }

    public function toggle(Product $product): RedirectResponse
    {
        $product->update(['is_active' => ! $product->is_active]);

        ActivityLogger::log(
            'product.status_changed',
            $product,
            'Status produk "'.$product->name.'" diubah menjadi '.($product->is_active ? 'aktif' : 'nonaktif').'.',
            ['is_active' => ! $product->is_active],
            ['is_active' => $product->is_active],
        );

        return back()->with('success', 'Status produk diperbarui.');
    }

    public function adjustStock(Request $request, Product $product): RedirectResponse
    {
        if (! $product->track_stock) {
            throw ValidationException::withMessages([
                'quantity' => 'Produk ini tidak melacak stok.',
            ]);
        }

        $validated = $request->validate([
            'mode' => ['required', Rule::in(['in', 'waste', 'adjust'])],
            'quantity' => ['required', 'integer', 'min:0', 'max:100000'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validated['mode'] !== 'adjust' && $validated['quantity'] < 1) {
            throw ValidationException::withMessages([
                'quantity' => 'Jumlah harus lebih dari nol.',
            ]);
        }

        if (in_array($validated['mode'], ['waste', 'adjust'], true) && blank($validated['note'] ?? null)) {
            throw ValidationException::withMessages([
                'note' => 'Alasan wajib diisi untuk koreksi atau produk rusak.',
            ]);
        }

        DB::transaction(function () use ($validated, $product, $request) {
            $locked = Product::query()->whereKey($product->id)->lockForUpdate()->firstOrFail();
            $before = $locked->stock_qty;

            $after = match ($validated['mode']) {
                'in' => $before + $validated['quantity'],
                'waste' => $before - $validated['quantity'],
                'adjust' => $validated['quantity'],
            };

            if ($after < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Stok tidak boleh negatif. Stok saat ini '.$before.'.',
                ]);
            }

            if ($after === $before) {
                throw ValidationException::withMessages([
                    'quantity' => 'Jumlah stok tidak berubah.',
                ]);
            }

            $type = match ($validated['mode']) {
                'in' => MovementType::StockIn,
                'waste' => MovementType::Waste,
                'adjust' => MovementType::Adjustment,
            };

            $locked->update(['stock_qty' => $after]);

            InventoryMovement::create([
                'product_id' => $locked->id,
                'user_id' => $request->user()->id,
                'movement_type' => $type,
                'quantity_change' => $after - $before,
                'stock_before' => $before,
                'stock_after' => $after,
                'source_key' => 'manual:'.Str::uuid(),
                'note' => $validated['note'] ?? null,
            ]);

            ActivityLogger::log('product.stock_changed', $locked, 'Stok '.$locked->name.' disesuaikan dari '.$before.' menjadi '.$after.'.', ['stock_qty' => $before], [
                'stock_qty' => $after,
                'type' => $type->value,
                'note' => $validated['note'] ?? null,
            ]);
        });

        return back()->with('success', 'Stok produk diperbarui.');
    }

    private function validateProduct(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'sku' => ['nullable', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($product?->id)],
            'name' => ['required', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:30'],
            'selling_price' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'cost_price' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'stock_qty' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'minimum_stock' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'track_stock' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
            'remove_image' => ['sometimes', 'boolean'],
        ]);
    }
}
