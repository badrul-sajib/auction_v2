<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    protected $fillable = ['product_id', 'warehouse_id', 'quantity'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Adjust (delta may be negative) the on-hand quantity for a product in a
     * warehouse, creating the stock row if it does not yet exist.
     */
    public static function adjust(int $productId, int $warehouseId, int $delta): self
    {
        $stock = static::firstOrCreate(
            ['product_id' => $productId, 'warehouse_id' => $warehouseId],
            ['quantity' => 0]
        );

        $stock->quantity += $delta;
        $stock->save();

        return $stock;
    }

    public static function quantityFor(int $productId, int $warehouseId): int
    {
        return (int) static::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->value('quantity');
    }
}
