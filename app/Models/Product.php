<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'category',
        'price',
        'stock',
        'unit',
        'image',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Scope a query to only include active products.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter products by category.
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to filter products by price range.
     */
    public function scopePriceRange($query, $minPrice, $maxPrice)
    {
        return $query->whereBetween('price', [$minPrice, $maxPrice]);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Generate a slug from the product name.
     */
    public static function generateSlug($name)
    {
        return str()->slug($name);
    }

    /**
     * Check if the product is in stock.
     */
    public function isInStock()
    {
        return $this->stock > 0 && $this->is_active;
    }

    /**
     * Decrease the product stock.
     */
    public function decreaseStock($quantity)
    {
        $this->decrement('stock', $quantity);
    }

    /**
     * Increase the product stock.
     */
    public function increaseStock($quantity)
    {
        $this->increment('stock', $quantity);
    }

    /**
     * Get the formatted price with currency.
     */
    public function getFormattedPrice()
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    /**
     * Get the product image URL.
     */
    public function getImageUrl()
    {
        if ($this->image) {
            return asset('storage/products/' . $this->image);
        }

        return 'https://via.placeholder.com/300x200?text=' . urlencode($this->name);
    }
}
