<?php

namespace Database\Seeders;

use App\Enums\MovementType;
use App\Enums\Role;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::updateOrCreate(
            ['username' => 'owner'],
            ['name' => 'Pemilik Angkringan', 'password' => 'password', 'role' => Role::Owner, 'is_active' => true],
        );

        User::updateOrCreate(
            ['username' => 'kasir'],
            ['name' => 'Kasir Angkringan', 'password' => 'password', 'role' => Role::Cashier, 'is_active' => true],
        );

        $categories = [
            ['name' => 'Makanan', 'description' => 'Menu utama dan lauk pendamping.', 'sort_order' => 1],
            ['name' => 'Minuman', 'description' => 'Minuman hangat dan dingin.', 'sort_order' => 2],
            ['name' => 'Camilan', 'description' => 'Gorengan dan camilan malam.', 'sort_order' => 3],
        ];

        foreach ($categories as $data) {
            Category::updateOrCreate(['name' => $data['name']], $data + ['is_active' => true]);
        }

        $products = [
            ['sku' => 'MKN-001', 'category' => 'Makanan', 'name' => 'Nasi Kucing Teri', 'unit' => 'bungkus', 'selling_price' => 5000, 'cost_price' => 2500, 'stock_qty' => 30, 'minimum_stock' => 8],
            ['sku' => 'MKN-002', 'category' => 'Makanan', 'name' => 'Sate Usus', 'unit' => 'tusuk', 'selling_price' => 4000, 'cost_price' => 1800, 'stock_qty' => 40, 'minimum_stock' => 10],
            ['sku' => 'MKN-003', 'category' => 'Makanan', 'name' => 'Tahu Bakso', 'unit' => 'porsi', 'selling_price' => 8000, 'cost_price' => 4200, 'stock_qty' => 18, 'minimum_stock' => 6],
            ['sku' => 'CAM-001', 'category' => 'Camilan', 'name' => 'Tempe Mendoan', 'unit' => 'porsi', 'selling_price' => 7000, 'cost_price' => 3200, 'stock_qty' => 20, 'minimum_stock' => 6],
            ['sku' => 'CAM-002', 'category' => 'Camilan', 'name' => 'Bakwan Sayur', 'unit' => 'buah', 'selling_price' => 3000, 'cost_price' => 1200, 'stock_qty' => 24, 'minimum_stock' => 8],
            ['sku' => 'MNM-001', 'category' => 'Minuman', 'name' => 'Es Teh Manis', 'unit' => 'gelas', 'selling_price' => 4000, 'cost_price' => 1200, 'stock_qty' => 50, 'minimum_stock' => 12],
            ['sku' => 'MNM-002', 'category' => 'Minuman', 'name' => 'Wedang Jahe', 'unit' => 'gelas', 'selling_price' => 6000, 'cost_price' => 2000, 'stock_qty' => 25, 'minimum_stock' => 8],
            ['sku' => 'MNM-003', 'category' => 'Minuman', 'name' => 'Kopi Hitam', 'unit' => 'gelas', 'selling_price' => 5000, 'cost_price' => 1800, 'stock_qty' => 30, 'minimum_stock' => 10],
        ];

        foreach ($products as $data) {
            $category = Category::where('name', $data['category'])->firstOrFail();
            $product = Product::updateOrCreate(
                ['sku' => $data['sku']],
                [
                    'category_id' => $category->id,
                    'name' => $data['name'],
                    'unit' => $data['unit'],
                    'selling_price' => $data['selling_price'],
                    'cost_price' => $data['cost_price'],
                    'stock_qty' => $data['stock_qty'],
                    'minimum_stock' => $data['minimum_stock'],
                    'track_stock' => true,
                    'is_active' => true,
                ],
            );

            InventoryMovement::firstOrCreate(
                ['source_key' => 'seed:product:'.$product->id],
                [
                    'product_id' => $product->id,
                    'user_id' => $owner->id,
                    'movement_type' => MovementType::Initial,
                    'quantity_change' => $product->stock_qty,
                    'stock_before' => 0,
                    'stock_after' => $product->stock_qty,
                    'note' => 'Stok awal data contoh.',
                ],
            );
        }

        $settings = [
            'store_name' => 'Angkringan',
            'store_address' => '',
            'store_phone' => '',
            'receipt_footer' => 'Terima kasih. Sampai jumpa lagi.',
            'qris_info' => 'Tunjukkan QRIS usaha dan verifikasi pembayaran di kanal merchant resmi.',
            'receipt_paper_width' => '80',
            'qris_image_path' => null,
        ];

        foreach ($settings as $key => $value) {
            StoreSetting::putValue($key, $value);
        }
    }
}
