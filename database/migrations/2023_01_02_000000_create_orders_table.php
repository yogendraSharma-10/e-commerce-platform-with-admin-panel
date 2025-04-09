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
        Schema::create('orders', function (Blueprint $table) {
            $table->id(); // Primary key for the orders table

            // Foreign key to link with the users table.
            // Assuming a 'users' table already exists or will be created.
            // cascadeOnDelete ensures that if a user is deleted, their orders are also deleted.
            $table->foreignId('user_id')
                  ->constrained('users') // Assumes 'users' table exists
                  ->cascadeOnDelete();

            $table->string('order_number')->unique(); // Unique identifier for each order, e.g., INV-2023-0001
            $table->string('status')->default('pending'); // Current status of the order (e.g., pending, processing, completed, cancelled, refunded)
            $table->decimal('total_amount', 10, 2); // Total amount of the order, 10 digits in total, 2 after decimal point
            $table->string('currency', 3)->default('USD'); // Currency code (e.g., USD, EUR)

            // Storing addresses as JSON for flexibility, allowing different address structures.
            // Alternatively, separate address fields or a dedicated addresses table could be used.
            $table->json('shipping_address')->nullable();
            $table->json('billing_address')->nullable();

            $table->string('payment_method')->nullable(); // e.g., 'credit_card', 'paypal', 'stripe'
            $table->string('payment_status')->default('pending'); // e.g., 'pending', 'paid', 'failed', 'refunded'
            $table->string('transaction_id')->nullable()->unique(); // ID from the payment gateway, should be unique

            $table->timestamp('shipped_at')->nullable(); // Timestamp when the order was shipped
            $table->timestamp('delivered_at')->nullable(); // Timestamp when the order was delivered

            $table->text('notes')->nullable(); // Any additional notes for the order

            $table->timestamps(); // Adds `created_at` and `updated_at` columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};