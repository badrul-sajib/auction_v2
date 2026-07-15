<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockAdjustment;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockAdjustmentController extends Controller
{
    public function index()
    {
        $adjustments = StockAdjustment::with(['product', 'warehouse'])
            ->latest()
            ->paginate(15);

        return view('pages.inventory.adjustments.index', compact('adjustments'));
    }

    public function create()
    {
        return view('pages.inventory.adjustments.create', [
            'products' => Product::orderBy('name')->get(),
            'warehouses' => Warehouse::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'type' => ['required', 'in:add,remove'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($data) {
            if ($data['type'] === 'remove') {
                $available = Stock::quantityFor($data['product_id'], $data['warehouse_id']);
                if ($data['quantity'] > $available) {
                    throw ValidationException::withMessages([
                        'quantity' => "Only {$available} unit(s) available in that warehouse.",
                    ]);
                }
            }

            $delta = $data['type'] === 'add' ? $data['quantity'] : -$data['quantity'];
            Stock::adjust($data['product_id'], $data['warehouse_id'], $delta);

            StockAdjustment::create($data);
        });

        return redirect()->route('inventory.adjustments.index')->with('status', 'Stock adjustment recorded.');
    }
}
