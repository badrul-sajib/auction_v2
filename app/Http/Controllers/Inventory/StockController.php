<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Stock;

class StockController extends Controller
{
    public function index()
    {
        $stocks = Stock::with(['product', 'warehouse'])
            ->orderByDesc('quantity')
            ->paginate(15);

        return view('pages.inventory.stock.index', compact('stocks'));
    }
}
