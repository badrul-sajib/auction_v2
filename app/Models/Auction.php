<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Auction extends Model
{
    protected $fillable = ['product_id', 'title', 'stock', 'token', 'expires_at', 'is_active'];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Auction $auction) {
            $auction->token ??= Str::random(32);
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Orders still reference this via the legacy order_link_id column.
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'order_link_id');
    }

    /** Units sold (all non-cancelled orders). */
    public function soldQuantity(): int
    {
        return (int) $this->orders()->where('status', '!=', 'cancelled')->sum('quantity');
    }

    /** Remaining units, or null when stock is unlimited. */
    public function remainingStock(): ?int
    {
        if ($this->stock === null) {
            return null;
        }

        return max(0, $this->stock - $this->soldQuantity());
    }

    /** Revenue for this auction (confirmed or delivered orders). */
    public function revenue(): float
    {
        return (float) $this->orders()->paid()->sum('total');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isSoldOut(): bool
    {
        $remaining = $this->remainingStock();

        return $remaining !== null && $remaining <= 0;
    }

    public function isValid(): bool
    {
        return $this->is_active && ! $this->isExpired() && ! $this->isSoldOut();
    }

    public function statusLabel(): string
    {
        if (! $this->is_active) {
            return 'Inactive';
        }
        if ($this->isExpired()) {
            return 'Expired';
        }
        if ($this->isSoldOut()) {
            return 'Sold out';
        }

        return 'Active';
    }

    public function url(): string
    {
        return route('order.show', $this->token);
    }
}
