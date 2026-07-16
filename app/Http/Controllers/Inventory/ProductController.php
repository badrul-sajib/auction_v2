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

    public function sampleImport()
    {
        $rows = [
            ['name', 'sku', 'category', 'merchant', 'price', 'offer_price', 'description', 'image'],
            ['Wireless Mouse', 'WM-001', 'Electronics', 'Acme Traders', '1200', '999', 'Ergonomic wireless mouse', 'https://example.com/mouse.jpg'],
            ['Basmati Rice 5kg', 'RICE-5', 'Dry Foods', 'Ghorer Bazar', '850', '', 'Premium aged basmati rice', ''],
        ];

        $handle = fopen('php://temp', 'r+');
        foreach ($rows as $row) {
            fputcsv($handle, $row, ',', '"', '');
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="products-import-sample.csv"',
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        if (! $handle) {
            return back()->with('status', 'Could not read the uploaded file.');
        }

        $header = fgetcsv($handle, null, ',', '"', '');
        $header = array_map(fn ($h) => strtolower(trim((string) $h)), $header ?: []);

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $line = 1;

        DB::transaction(function () use ($handle, $header, &$created, &$updated, &$skipped, &$line) {
            while (($row = fgetcsv($handle, null, ',', '"', '')) !== false) {
                $line++;
                $data = array_combine($header, array_pad($row, count($header), null));

                $name = trim((string) ($data['name'] ?? ''));
                $sku = trim((string) ($data['sku'] ?? ''));
                $price = $data['price'] ?? null;

                if ($name === '' || $sku === '' || ! is_numeric($price)) {
                    $skipped++;
                    continue;
                }

                $offer = $data['offer_price'] ?? null;
                $offer = is_numeric($offer) && (float) $offer < (float) $price ? (float) $offer : null;

                $categoryId = filled($data['category'] ?? null)
                    ? Category::firstOrCreate(['name' => trim($data['category'])])->id : null;
                $merchantId = filled($data['merchant'] ?? null)
                    ? Merchant::firstOrCreate(['name' => trim($data['merchant'])])->id : null;

                $product = Product::firstOrNew(['sku' => $sku]);
                $wasExisting = $product->exists;

                $product->fill([
                    'name' => $name,
                    'price' => (float) $price,
                    'offer_price' => $offer,
                    'category_id' => $categoryId,
                    'merchant_id' => $merchantId,
                    'description' => filled($data['description'] ?? null) ? trim($data['description']) : null,
                ]);

                // Optional image column: download from URL and store.
                if (filled($data['image'] ?? null)) {
                    if ($stored = $this->fetchImage(trim($data['image']))) {
                        if ($product->image) {
                            Storage::disk('public')->delete($product->image);
                        }
                        $product->image = $stored;
                    }
                }

                $product->save();

                $wasExisting ? $updated++ : $created++;
            }
        });

        fclose($handle);

        return redirect()->route('inventory.products.index')
            ->with('status', "Import complete: {$created} added, {$updated} updated, {$skipped} skipped.");
    }

    /** Download an image URL and store it on the public disk; returns the path or null. */
    private function fetchImage(string $url): ?string
    {
        if (! preg_match('#^https?://#i', $url)) {
            return null;
        }

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(15)->get($url);
        } catch (\Throwable $e) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $mime = strtolower((string) $response->header('Content-Type'));
        $ext = match (true) {
            str_contains($mime, 'png') => 'png',
            str_contains($mime, 'webp') => 'webp',
            str_contains($mime, 'jpeg'), str_contains($mime, 'jpg') => 'jpg',
            default => null,
        };

        if ($ext === null) {
            return null;
        }

        $path = 'products/' . \Illuminate\Support\Str::random(40) . '.' . $ext;
        \Illuminate\Support\Facades\Storage::disk('public')->put($path, $response->body());

        return $path;
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
