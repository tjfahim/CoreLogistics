<?php

namespace App\Services;

use App\Models\ItemsShipment;
use App\Models\CostType;
use App\Models\Agent;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ShipmentReportService
{
    /**
     * Generate single shipment cost report
     */
    public function generateSingleShipmentReport($shipmentId)
    {
        $shipment = ItemsShipment::with([
            'costs' => function($query) {
                $query->with(['costType', 'agent', 'payments']);
            },
            'cargoItems',
            'cargoItems.customer'
        ])->findOrFail($shipmentId);

        return [
            'shipment' => $shipment,
            'summary' => [
                'total_cost' => $shipment->total_cost,
                'total_paid' => $shipment->total_paid,
                'total_due' => $shipment->total_due,
                'total_weight' => $shipment->total_weight,
                'total_cbm' => $shipment->total_cbm,
                'total_items' => $shipment->total_items,
                'pending_costs' => $shipment->pending_costs,
                'partial_costs' => $shipment->partial_costs,
                'paid_costs' => $shipment->paid_costs,
            ],
            'costs_by_type' => $this->groupCostsByType($shipment),
            'payments_by_method' => $this->groupPaymentsByMethod($shipment),
            'agent_wise_summary' => $this->getAgentWiseSummary($shipment),
            'cost_type_wise_summary' => $this->getCostTypeWiseSummary($shipment),
        ];
    }

    /**
     * Get cost type wise summary with paid and due amounts
     */
    private function getCostTypeWiseSummary($shipment)
    {
        $costTypeSummary = [];
        
        foreach ($shipment->costs as $cost) {
            $costTypeName = $cost->costType->name;
            
            if (!isset($costTypeSummary[$costTypeName])) {
                $costTypeSummary[$costTypeName] = [
                    'type' => $cost->costType->type,
                    'total_amount' => 0,
                    'total_paid' => 0,
                    'total_due' => 0,
                    'count' => 0,
                    'fixed_cost_total' => 0,
                    'per_kg_cost_total' => 0,
                ];
            }
            
            $costTypeSummary[$costTypeName]['total_amount'] += $cost->amount;
            $costTypeSummary[$costTypeName]['total_paid'] += $cost->paid_amount;
            $costTypeSummary[$costTypeName]['total_due'] += $cost->due_amount;
            $costTypeSummary[$costTypeName]['count']++;
            
            if ($cost->costType->type === 'fixed') {
                $costTypeSummary[$costTypeName]['fixed_cost_total'] += $cost->amount;
            } elseif ($cost->costType->type === 'per_kg') {
                $costTypeSummary[$costTypeName]['per_kg_cost_total'] += $cost->amount;
            }
        }
        
        return $costTypeSummary;
    }

    /**
     * Group costs by type for a shipment
     */
    private function groupCostsByType($shipment)
    {
        return $shipment->costs->groupBy('costType.name')->map(function($costs) {
            return [
                'total_amount' => $costs->sum('amount'),
                'total_paid' => $costs->sum('paid_amount'),
                'total_due' => $costs->sum('due_amount'),
                'count' => $costs->count(),
            ];
        });
    }

    /**
     * Get agent-wise summary
     */
    private function getAgentWiseSummary($shipment)
    {
        $agentCosts = $shipment->costs->whereNotNull('agent_id')->groupBy('agent_id');
        
        $summary = [];
        foreach ($agentCosts as $agentId => $costs) {
            $agent = $costs->first()->agent;
            $summary[$agent->name] = [
                'total_amount' => $costs->sum('amount'),
                'total_paid' => $costs->sum('paid_amount'),
                'total_due' => $costs->sum('due_amount'),
                'cost_count' => $costs->count(),
            ];
        }
        
        return $summary;
    }

    /**
     * Generate multiple shipments cost report
     */
    public function generateMultipleShipmentsReport($filters = [])
    {
        $query = ItemsShipment::with(['costs.costType', 'costs.agent', 'cargoItems']);
        
        // Apply filters
        if (!empty($filters['start_date'])) {
            $query->where('shipment_date', '>=', $filters['start_date']);
        }
        
        if (!empty($filters['end_date'])) {
            $query->where('shipment_date', '<=', $filters['end_date']);
        }
        
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        $shipments = $query->get();
        
        // Get all costs for these shipments
        $allCosts = DB::table('items_shipment_costs')
            ->whereIn('items_shipment_id', $shipments->pluck('id'))
            ->get();
            
        return [
            'shipments' => $shipments,
            'summary' => [
                'total_shipments' => $shipments->count(),
                'grand_total_cost' => $allCosts->sum('amount'),
                'grand_total_paid' => $allCosts->sum('paid_amount'),
                'grand_total_due' => $allCosts->sum('due_amount'),
                'total_weight' => $shipments->sum(function($shipment) {
                    return $shipment->total_weight;
                }),
                'total_cbm' => $shipments->sum(function($shipment) {
                    return $shipment->total_cbm;
                }),
                'total_items' => $shipments->sum(function($shipment) {
                    return $shipment->total_items;
                }),
            ],
            'monthly_summary' => $this->getMonthlySummary($shipments),
            'cost_type_summary' => $this->getCostTypeSummary($allCosts),
            'agent_summary' => $this->getAgentSummary($allCosts),
            'payment_status_summary' => $this->getPaymentStatusSummary($allCosts),
        ];
    }

    /**
     * Get cost type summary for multiple shipments with paid and due amounts
     */
    private function getCostTypeSummary($allCosts)
    {
        // Group costs by cost_type_id
        $groupedCosts = [];
        foreach ($allCosts as $cost) {
            $costTypeId = $cost->cost_type_id;
            
            if (!isset($groupedCosts[$costTypeId])) {
                $groupedCosts[$costTypeId] = [
                    'costs' => collect(),
                    'total_amount' => 0,
                    'total_paid' => 0,
                    'total_due' => 0,
                ];
            }
            
            $groupedCosts[$costTypeId]['costs']->push($cost);
            $groupedCosts[$costTypeId]['total_amount'] += $cost->amount;
            $groupedCosts[$costTypeId]['total_paid'] += $cost->paid_amount;
            $groupedCosts[$costTypeId]['total_due'] += $cost->due_amount;
        }
        
        // Get cost type details
        $costTypes = CostType::whereIn('id', array_keys($groupedCosts))->get();
        
        return $costTypes->map(function($costType) use ($groupedCosts) {
            $data = $groupedCosts[$costType->id] ?? [
                'total_amount' => 0,
                'total_paid' => 0,
                'total_due' => 0,
                'costs' => collect(),
            ];
            
            return [
                'name' => $costType->name,
                'type' => $costType->type,
                'total_amount' => $data['total_amount'],
                'total_paid' => $data['total_paid'],
                'total_due' => $data['total_due'],
                'count' => $data['costs']->count(),
            ];
        });
    }

    /**
     * Get agent summary for multiple shipments
     */
    private function getAgentSummary($allCosts)
    {
        // Group costs by agent_id
        $groupedCosts = [];
        foreach ($allCosts as $cost) {
            $agentId = $cost->agent_id;
            
            if (!$agentId) continue;
            
            if (!isset($groupedCosts[$agentId])) {
                $groupedCosts[$agentId] = [
                    'costs' => collect(),
                    'total_amount' => 0,
                    'total_paid' => 0,
                    'total_due' => 0,
                ];
            }
            
            $groupedCosts[$agentId]['costs']->push($cost);
            $groupedCosts[$agentId]['total_amount'] += $cost->amount;
            $groupedCosts[$agentId]['total_paid'] += $cost->paid_amount;
            $groupedCosts[$agentId]['total_due'] += $cost->due_amount;
        }
        
        // Get agent details
        $agents = Agent::whereIn('id', array_keys($groupedCosts))->get();
        
        return $agents->map(function($agent) use ($groupedCosts) {
            $data = $groupedCosts[$agent->id] ?? [
                'total_amount' => 0,
                'total_paid' => 0,
                'total_due' => 0,
                'costs' => collect(),
            ];
            
            return [
                'name' => $agent->name,
                'company' => $agent->company_name,
                'total_amount' => $data['total_amount'],
                'total_paid' => $data['total_paid'],
                'total_due' => $data['total_due'],
                'cost_count' => $data['costs']->count(),
            ];
        })->filter(function($agent) {
            return $agent['total_amount'] > 0;
        });
    }

    /**
     * Group payments by method
     */
    private function groupPaymentsByMethod($shipment)
    {
        $payments = collect();
        foreach ($shipment->costs as $cost) {
            foreach ($cost->payments as $payment) {
                $payments->push($payment);
            }
        }
        
        return $payments->groupBy('payment_method')->map(function($payments) {
            return [
                'total_amount' => $payments->sum('amount'),
                'count' => $payments->count(),
            ];
        });
    }

    /**
     * Get monthly summary for multiple shipments
     */
    private function getMonthlySummary($shipments)
    {
        $monthlyData = [];
        
        foreach ($shipments as $shipment) {
            $month = Carbon::parse($shipment->shipment_date)->format('Y-m');
            
            if (!isset($monthlyData[$month])) {
                $monthlyData[$month] = [
                    'shipment_count' => 0,
                    'total_cost' => 0,
                    'total_paid' => 0,
                    'total_due' => 0,
                ];
            }
            
            $monthlyData[$month]['shipment_count']++;
            $monthlyData[$month]['total_cost'] += $shipment->total_cost;
            $monthlyData[$month]['total_paid'] += $shipment->total_paid;
            $monthlyData[$month]['total_due'] += $shipment->total_due;
        }
        
        return $monthlyData;
    }

    /**
     * Get payment status summary
     */
    private function getPaymentStatusSummary($allCosts)
    {
        $statuses = ['pending', 'partial', 'paid'];
        $summary = [];
        
        foreach ($statuses as $status) {
            $costs = $allCosts->where('status', $status);
            $summary[$status] = [
                'count' => $costs->count(),
                'total_amount' => $costs->sum('amount'),
                'total_paid' => $costs->sum('paid_amount'),
                'total_due' => $costs->sum('due_amount'),
                'percentage' => $allCosts->sum('amount') > 0 ? 
                    ($costs->sum('amount') / $allCosts->sum('amount')) * 100 : 0,
            ];
        }
        
        return $summary;
    }
}