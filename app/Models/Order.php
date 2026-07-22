<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    /** All valid order statuses. */
    public const STATUSES = ['pending', 'confirmed', 'delivered', 'cancelled'];

    /** Statuses that count as collectable revenue / a completed sale. */
    public const PAID_STATUSES = ['confirmed', 'delivered'];

    protected $fillable = [
        'order_link_id', 'product_id', 'customer_name', 'customer_phone',
        'customer_address', 'quantity', 'unit_price', 'total', 'status',
        'payment_method_id', 'transaction_number', 'agent_type', 'agent_id',
        'withdrawal_id',
    ];

    /** Scope: paid orders (confirmed or delivered). */
    public function scopePaid($query)
    {
        return $query->whereIn('status', self::PAID_STATUSES);
    }

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function auction(): BelongsTo
    {
        return $this->belongsTo(Auction::class, 'order_link_id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function withdrawal(): BelongsTo
    {
        return $this->belongsTo(Withdrawal::class);
    }
}
