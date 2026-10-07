<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // One row per uploaded Excel file (a "batch")
        Schema::create('legacy_uploads', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('file_name');
            $t->string('file_path');
            $t->string('file_hash', 40)->unique();      // stops the same file being uploaded twice
            $t->string('status', 20)->default('staged'); // staged -> approved -> imported
            $t->unsignedInteger('total_rows')->default(0);
            $t->unsignedInteger('error_rows')->default(0);
            $t->unsignedInteger('warning_rows')->default(0);
            $t->decimal('total_weight', 14, 3)->default(0);
            $t->decimal('total_billed', 16, 2)->default(0);
            $t->decimal('total_paid', 16, 2)->default(0);
            $t->json('source_totals')->nullable();       // totals the Excel itself claims (Summary sheet)
            $t->text('note')->nullable();
            $t->foreignId('uploaded_by')->nullable();
            $t->timestamp('approved_at')->nullable();
            $t->timestamp('imported_at')->nullable();
            $t->timestamps();
        });

        // One row per old Excel line (Cargo_Items sheet)
        Schema::create('legacy_rows', function (Blueprint $t) {
            $t->id();
            $t->foreignId('upload_id')->constrained('legacy_uploads')->cascadeOnDelete();
            $t->unsignedInteger('old_row')->nullable();
            $t->date('delivered_at')->nullable();
            $t->string('customer_code', 30)->nullable();
            $t->string('customer_name')->nullable();
            $t->string('shipment_no', 30)->nullable();
            $t->string('warehouse', 50)->nullable();
            $t->string('shipping_mark')->nullable();
            $t->string('tracking_no', 100)->nullable();
            $t->string('tracking_source', 60)->nullable();
            $t->string('goods_name_en')->nullable();
            $t->unsignedInteger('pack_no')->nullable();
            $t->string('pack_note', 50)->nullable();
            $t->decimal('weight_kg', 12, 3)->default(0);
            $t->decimal('price_per_kg', 10, 2)->nullable();
            $t->decimal('price', 14, 2)->default(0);
            $t->string('receiver_name')->nullable();
            $t->string('receiver_phone', 30)->nullable();
            $t->string('status', 30)->default('delivered');
            $t->decimal('paid_amount', 14, 2)->default(0);
            $t->string('name_as_written')->nullable();
            $t->text('review_flag')->nullable();
            $t->json('issues')->nullable();               // {"errors":[...],"warnings":[...]}
            $t->unsignedSmallInteger('error_count')->default(0);
            $t->unsignedSmallInteger('warning_count')->default(0);
            $t->string('import_status', 20)->default('staged'); // staged | imported
            $t->unsignedBigInteger('cargo_item_id')->nullable();
            $t->timestamps();

            $t->index(['upload_id', 'shipment_no']);
            $t->index(['upload_id', 'customer_code']);
            $t->index(['upload_id', 'tracking_no']);
        });

        Schema::create('legacy_payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('upload_id')->constrained('legacy_uploads')->cascadeOnDelete();
            $t->string('customer_code', 30);
            $t->string('customer_name')->nullable();
            $t->string('shipment_no', 30);
            $t->date('payment_date')->nullable();
            $t->decimal('amount', 14, 2);
            $t->string('payment_method', 30)->default('cash');
            $t->string('reference_no', 100)->nullable();
            $t->text('notes')->nullable();
            $t->unsignedBigInteger('customer_payment_id')->nullable();
            $t->timestamps();
            $t->index(['upload_id', 'customer_code']);
        });

        Schema::create('legacy_customers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('upload_id')->constrained('legacy_uploads')->cascadeOnDelete();
            $t->string('customer_code', 30);
            $t->string('name');
            $t->string('phone', 30)->nullable();
            $t->text('aliases')->nullable();
            $t->text('receivers')->nullable();
            $t->decimal('unapplied_credit', 14, 2)->default(0);
            $t->decimal('client_dues_list', 14, 2)->nullable();
            $t->unsignedBigInteger('customer_id')->nullable();
            $t->timestamps();
            $t->unique(['upload_id', 'customer_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legacy_customers');
        Schema::dropIfExists('legacy_payments');
        Schema::dropIfExists('legacy_rows');
        Schema::dropIfExists('legacy_uploads');
    }
};
