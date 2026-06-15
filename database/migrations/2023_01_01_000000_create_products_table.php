<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id(); // Primary key, auto-incrementing
            $table->uuid('uuid')->unique(); // Universally Unique Identifier for external referencing, e.g., by Analytics Dashboard
            $table->string('name'); // Product name
            $table->string('slug')->unique(); // SEO-friendly URL slug
            $table->text('description')->nullable(); // Detailed product description
            $table->text('short_description')->nullable(); // Shorter description for listings
            $table->decimal('price', 10, 2); // Product price, 10 digits total, 2 after decimal
            $table->decimal('compare_at_price', 10, 2)->nullable(); // Original price for sale items
            $table->unsignedInteger('stock_quantity')->default(0); // Available stock
            $table->string('sku')->unique()->nullable(); // Stock Keeping Unit
            $table->boolean('is_active')->default(true); // Whether the product is visible/purchasable
            $table->boolean('is_featured')->default(false); // Whether the product is featured on the homepage
            $table->string('image_url')->nullable(); // Main product image URL
            $table->json('gallery_images')->nullable(); // JSON array of additional image URLs
            $table->string('category')->nullable(); // Simple category string for now, could be a foreign key to a categories table later
            $table->json('metadata')->nullable(); // Flexible JSON field for additional product attributes (e.g., dimensions, weight, brand, integration with AI-Powered Document Categorizer for product specs)
            $table->timestamp('published_at')->nullable(); // When the product was made public
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null'); // User who created the product (e.g., admin)
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null'); // User who last updated the product

            $table->timestamps(); // created_at and updated_at timestamps
            $table->softDeletes(); // Adds a `deleted_at` column for soft deletion
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};