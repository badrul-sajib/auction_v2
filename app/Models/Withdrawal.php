<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Withdrawal extends Model
{
    protected $fillable = ['invoice_number', 'amount', 'orders_count', 'note'];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /** Statuses that count as collectable revenue. */
    public const PAID_STATUSES = ['confirmed', 'delivered'];

    /** Available balance = paid orders not yet withdrawn. */
    public static function availableBalance(): float
    {
        return (float) Order::whereIn('status', self::PAID_STATUSES)
            ->whereNull('withdrawal_id')
            ->sum('total');
    }

    public static function nextInvoiceNumber(): string
    {
        $next = (static::max('id') ?? 0) + 1;

        return 'INV-' . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}
