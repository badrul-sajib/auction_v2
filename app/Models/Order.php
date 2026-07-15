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
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function orderLink(): BelongsTo
    {
        return $this->belongsTo(OrderLink::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
