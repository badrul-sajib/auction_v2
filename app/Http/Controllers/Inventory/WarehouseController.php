<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::withCount('stocks')->latest()->paginate(10);

        return view('pages.inventory.warehouses.index', compact('warehouses'));
    }

    public function create()
    {
        return view('pages.inventory.warehouses.create', ['warehouse' => new Warehouse()]);
    }

    public function store(Request $request)
    {
        Warehouse::create($this->validateData($request));

        return redirect()->route('inventory.warehouses.index')->with('status', 'Warehouse created.');
    }

    public function edit(Warehouse $warehouse)
    {
        return view('pages.inventory.warehouses.edit', compact('warehouse'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $warehouse->update($this->validateData($request));

        return redirect()->route('inventory.warehouses.index')->with('status', 'Warehouse updated.');
    }

    public function destroy(Warehouse $warehouse)
    {
        $warehouse->delete();

        return redirect()->route('inventory.warehouses.index')->with('status', 'Warehouse deleted.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'location' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
