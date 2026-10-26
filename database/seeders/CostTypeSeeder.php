<?php

namespace Database\Seeders;

use App\Models\CostType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CostTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
          $costTypes = [
            ['name' => 'Freight Agent Cost', 'type' => 'per_kg', 'description' => 'Cost per kg for freight forwarder', 'have_agent' => true,'have_partial_payment'=> true],
            ['name' => 'Freight Agent Cost CBM', 'type' => 'per_kg', 'description' => 'Cost per kg for freight forwarder', 'have_agent' => true,'have_partial_payment'=> true],
            ['name' => 'CNF Agent Cost', 'type' => 'per_kg', 'description' => 'Cost per kg for CNF agent', 'have_agent' => true,'have_partial_payment'=> true],
            ['name' => 'Transport Cost Chine warehouse to freight', 'type' => 'fixed', 'description' => 'Transportation cost', 'have_agent' => false,'have_partial_payment'=> false],
            ['name' => 'Labor Cost Chine warehouse to freight', 'type' => 'fixed', 'description' => 'Loading/unloading labor cost' , 'have_agent' => false,'have_partial_payment'=> false],
            ['name' => 'Documentation Cost', 'type' => 'fixed', 'description' => 'Documentation related charges', 'have_agent' => false,'have_partial_payment'=> false],
            ['name' => 'Bill of Lading Cost', 'type' => 'fixed', 'description' => 'Cost for Bill of Lading', 'have_agent' => false,'have_partial_payment'=> false],
            ['name' => 'LC Personal', 'type' => 'fixed', 'description' => 'LC personal charges', 'have_agent' => false,'have_partial_payment'=> false],
            ['name' => 'LC Other', 'type' => 'fixed', 'description' => 'Other LC related costs', 'have_agent' => false,'have_partial_payment'=> false],
            ['name' => 'Tax', 'type' => 'fixed', 'description' => 'Tax charges', 'have_agent' => false,'have_partial_payment'=> false],
            ['name' => 'Airport Warehouse', 'type' => 'fixed', 'description' => 'Airport warehouse bill', 'have_agent' => false,'have_partial_payment'=> false],
            ['name' => 'Unloading Cost airport warehouse to our warehouse', 'type' => 'fixed', 'description' => 'Unloading cost at warehouse', 'have_agent' => false,'have_partial_payment'=> false],
            ['name' => 'Miscellaneous airport warehouse to our warehouse', 'type' => 'fixed', 'description' => 'Other miscellaneous costs', 'have_agent' => false,'have_partial_payment'=> false],
        ];

        foreach ($costTypes as $costType) {
            CostType::create($costType);
        }
    }
}
