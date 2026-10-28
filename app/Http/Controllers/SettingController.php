<?php

namespace App\Http\Controllers;

use App\Models\StoreSetting;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingController extends Controller
{
    private const KEYS = [
        'store_name',
        'store_address',
        'store_phone',
        'receipt_footer',
        'receipt_paper_width',
        'qris_info',
        'qris_image_path',
    ];

    public function edit(): View
    {
        $settings = StoreSetting::query()->pluck('value', 'key');
        $qrisImage = $settings->get('qris_image_path');

        return view('settings.edit', [
            'settings' => $settings,
            'qrisImage' => $qrisImage ? Storage::disk('public')->url($qrisImage) : null,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:100'],
            'store_address' => ['nullable', 'string', 'max:255'],
            'store_phone' => ['nullable', 'string', 'max:30'],
            'receipt_footer' => ['nullable', 'string', 'max:255'],
            'receipt_paper_width' => ['required', Rule::in(['58', '80'])],
            'qris_info' => ['nullable', 'string', 'max:1000'],
            'qris_image' => ['nullable', 'image', 'max:2048'],
            'remove_qris_image' => ['sometimes', 'boolean'],
        ]);

        $old = StoreSetting::query()->pluck('value', 'key')->only(self::KEYS)->all();

        foreach (['store_name', 'store_address', 'store_phone', 'receipt_footer', 'receipt_paper_width', 'qris_info'] as $key) {
            StoreSetting::putValue($key, $validated[$key] ?? null);
        }

        $imagePath = StoreSetting::getValue('qris_image_path');

        if ($request->hasFile('qris_image')) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            StoreSetting::putValue('qris_image_path', $request->file('qris_image')->store('qris', 'public'));
        } elseif ($request->boolean('remove_qris_image') && $imagePath) {
            Storage::disk('public')->delete($imagePath);
            StoreSetting::putValue('qris_image_path', null);
        }

        $new = StoreSetting::query()->pluck('value', 'key')->only(self::KEYS)->all();

        if ($old !== $new) {
            ActivityLogger::log('settings.updated', null, 'Pengaturan toko diperbarui.', $old, $new);
        }

        return redirect()->route('settings.edit')->with('success', 'Pengaturan toko disimpan.');
    }
}
