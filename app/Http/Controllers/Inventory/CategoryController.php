<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->latest()->paginate(10);

        return view('pages.inventory.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('pages.inventory.categories.create', ['category' => new Category()]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['slug'] = Str::slug($data['name']);
        Category::create($data);

        return redirect()->route('inventory.categories.index')->with('status', 'Category created.');
    }

    public function edit(Category $category)
    {
        return view('pages.inventory.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $this->validateData($request);
        $data['slug'] = Str::slug($data['name']);
        $category->update($data);

        return redirect()->route('inventory.categories.index')->with('status', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('inventory.categories.index')->with('status', 'Category deleted.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);
    }
}
