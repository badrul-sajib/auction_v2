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

    public function totalStock(): int
    {
        return (int) $this->stocks()->sum('quantity');
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
