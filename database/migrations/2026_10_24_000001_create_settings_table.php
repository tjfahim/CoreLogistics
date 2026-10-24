<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        $now = now();
        $defaults = [
            'site_title'   => 'Techlab33 - Custom Software',
            'site_tagline' => 'Custom Software',
            'company_name' => 'TechLab33',
            'company_address' => '123 Street Name, Dhaka, Bangladesh',
            'company_phone'   => '+880 1234 56789',
            'company_email'   => 'info@techlab33.com',
            'footer_text'  => null,
            'logo'         => null,
            'logo_mini'    => null,
            'favicon'      => null,
        ];
        foreach ($defaults as $k => $v) {
            \DB::table('settings')->insert(['key' => $k, 'value' => $v, 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
