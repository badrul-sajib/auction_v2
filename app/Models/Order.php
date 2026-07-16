<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'order_link_id', 'product_id', 'customer_name', 'customer_phone',
        'customer_address', 'quantity', 'unit_price', 'total', 'status',
        'payment_method_id', 'transaction_number', 'agent_type', 'agent_id',
        'withdrawal_id',
    ];

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
