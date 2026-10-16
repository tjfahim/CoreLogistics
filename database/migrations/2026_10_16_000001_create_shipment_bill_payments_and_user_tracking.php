<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) One "voucher" = one lump-sum payment that settles cost lines of one or many shipments.
        //    The split lives in items_shipment_payments (one row per cost line, linked by shipment_bill_payment_id).
        Schema::create('shipment_bill_payments', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no', 30)->nullable()->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();   // who recorded it
            $table->foreignId('agent_id')->nullable()->constrained('agents')->nullOnDelete(); // paid to (optional)
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->string('payment_method')->default('cash');
            $table->string('reference_no')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('payment_date');
        });

        // 2) Shipment cost payments: who saved it + which voucher it belongs to (null = single payment from the costs page)
        Schema::table('items_shipment_payments', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('paid_by')->constrained('users')->nullOnDelete();
            $table->foreignId('shipment_bill_payment_id')->nullable()->after('user_id')
                  ->constrained('shipment_bill_payments')->nullOnDelete();
        });

        // 3) Customer receipts: who received/saved it
        Schema::table('customer_payments', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('customer_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('customer_payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });

        Schema::table('items_shipment_payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shipment_bill_payment_id');
            $table->dropConstrainedForeignId('user_id');
        });

        Schema::dropIfExists('shipment_bill_payments');
    }
};
