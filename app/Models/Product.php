<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property float $price
 * @property string $currency
 * @property int $stock
 * @property string $sku
 * @property float|null $weight
 * @property string|null $dimensions
 * @property bool $is_active
 * @property int|null $category_id
 * @property int|null $brand_id
 * @property string|null $main_image_url
 * @property array|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read string|null $full_image_url
 * @property-read Category|null $category
 * @property-read Brand|null $brand
 * @property-read \Illuminate\Database\Eloquent\Collection<int, OrderItem> $orderItems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProductImage> $images
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder|Product active()
 * @method static \Illuminate\Database\Eloquent\Builder|Product search(string $search)
 * @mixin \Eloquent
 */
class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'currency',
        'stock',
        'sku',
        'weight',
        'dimensions',
        'is_active',
        'category_id',
        'brand_id', // Assuming a Brand model might exist
        'main_image_url', // Main image for the product
        'metadata', // For flexible product attributes (e.g., color, size, material)
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'float',
        'stock' => 'integer',
        'is_active' => 'boolean',
        'metadata' => 'array', // Casts JSON column to a PHP array
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        // Automatically generate a slug when creating a product if not provided.
        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });

        // Regenerate slug if the name changes and slug is empty or derived from old name.
        static::updating(function (Product $product) {
            if ($product->isDirty('name') && (empty($product->slug) || Str::slug($product->getOriginal('name')) === $product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    /**
     * Get the category that owns the product.
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        // Assuming a Category model exists for product categorization.
        // The ProductCategorizationService would interact with this relationship.
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the brand that owns the product.
     *
     * @return BelongsTo
     */
    public function brand(): BelongsTo
    {
        // Assuming a Brand model exists for product branding.
        return $this->belongsTo(Brand::class);
    }

    /**
     * Get the order items for the product.
     *
     * @return HasMany
     */
    public function orderItems(): HasMany
    {
        // Assuming an OrderItem model exists to link products to orders.
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the images for the product.
     *
     * @return HasMany
     */
    public function images(): HasMany
    {
        // Assuming a ProductImage model exists for multiple product images.
        return $this->hasMany(ProductImage::class);
    }

    /**
     * Scope a query to only include active products.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to search products by name or description.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where('name', 'like', '%' . $search . '%')
                     ->orWhere('description', 'like', '%' . $search . '%');
    }

    /**
     * Accessor for the full image URL.
     * This could integrate with an external image service or CDN,
     * potentially referencing a Microservices-based Analytics Dashboard
     * for image usage stats or an AI-Powered Document Categorizer & Search
     * for image content analysis.
     *
     * @return string|null
     */
    public function getFullImageUrlAttribute(): ?string
    {
        if ($this->main_image_url) {
            // Example: Prepend a base URL from config or CDN.
            // Ensure 'app.image_cdn_base_url' is configured in config/app.php or .env
            return config('app.image_cdn_base_url') . '/' . $this->main_image_url;
        }
        return null;
    }

    /**
     * Check if the product is currently in stock.
     *
     * @return bool
     */
    public function isInStock(): bool
    {
        return $this->stock > 0;
    }

    