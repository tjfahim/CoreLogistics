<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) New table: how much of each receipt goes to which shipment
        Schema::create('customer_payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_payment_id')
                  ->constrained('customer_payments')
                  ->cascadeOnDelete();
            $table->foreignId('itemshipment_id')
                  ->constrained('items_shipments')
                  ->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->timestamps();

            $table->index(['itemshipment_id', 'customer_payment_id'], 'cpa_shipment_payment_idx');
        });

        // 2) A lump-sum receipt can cover several shipments, so the old single column must allow NULL
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE customer_payments MODIFY itemshipment_id BIGINT UNSIGNED NULL');
        } else {
            Schema::table('customer_payments', function (Blueprint $table) {
                $table->unsignedBigInteger('itemshipment_id')->nullable()->change(); // needs doctrine/dbal on Laravel <= 10
            });
        }

        // 3) Backfill: every old payment belonged to exactly one shipment
        DB::table('customer_payments')
            ->whereNotNull('itemshipment_id')
            ->orderBy('id')
            ->chunkById(500, function ($payments) {
                $rows = [];
                foreach ($payments as $p) {
                    $rows[] = [
                        'customer_payment_id' => $p->id,
                        'itemshipment_id'     => $p->itemshipment_id,
                        'amount'              => $p->amount,
                        'created_at'          => $p->created_at ?? now(),
                        'updated_at'          => now(),
                    ];
                }
                DB::table('customer_payment_allocations')->insert($rows);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_payment_allocations');
    }
};