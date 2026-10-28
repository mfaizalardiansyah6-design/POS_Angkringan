<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('categories.index', [
            'categories' => Category::query()->withCount('products')->ordered()->get(),
        ]);
    }

    public function create(): View
    {
        return view('categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $category = Category::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        ActivityLogger::log('category.created', $category, 'Kategori dibuat: '.$category->name.'.', null, $category->only(['name', 'is_active', 'sort_order']));

        return redirect()->route('categories.index')->with('success', 'Kategori "'.$category->name.'" ditambahkan.');
    }

    public function edit(Category $category): View
    {
        return view('categories.edit', ['category' => $category]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80', Rule::unique('categories', 'name')->ignore($category->id)],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $old = $category->only(['name', 'description', 'sort_order', 'is_active']);

        $category->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($old !== $category->only(['name', 'description', 'sort_order', 'is_active'])) {
            ActivityLogger::log('category.updated', $category, 'Kategori diperbarui: '.$category->name.'.', $old, $category->only(['name', 'description', 'sort_order', 'is_active']));
        }

        return redirect()->route('categories.index')->with('success', 'Kategori diperbarui.');
    }

    public function toggle(Category $category): RedirectResponse
    {
        $category->update(['is_active' => ! $category->is_active]);

        ActivityLogger::log(
            'category.status_changed',
            $category,
            'Status kategori "'.$category->name.'" diubah menjadi '.($category->is_active ? 'aktif' : 'nonaktif').'.',
            ['is_active' => ! $category->is_active],
            ['is_active' => $category->is_active],
        );

        return back()->with('success', 'Status kategori diperbarui.');
    }
}
