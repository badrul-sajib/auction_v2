<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\Product;
use Illuminate\Http\Request;

class AuctionController extends Controller
{
    public function index(Product $product)
    {
        $auctions = $product->load('merchant')
            ->auctions()
            ->withCount(['orders'])
            ->latest()
            ->paginate(15);

        return view('pages.inventory.auctions.index', compact('product', 'auctions'));
    }

    public function create(Product $product)
    {
        return view('pages.inventory.auctions.create', [
            'product' => $product,
            'availableStock' => $product->totalStock(),
        ]);
    }

    public function store(Request $request, Product $product)
    {
        $available = $product->totalStock();

        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'stock' => ['nullable', 'integer', 'min:1', 'max:' . max(1, $available)],
            'validity' => ['required', 'in:1,7,30,never,custom'],
            'expires_at' => ['nullable', 'required_if:validity,custom', 'date', 'after:now'],
        ], [
            'stock.max' => "Only {$available} unit(s) are in stock for this product.",
        ]);

        $expiresAt = match ($data['validity']) {
            'never' => null,
            'custom' => $data['expires_at'],
            default => now()->addDays((int) $data['validity']),
        };

        $auction = $product->auctions()->create([
            'title' => $data['title'] ?? null,
            'stock' => $data['stock'] ?? null,
            'expires_at' => $expiresAt,
            'is_active' => true,
        ]);

        return redirect()
            ->route('inventory.auctions.index', $product)
            ->with('auction_link', $auction->url())
            ->with('status', 'Auction created.');
    }

    public function show(Auction $auction)
    {
        $auction->load('product');
        $orders = $auction->orders()->with('paymentMethod')->latest()->paginate(15);

        return view('pages.inventory.auctions.show', compact('auction', 'orders'));
    }

    public function toggle(Auction $auction)
    {
        $auction->update(['is_active' => ! $auction->is_active]);

        return back()->with('status', 'Auction ' . ($auction->is_active ? 'activated' : 'deactivated') . '.');
    }

    public function destroy(Auction $auction)
    {
        $product = $auction->product;
        $auction->delete();

        return redirect()->route('inventory.auctions.index', $product)->with('status', 'Auction removed.');
    }
}
