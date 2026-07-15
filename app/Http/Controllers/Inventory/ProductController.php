<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Merchant;
use App\Models\Stock;
use App\Models\StockAdjustment;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'merchant'])
            ->withSum('stocks as total_stock', 'quantity')
            ->latest()
            ->paginate(10);

        return view('pages.inventory.products.index', compact('products'));
    }

    public function create()
    {
        return view('pages.inventory.products.create', [
            'product' => new Product(),
            'categories' => Category::orderBy('name')->get(),
            'merchants' => Merchant::orderBy('name')->get(),
            'warehouses' => Warehouse::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $opening = $request->validate([
            'opening_quantity' => ['nullable', 'integer', 'min:0'],
            'opening_warehouse_id' => ['nullable', 'exists:warehouses,id', 'required_with:opening_quantity'],
            'opening_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        DB::transaction(function () use ($data, $opening) {
            $qty = (int) ($opening['opening_quantity'] ?? 0);

            if ($qty > 0 && ! empty($opening['opening_cost'])) {
                $data['cost'] = $opening['opening_cost'];
            }

            $product = Product::create($data);

            if ($qty > 0) {
                Stock::adjust($product->id, $opening['opening_warehouse_id'], $qty);
                StockAdjustment::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $opening['opening_warehouse_id'],
                    'type' => 'add',
                    'quantity' => $qty,
                    'reason' => 'Opening stock',
                ]);
            }
        });

        return redirect()->route('inventory.products.index')->with('status', 'Product created.');
    }

    public function edit(Product $product)
    {
        return view('pages.inventory.products.edit', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
            'merchants' => Merchant::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validateData($request, $product);

        if ($request->boolean('remove_image') && $product->image) {
            Storage::disk('public')->delete($product->image);
            $data['image'] = null;
        }

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return redirect()->route('inventory.products.index')->with('status', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('inventory.products.index')->with('status', 'Product deleted.');
    }

    private function validateData(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($product?->id)],
            'category_id' => ['nullable', 'exists:categories,id'],
            'merchant_id' => ['nullable', 'exists:merchants,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'offer_price' => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ], [
            'offer_price.lt' => 'The offer price must be lower than the regular price. Leave it blank for no offer.',
        ]);
    }
}
