<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /* ---- 1) Third parties (companies that ship through us) ---- */
        Schema::create('third_parties', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 40)->nullable()->unique();
            $table->string('contact_person')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->text('note')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('cargo_items', function (Blueprint $table) {
            $table->foreignId('third_party_id')->nullable()->after('customer_id')
                  ->constrained('third_parties')->nullOnDelete();
        });

        // money a third party pays us (BDT is the final value, the foreign part is kept for history)
        Schema::create('third_party_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('third_party_id')->constrained('third_parties')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('amount', 15, 2);                       // final BDT
            $table->string('currency', 10)->default('bdt');
            $table->decimal('foreign_amount', 15, 2)->nullable();
            $table->decimal('exchange_rate', 15, 4)->default(1);
            $table->date('payment_date');
            $table->string('payment_method')->default('cash');
            $table->string('reference_no')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('payment_date');
        });

        /* ---- 2) When did an item reach a status? (for daily / date-range reports) ---- */
        Schema::create('cargo_item_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cargo_item_id')->constrained('cargo_items')->cascadeOnDelete();
            $table->string('status', 30);
            $table->timestamp('changed_at')->useCurrent();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->index(['status', 'changed_at']);
        });

        // history before this feature: best guess = last update time of the item's current status
        DB::table('cargo_items')->whereNotIn('status', ['pending', 'packed'])->orderBy('id')->chunkById(500, function ($rows) {
            DB::table('cargo_item_status_logs')->insert($rows->map(fn ($r) => [
                'cargo_item_id' => $r->id,
                'status'        => $r->status,
                'changed_at'    => $r->updated_at ?? now(),
            ])->all());
        });

        /* ---- 3) Currency on every payment (final BDT stays in `amount`) ---- */
        foreach (['shipment_bill_payments', 'items_shipment_payments', 'customer_payments'] as $t) {
            Schema::table($t, function (Blueprint $table) {
                $table->string('currency', 10)->default('bdt');
                $table->decimal('foreign_amount', 15, 2)->nullable();
                $table->decimal('exchange_rate', 15, 4)->default(1);
            });
        }
    }

    public function down(): void
    {
        foreach (['shipment_bill_payments', 'items_shipment_payments', 'customer_payments'] as $t) {
            Schema::table($t, fn (Blueprint $table) => $table->dropColumn(['currency', 'foreign_amount', 'exchange_rate']));
        }
        Schema::dropIfExists('cargo_item_status_logs');
        Schema::dropIfExists('third_party_payments');
        Schema::table('cargo_items', fn (Blueprint $table) => $table->dropConstrainedForeignId('third_party_id'));
        Schema::dropIfExists('third_parties');
    }
};
