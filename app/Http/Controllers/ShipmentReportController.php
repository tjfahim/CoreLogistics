<?php

namespace App\Http\Controllers;

use App\Services\ShipmentReportService;
use App\Models\ItemsShipment;
use Illuminate\Http\Request;

class ShipmentReportController extends Controller
{
    protected $reportService;

    public function __construct(ShipmentReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Display report dashboard
     */
    public function dashboard()
    {
        $shipments = ItemsShipment::with('costs')->latest()->paginate(10);
        
        // Get summary statistics
        $totalShipments = ItemsShipment::count();
        $totalCost = ItemsShipment::with('costs')->get()->sum('total_cost');
        $totalPaid = ItemsShipment::with('costs')->get()->sum('total_paid');
        $totalDue = ItemsShipment::with('costs')->get()->sum('total_due');
        
        $activeShipments = ItemsShipment::where('status', 'active')->count();
        $completedShipments = ItemsShipment::where('status', 'completed')->count();
        
        return view('admin.reports.dashboard', compact(
            'shipments',
            'totalShipments',
            'totalCost',
            'totalPaid',
            'totalDue',
            'activeShipments',
            'completedShipments'
        ));
    }

    /**
     * Generate single shipment report
     */
    public function singleShipmentReport($id)
    {
        $report = $this->reportService->generateSingleShipmentReport($id);
        
        return view('admin.reports.single-shipment', compact('report'));
    }

  

    /**
     * Download report as PDF
     */
   public function downloadPdf($type, $id = null, Request $request = null)
{
    // Laravel auto-injects Request if it's type-hinted and not bound to route param
    $request = $request ?? request();

    try {
        if ($type === 'single' && $id) {
            $report = $this->reportService->generateSingleShipmentReport($id);
            $pdf = \PDF::loadView('admin.reports.pdf.single-shipment', compact('report'));
            $filename = "shipment-report-{$report['shipment']->shipment_name}-" . date('Y-m-d') . ".pdf";
            return $pdf->download($filename);
        }

    } catch (\Exception $e) {
        return back()->with('error', 'Error generating PDF: ' . $e->getMessage());
    }
}

    /**
     * Export report as Excel
     */
    public function exportExcel($type, $id = null, Request $request = null)
    {
        // You can implement Excel export using Laravel Excel package
        // This is a basic implementation
        if ($type === 'single' && $id) {
            $report = $this->reportService->generateSingleShipmentReport($id);
            // Export logic here
        } 
        return response()->json(['message' => 'Excel export feature to be implemented']);
    }
}