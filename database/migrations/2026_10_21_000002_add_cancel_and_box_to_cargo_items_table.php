<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) allow the new 'cancelled' status (cargo_items.status is an ENUM)
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement("ALTER TABLE cargo_items MODIFY COLUMN status ENUM(
                'pending','packed','ready_to_ship','shipping_ongoing','arrived_bd',
                'waiting_for_customs','ready_to_deliver','delivered','cancelled'
            ) NOT NULL DEFAULT 'pending'");
        }

        Schema::table('cargo_items', function (Blueprint $table) {
            // 2) cancel info
            $table->timestamp('cancelled_at')->nullable()->after('status');
            $table->string('cancel_reason')->nullable()->after('cancelled_at');
            $table->string('status_before_cancel', 30)->nullable()->after('cancel_reason');

            // 3) box grouping (display-only identifier, e.g. "BOX-1")
            $table->string('box_no', 50)->nullable()->after('written_ctn_no')->index();
        });
    }

    public function down(): void
    {
        Schema::table('cargo_items', function (Blueprint $table) {
            $table->dropColumn(['cancelled_at', 'cancel_reason', 'status_before_cancel', 'box_no']);
        });

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            DB::table('cargo_items')->where('status', 'cancelled')->update(['status' => 'pending']);
            DB::statement("ALTER TABLE cargo_items MODIFY COLUMN status ENUM(
                'pending','packed','ready_to_ship','shipping_ongoing','arrived_bd',
                'waiting_for_customs','ready_to_deliver','delivered'
            ) NOT NULL DEFAULT 'pending'");
        }
    }
};
