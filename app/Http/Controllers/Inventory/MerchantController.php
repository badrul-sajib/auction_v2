<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use Illuminate\Http\Request;

class MerchantController extends Controller
{
    public function index()
    {
        $merchants = Merchant::withCount('products')->latest()->paginate(10);

        return view('pages.inventory.merchants.index', compact('merchants'));
    }

    public function create()
    {
        return view('pages.inventory.merchants.create', ['merchant' => new Merchant()]);
    }

    public function store(Request $request)
    {
        Merchant::create($this->validateData($request));

        return redirect()->route('inventory.merchants.index')->with('status', 'Merchant created.');
    }

    public function edit(Merchant $merchant)
    {
        return view('pages.inventory.merchants.edit', compact('merchant'));
    }

    public function update(Request $request, Merchant $merchant)
    {
        $merchant->update($this->validateData($request));

        return redirect()->route('inventory.merchants.index')->with('status', 'Merchant updated.');
    }

    public function destroy(Merchant $merchant)
    {
        $merchant->delete();

        return redirect()->route('inventory.merchants.index')->with('status', 'Merchant deleted.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
