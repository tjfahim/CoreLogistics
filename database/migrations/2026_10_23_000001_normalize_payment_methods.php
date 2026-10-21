<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    private array $map = ['bank' => 'bank_transfer', 'check' => 'cheque', 'card' => 'bank_transfer'];
    private array $tables = ['shipment_bill_payments', 'items_shipment_payments', 'customer_payments', 'third_party_payments'];

    public function up(): void
    {
        foreach ($this->tables as $t) {
            if (!\Schema::hasTable($t) || !\Schema::hasColumn($t, 'payment_method')) continue;
            foreach ($this->map as $from => $to) {
                DB::table($t)->where('payment_method', $from)->update(['payment_method' => $to]);
            }
        }
    }

    public function down(): void {}
};
