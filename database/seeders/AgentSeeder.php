<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Agent;

class AgentSeeder extends Seeder
{
    public function run()
    {
        $freightForwarders = [
            [
                'name' => 'Alex Transport',
                'type' => 'freight_forwarder',
                'company_name' => 'AT Logistics Ltd',
                'email' => 'alex@atlogistics.com',
                'phone' => '01710000001',
                'address' => 'Dhaka, Bangladesh',
                'notes' => 'Trusted partner for Hong Kong shipments.'
            ],
            [
                'name' => 'Global Freight BD',
                'type' => 'freight_forwarder',
                'company_name' => 'Global Freight Corporation',
                'email' => 'info@globalfreight.com',
                'phone' => '01710000002',
                'address' => 'Chittagong, Bangladesh',
                'notes' => 'Handles air cargo.'
            ],
            [
                'name' => 'Skyline Cargo',
                'type' => 'freight_forwarder',
                'company_name' => 'Skyline Shipping Ltd',
                'email' => 'support@skyline.com',
                'phone' => '01710000003',
                'address' => 'Kawran Bazar, Dhaka',
                'notes' => null
            ],
            [
                'name' => 'OceanWay Logistics',
                'type' => 'freight_forwarder',
                'company_name' => 'OceanWay Co.',
                'email' => 'contact@oceanway.com',
                'phone' => '01710000004',
                'address' => 'Uttara, Dhaka',
                'notes' => 'Specializes in sea freight.'
            ],
        ];

        $cnfAgents = [
            [
                'name' => 'BD Clearing House',
                'type' => 'cnf_agent',
                'company_name' => 'BD CH Ltd',
                'email' => 'bdch@mail.com',
                'phone' => '01820000001',
                'address' => 'Kamalapur ICD',
                'notes' => 'Fast dhaka clearance.'
            ],
            [
                'name' => 'Portside CNF',
                'type' => 'cnf_agent',
                'company_name' => 'Portside BD',
                'email' => 'portside@mail.com',
                'phone' => '01820000002',
                'address' => 'Chittagong Port',
                'notes' => 'Expert for port clearance.'
            ],
            [
                'name' => 'Elite Customs BD',
                'type' => 'cnf_agent',
                'company_name' => 'Elite Customs Co.',
                'email' => 'elite@customsbd.com',
                'phone' => '01820000003',
                'address' => 'Airport Customs, Dhaka',
                'notes' => null
            ],
            [
                'name' => 'Prime CNF Agency',
                'type' => 'cnf_agent',
                'company_name' => 'Prime Logistics BD',
                'email' => 'primecnf@mail.com',
                'phone' => '01820000004',
                'address' => 'Gazipur',
                'notes' => 'Handles garment shipments.'
            ],
        ];

        foreach (array_merge($freightForwarders, $cnfAgents) as $agent) {
            Agent::create($agent);
        }
    }
}
