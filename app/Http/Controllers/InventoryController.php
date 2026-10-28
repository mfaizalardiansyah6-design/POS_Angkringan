<?php

namespace App\Http\Controllers;

use App\Enums\MovementType;
use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = InventoryMovement::query()->with(['product', 'user', 'sale'])->latest();

        if ($productId = $request->query('product')) {
            $query->where('product_id', $productId);
        }

        if ($type = $request->query('type')) {
            if (MovementType::tryFrom($type)) {
                $query->where('movement_type', $type);
            }
        }

        return view('inventory.index', [
            'movements' => $query->paginate(20)->withQueryString(),
            'products' => Product::query()->orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['product', 'type']),
        ]);
    }
}
