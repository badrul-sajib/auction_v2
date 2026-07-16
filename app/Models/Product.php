<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = ['name', 'sku', 'category_id', 'merchant_id', 'price', 'offer_price', 'cost', 'description', 'image'];

    protected $casts = [
        'price' => 'decimal:2',
        'offer_price' => 'decimal:2',
        'cost' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function auctions(): HasMany
    {
        return $this->hasMany(Auction::class);
    }

    public function totalStock(): int
    {
        return (int) $this->stocks()->sum('quantity');
    }

    /** Reduce on-hand stock across warehouses (largest first). */
    public function reduceStock(int $qty): void
    {
        foreach ($this->stocks()->orderByDesc('quantity')->get() as $stock) {
            if ($qty <= 0) {
                break;
            }
            $take = min($qty, $stock->quantity);
            $stock->decrement('quantity', $take);
            $qty -= $take;
        }
    }

    /** Return stock to the first warehouse (or create a row). */
    public function increaseStock(int $qty): void
    {
        if ($qty <= 0) {
            return;
        }

        $stock = $this->stocks()->orderByDesc('quantity')->first();
        if ($stock) {
            $stock->increment('quantity', $qty);
        }
    }

    public function imageUrl(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function currentPrice(): float
    {
        return (float) ($this->offer_price ?? $this->price);
    }
}
