<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CargoItem;
use App\Models\Invoice;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    public function searchInvoice(Request $request)
    {
        $request->validate([
            'invoice_number' => ['required', 'string', 'max:100'],
        ]);

        $invoice = Invoice::with(['customer', 'itemshipment'])
            ->where('invoice_number', trim($request->invoice_number))
            ->first();

        if (!$invoice) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'No invoice found for: ' . $request->invoice_number);
        }

        return redirect()->route('shipments.billing', [
            'itemshipment' => $invoice->itemshipment_id,
            'highlight'    => $invoice->customer_id,
        ]);
    }

    public function searchTracking(Request $request)
    {
        $request->validate([
            'tracking_no' => ['required', 'string', 'max:100'],
        ]);

        $tracking = trim($request->tracking_no);

        $item = CargoItem::where('tracking_no', $tracking)->first();

        if (!$item) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'No item found with Tracking No: ' . $tracking);
        }

        // Uses the existing admin tracking view
        return view('admin.cargo_items.tracking_view', compact('item'));
    }
}