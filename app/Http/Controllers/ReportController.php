<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Http\Request;

class ReportController extends Controller
{
     public function index(Request $request)
{
    // Filter by date range if provided
    $query = Shipment::with(['costs', 'customerBills.deliveries', 'customerBills.customer']);

    if ($request->filled('from_date') && $request->filled('to_date')) {
        $query->whereBetween('created_at', [$request->from_date, $request->to_date]);
    }

    $shipments = $query->get();

    // --- Overall Summary ---
    $totalEarnings       = 0;
    $totalShipmentCost   = 0;
    $totalShipmentPaid   = 0;
    $totalCustomerPaid   = 0;

    foreach ($shipments as $shipment) {
        // Shipment totals
        $shipmentCost = $shipment->costs->sum('amount');
        $shipmentPaid = $shipment->costs->sum('paid_amount');

        $totalShipmentCost += $shipmentCost;
        $totalShipmentPaid += $shipmentPaid;

        // Customer totals (calculate paid from deliveries dynamically)
        $bills = $shipment->customerBills;
        $totalEarnings += $bills->sum('total_price');
        $totalCustomerPaid += $bills->sum(function($bill) {
            return $bill->deliveries->sum('amount');
        });
    }

    $profit = $totalEarnings - $totalShipmentCost;
    $cashOnHand = $totalCustomerPaid - $totalShipmentPaid;

    return view('admin.reports.index', compact(
        'shipments', 'totalEarnings', 'totalShipmentCost', 'profit', 'cashOnHand'
    ));
}

}
