<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        Customer::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '01700000001',
            'customer_code' => 'CUST001',
            'address' => 'Dhaka, Bangladesh'
        ]);

        Customer::create([
            'name' => 'Sarah Khan',
            'email' => 'sarah@example.com',
            'phone' => '01700000002',
            'customer_code' => 'CUST002',
            'address' => 'Chittagong, Bangladesh'
        ]);

        Customer::create([
            'name' => 'Mahmud Rahman',
            'email' => 'mahmud@example.com',
            'phone' => '01700000003',
            'customer_code' => 'CUST003',
            'address' => 'Sylhet, Bangladesh'
        ]);

        Customer::create([
            'name' => 'Ayesha Akter',
            'email' => 'ayesha@example.com',
            'phone' => '01700000004',
            'customer_code' => 'CUST004',
            'address' => 'Rajshahi, Bangladesh'
        ]);

        Customer::create([
            'name' => 'Imran Hossain',
            'email' => 'imran@example.com',
            'phone' => '01700000005',
            'customer_code' => 'CUST005',
            'address' => 'Khulna, Bangladesh'
        ]);
    }
}
