<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One row = "was the customer told that receipt X reached status Y?"
        // Rows are created the first time someone ticks a cell, so no backfill is needed.
        Schema::create('receipt_status_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_no', 50);
            $table->string('status', 40);                       // cargo_items.status value
            $table->boolean('is_notified')->default(false);
            $table->timestamp('notified_at')->nullable();
            $table->foreignId('notified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->unique(['receipt_no', 'status'], 'rsn_receipt_status_unique');
            $table->index(['status', 'is_notified'], 'rsn_status_notified_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipt_status_notifications');
    }
};
