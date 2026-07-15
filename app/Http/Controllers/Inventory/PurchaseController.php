<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Stock;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index()
    {
        $purchases = Purchase::with(['product', 'warehouse', 'merchant'])
            ->latest()
            ->paginate(15);

        return view('pages.inventory.purchases.index', compact('purchases'));
    }

    public function create()
    {
        return view('pages.inventory.purchases.create', [
            'products' => Product::orderBy('name')->get(),
            'warehouses' => Warehouse::orderBy('name')->get(),
            'merchants' => Merchant::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'merchant_id' => ['nullable', 'exists:merchants,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'total_cost' => ['required', 'numeric', 'min:0'],
            'purchased_at' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($data) {
            // System derives unit cost from the total.
            $data['unit_cost'] = round($data['total_cost'] / $data['quantity'], 2);
            $data['purchased_at'] = $data['purchased_at'] ?? now()->toDateString();

            Purchase::create($data);

            // Purchases add on-hand stock and set the product's latest cost.
            Stock::adjust($data['product_id'], $data['warehouse_id'], $data['quantity']);
            Product::whereKey($data['product_id'])->update(['cost' => $data['unit_cost']]);
        });

        return redirect()->route('inventory.purchases.index')->with('status', 'Purchase recorded and stock updated.');
    }
}
