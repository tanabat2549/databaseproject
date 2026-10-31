<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uid',
        'product_id',
        'quantity',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    /**
     * The user who owns this cart item.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uid');
    }

    /**
     * The product in the cart.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Exact subtotal as a decimal string (bcmath), e.g. "1780.00".
     */
    public function getSubtotalDecimalAttribute(): string
    {
        if (! $this->product) {
            return '0.00';
        }

        return bcmul((string) $this->product->price, (string) $this->quantity, 2);
    }

    /**
     * Subtotal as float (kept for existing views).
     */
    public function getSubtotalAttribute(): float
    {
        return (float) $this->subtotal_decimal;
    }

    /**
     * Exact total of many cart items as a decimal string (bcmath).
     *
     * @param  iterable<CartItem>  $items
     */
    public static function totalFor(iterable $items): string
    {
        $total = '0.00';

        foreach ($items as $item) {
            $total = bcadd($total, $item->subtotal_decimal, 2);
        }

        return $total;
    }

    /**
     * Check if item has enough stock in the inventory.
     */
    public function getIsAvailableAttribute(): bool
    {
        return $this->product !== null && $this->product->stock >= $this->quantity;
    }

    /**
     * Check if item has any stock issue (out of stock or requested > stock).
     */
    public function getHasStockIssueAttribute(): bool
    {
        return $this->product === null || $this->product->stock < $this->quantity;
    }
}