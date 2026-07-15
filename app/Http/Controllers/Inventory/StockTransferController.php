<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockTransferController extends Controller
{
    public function index()
    {
        $transfers = StockTransfer::with(['product', 'fromWarehouse', 'toWarehouse'])
            ->latest()
            ->paginate(15);

        return view('pages.inventory.transfers.index', compact('transfers'));
    }

    public function create()
    {
        return view('pages.inventory.transfers.create', [
            'products' => Product::orderBy('name')->get(),
            'warehouses' => Warehouse::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'from_warehouse_id' => ['required', 'exists:warehouses,id'],
            'to_warehouse_id' => ['required', 'exists:warehouses,id', 'different:from_warehouse_id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($data) {
            $available = Stock::quantityFor($data['product_id'], $data['from_warehouse_id']);
            if ($data['quantity'] > $available) {
                throw ValidationException::withMessages([
                    'quantity' => "Only {$available} unit(s) available in the source warehouse.",
                ]);
            }

            Stock::adjust($data['product_id'], $data['from_warehouse_id'], -$data['quantity']);
            Stock::adjust($data['product_id'], $data['to_warehouse_id'], $data['quantity']);

            StockTransfer::create($data);
        });

        return redirect()->route('inventory.transfers.index')->with('status', 'Stock transferred.');
    }
}
