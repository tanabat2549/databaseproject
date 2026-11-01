<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'category',
        'description',
        'price',
        'image',
        'stock',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
        ];
    }

    /* -------------------------------------------------------------------------- */
    /*                                RELATIONSHIPS                               */
    /* -------------------------------------------------------------------------- */

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'product_id');
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class, 'product_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'product_id');
    }

    public function favoritedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'wishlists', 'product_id', 'uid')
            ->withTimestamps();
    }

    /* -------------------------------------------------------------------------- */
    /*                                   SCOPES                                   */
    /* -------------------------------------------------------------------------- */

    /**
     * Scope a query to only include products in stock.
     */
    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock', '>', 0);
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeCategory(Builder $query, ?string $category): Builder
    {
        if (! empty($category) && $category !== 'all') {
            return $query->where('category', $category);
        }

        return $query;
    }

    /**
     * Scope a query to search by name or description.
     * escape ตัวอักษร wildcard (% และ _) เพื่อให้ค้นหาเป็นข้อความตรงตัว
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $escaped = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term);
        $wildcard = "%{$escaped}%";

        return $query->where(function (Builder $q) use ($wildcard) {
            $q->whereRaw("name like ? escape '!'", [$wildcard])
                ->orWhereRaw("description like ? escape '!'", [$wildcard]);
        });
    }

    /**
     * Scope a query to filter by price range.
     */
    public function scopePriceRange(Builder $query, ?float $minPrice, ?float $maxPrice): Builder
    {
        if ($minPrice !== null && $maxPrice !== null && $minPrice > $maxPrice) {
            [$minPrice, $maxPrice] = [$maxPrice, $minPrice];
        }

        if ($minPrice !== null && $minPrice > 0) {
            $query->where('price', '>=', $minPrice);
        }

        if ($maxPrice !== null && $maxPrice > 0) {
            $query->where('price', '<=', $maxPrice);
        }

        return $query;
    }

    /**
     * Scope a query to sort products.
     * หมายเหตุ: 'rating' ต้องการ reviews_avg_rating ซึ่ง ProductController เรียก withAvg ไว้แล้ว
     */
    public function scopeSorted(Builder $query, ?string $sort): Builder
    {
        return match ($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'rating' => $query->orderByDesc('reviews_avg_rating'),
            'name_asc' => $query->orderBy('name', 'asc'),
            'latest' => $query->orderByDesc('created_at'),
            default => $query->orderByDesc('id'),
        };
    }

    /* -------------------------------------------------------------------------- */
    /*                               HELPER METHODS                               */
    /* -------------------------------------------------------------------------- */

    public function hasStock(int $quantity = 1): bool
    {
        return $this->stock >= $quantity;
    }

    public function averageRating(): float
    {
        if (isset($this->reviews_avg_rating)) {
            return round((float) $this->reviews_avg_rating, 1);
        }

        return round((float) ($this->reviews()->avg('rating') ?? 0), 1);
    }

    public function reviewsCount(): int
    {
        if (isset($this->reviews_count)) {
            return (int) $this->reviews_count;
        }

        return $this->reviews()->count();
    }

    /**
     * Rating distribution breakdown for 1 to 5 stars.
     *
     * @return array<int, array{count: int, percentage: float}>
     */
    public function ratingBreakdown(): array
    {
        $total = $this->reviewsCount();
        $counts = $this->reviews()
            ->selectRaw('rating, count(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        $breakdown = [];
        for ($star = 5; $star >= 1; $star--) {
            $count = $counts[$star] ?? 0;
            $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;
            $breakdown[$star] = [
                'count' => $count,
                'percentage' => $percentage,
            ];
        }

        return $breakdown;
    }

    /* -------------------------------------------------------------------------- */
    /*                                 ACCESSORS                                  */
    /* -------------------------------------------------------------------------- */

    public function getFormattedPriceAttribute(): string
    {
        return '฿' . number_format((float) $this->price, 2);
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'bouquet' => 'ช่อดอกไม้ (Bouquet)',
            'single' => 'ดอกเดี่ยว (Single)',
            'potted' => 'ไม้กระถาง (Potted)',
            'wreath' => 'พวงหรีด (Wreath)',
            default => ucfirst((string) $this->category),
        };
    }

    public function getImageUrlAttribute(): string
    {
        if (! empty($this->image)) {
            return str_starts_with($this->image, 'http')
                ? $this->image
                : asset('storage/' . $this->image);
        }

        return match ($this->name) {
            'Red Rose Bouquet' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=800&q=80',
            'Pastel Mixed Bouquet' => 'https://images.unsplash.com/photo-1561181286-d3fee7d55364?auto=format&fit=crop&w=800&q=80',
            'Sunflower Bouquet' => 'https://images.unsplash.com/photo-1597848212624-a19eb35e2651?auto=format&fit=crop&w=800&q=80',
            'Tulip Bouquet' => 'https://images.unsplash.com/photo-1520763185298-1b434c919102?auto=format&fit=crop&w=800&q=80',
            'Single Red Rose' => 'https://images.unsplash.com/photo-1559563458-527698bf5295?auto=format&fit=crop&w=800&q=80',
            'Single Sunflower' => 'https://images.unsplash.com/photo-1563245372-f21724e3856d?auto=format&fit=crop&w=800&q=80',
            'Single White Lily' => 'https://images.unsplash.com/photo-1508610048659-a06b669e3321?auto=format&fit=crop&w=800&q=80',
            'Potted Orchid' => 'https://images.unsplash.com/photo-1525310072745-f49212b5ac6d?auto=format&fit=crop&w=800&q=80',
            'Potted Lavender' => 'https://images.unsplash.com/photo-1528183429752-a97d0bf99b5a?auto=format&fit=crop&w=800&q=80',
            'Mini Cactus Set' => 'https://images.unsplash.com/photo-1459411552884-841db9b3cc2a?auto=format&fit=crop&w=800&q=80',
            'Eucalyptus Wreath' => 'https://images.unsplash.com/photo-1513297887119-d46091b24bfa?auto=format&fit=crop&w=800&q=80',
            'Dried Flower Wreath' => 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=800&q=80',
            default => match ($this->category) {
                'bouquet' => 'https://images.unsplash.com/photo-1561181286-d3fee7d55364?auto=format&fit=crop&w=800&q=80',
                'single' => 'https://images.unsplash.com/photo-1559563458-527698bf5295?auto=format&fit=crop&w=800&q=80',
                'potted' => 'https://images.unsplash.com/photo-1525310072745-f49212b5ac6d?auto=format&fit=crop&w=800&q=80',
                'wreath' => 'https://images.unsplash.com/photo-1513297887119-d46091b24bfa?auto=format&fit=crop&w=800&q=80',
                default => 'https://images.unsplash.com/photo-1508610048659-a06b669e3321?auto=format&fit=crop&w=800&q=80',
            },
        };
    }
}