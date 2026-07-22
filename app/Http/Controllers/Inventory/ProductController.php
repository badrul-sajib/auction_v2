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
            ['name', 'sku', 'category', 'merchant', 'price', 'offer_price', 'description', 'image', 'opening_stock', 'warehouse'],
            ['Wireless Mouse', 'WM-001', 'Electronics', 'Acme Traders', '1200', '999', 'Ergonomic wireless mouse', 'https://example.com/mouse.jpg', '50', 'Main Warehouse'],
            ['Basmati Rice 5kg', 'RICE-5', 'Dry Foods', 'Ghorer Bazar', '850', '', 'Premium aged basmati rice', '', '100', 'Main Warehouse'],
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

    private const IMPORT_COLUMNS = [
        'name', 'sku', 'category', 'merchant', 'price', 'offer_price', 'description', 'image', 'opening_stock', 'warehouse',
    ];

    /** Step 1: parse the uploaded CSV and show an editable preview. */
    public function previewImport(Request $request)
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

        $rows = [];
        while (($row = fgetcsv($handle, null, ',', '"', '')) !== false) {
            if (count(array_filter($row, fn ($c) => trim((string) $c) !== '')) === 0) {
                continue; // skip fully blank lines
            }
            $assoc = array_combine($header, array_pad($row, count($header), null));
            $rows[] = collect(self::IMPORT_COLUMNS)
                ->mapWithKeys(fn ($col) => [$col => trim((string) ($assoc[$col] ?? ''))])
                ->all();
        }
        fclose($handle);

        if (empty($rows)) {
            return back()->with('status', 'No data rows found in the file.');
        }

        return view('pages.inventory.products.import-preview', [
            'rows' => $rows,
            'columns' => self::IMPORT_COLUMNS,
            'options' => [
                'category' => Category::orderBy('name')->pluck('name'),
                'merchant' => Merchant::orderBy('name')->pluck('name'),
                'warehouse' => Warehouse::orderBy('name')->pluck('name'),
            ],
        ]);
    }

    /** Step 2: import the (possibly edited) rows from the preview. */
    public function import(Request $request)
    {
        $request->validate([
            'rows.*.image_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $rows = $request->input('rows', []);

        $created = 0;
        $updated = 0;
        $skipped = 0;

        DB::transaction(function () use ($request, $rows, &$created, &$updated, &$skipped) {
            foreach ($rows as $i => $data) {
                $imageFile = $request->file("rows.{$i}.image_file");
                match ($this->importRow($data, $imageFile)) {
                    'created' => $created++,
                    'updated' => $updated++,
                    default => $skipped++,
                };
            }
        });

        return redirect()->route('inventory.products.index')
            ->with('status', "Import complete: {$created} added, {$updated} updated, {$skipped} skipped.");
    }

    /** Create/update a single product from a row of import data. */
    private function importRow(array $data, ?\Illuminate\Http\UploadedFile $imageFile = null): string
    {
        $name = trim((string) ($data['name'] ?? ''));
        $sku = trim((string) ($data['sku'] ?? ''));
        $price = $data['price'] ?? null;

        if ($name === '' || $sku === '' || ! is_numeric($price)) {
            return 'skipped';
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

        // Uploaded file takes precedence over an image URL.
        if ($imageFile) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $product->image = $imageFile->store('products', 'public');
        } elseif (filled($data['image'] ?? null)) {
            if ($stored = $this->fetchImage(trim($data['image']))) {
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }
                $product->image = $stored;
            }
        }

        $product->save();

        // Opening stock only applies to newly created products.
        $openingQty = (int) ($data['opening_stock'] ?? 0);
        if (! $wasExisting && $openingQty > 0) {
            $warehouseName = filled($data['warehouse'] ?? null) ? trim($data['warehouse']) : 'Main Warehouse';
            $warehouse = Warehouse::firstOrCreate(['name' => $warehouseName]);

            Stock::adjust($product->id, $warehouse->id, $openingQty);
            StockAdjustment::create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'type' => 'add',
                'quantity' => $openingQty,
                'reason' => 'Opening stock (import)',
            ]);
        }

        return $wasExisting ? 'updated' : 'created';
    }

    /** Download an image URL and store it on the public disk; returns the path or null. */
    private function fetchImage(string $url): ?string
    {
        if (! preg_match('#^https?://#i', $url) || ! $this->isPublicHost($url)) {
            return null;
        }

        try {
            $response = \Illuminate\Support\Facades\Http::connectTimeout(5)->timeout(15)
                ->withOptions(['allow_redirects' => ['max' => 2]])
                ->get($url);
        } catch (\Throwable $e) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $body = $response->body();
        if (strlen($body) > 3 * 1024 * 1024) { // cap at 3 MB
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
        \Illuminate\Support\Facades\Storage::disk('public')->put($path, $body);

        return $path;
    }

    /** SSRF guard: reject hosts that resolve to private/reserved IP ranges. */
    private function isPublicHost(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (! $host) {
            return false;
        }

        // Resolve the host to IPv4 addresses; reject if it can't be resolved.
        $ips = @gethostbynamel($host);
        if (empty($ips)) {
            return false;
        }

        foreach ($ips as $ip) {
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                return false; // private or reserved → block (SSRF)
            }
        }

        return true;
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
