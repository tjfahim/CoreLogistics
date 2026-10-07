<?php

namespace App\Http\Controllers;

use App\Models\LegacyCustomer;
use App\Models\LegacyRow;
use App\Models\LegacyUpload;
use App\Services\LegacyImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LegacyUploadController extends Controller
{
    public function __construct(private LegacyImportService $service) {}

    /** List of uploaded files + upload form */
    public function index()
    {
        $uploads = LegacyUpload::latest()->get();
        return view('admin.legacy.index', compact('uploads'));
    }

    /** Upload -> parse -> store in legacy_* tables -> go to review page */
    public function store(Request $request)
    {
        $request->validate([
            'file'  => ['required', 'file', 'mimes:xlsx'],
            'title' => ['nullable', 'string', 'max:255'],
            'note'  => ['nullable', 'string', 'max:1000'],
        ]);

        $file = $request->file('file');
        $hash = sha1_file($file->getRealPath());

        if ($existing = LegacyUpload::where('file_hash', $hash)->first()) {
            return redirect()->route('legacy.show', $existing)
                ->with('error', 'This exact file was already uploaded. Opened the existing one.');
        }

        $name = time() . '_' . preg_replace('/[^A-Za-z0-9.\-_]/', '_', $file->getClientOriginalName());
        $path = $file->storeAs('legacy_excel', $name, 'local');

        try {
            $upload = $this->service->stage(Storage::disk('local')->path($path), [
                'title'       => $request->input('title') ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'file_name'   => $file->getClientOriginalName(),
                'file_path'   => $path,
                'file_hash'   => $hash,
                'note'        => $request->input('note'),
                'uploaded_by' => auth()->id(),
            ]);
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            return back()->with('error', 'Could not read the file: ' . $e->getMessage());
        }

        return redirect()->route('legacy.show', $upload)->with('success', 'File stored. Please check the data below.');
    }

    /** REVIEW page: checks, flagged rows, all rows */
    public function show(Request $request, LegacyUpload $upload)
    {
        $filter = $request->input('filter', 'all');
        $q = $upload->rows()->orderBy('old_row');

        if ($filter === 'errors')   $q->where('error_count', '>', 0);
        if ($filter === 'warnings') $q->where('warning_count', '>', 0);
        if ($filter === 'flagged')  $q->whereNotNull('review_flag');
        if ($s = $request->input('search')) {
            $q->where(fn ($w) => $w->where('tracking_no', 'like', "%$s%")->orWhere('customer_name', 'like', "%$s%")
                ->orWhere('shipping_mark', 'like', "%$s%")->orWhere('shipment_no', 'like', "%$s%")
                ->orWhere('goods_name_en', 'like', "%$s%"));
        }

        $rows   = $q->paginate(50)->withQueryString();
        $checks = $this->service->checks($upload);

        return view('admin.legacy.show', compact('upload', 'rows', 'checks', 'filter'));
    }

    /** Approve = data checked, the old report becomes available */
    public function approve(LegacyUpload $upload)
    {
        if ($upload->error_rows > 0) {
            return back()->with('error', 'Cannot approve: there are rows with errors.');
        }
        $upload->update(['status' => 'approved', 'approved_at' => now()]);
        return redirect()->route('legacy.report', $upload)->with('success', 'Approved. Old report is ready.');
    }

    /** Copy into live tables (customers, shipments, cargo_items, payments) */
    public function importLive(LegacyUpload $upload)
    {
        if ($upload->status === 'staged') return back()->with('error', 'Approve the upload first.');
        try {
            $c = $this->service->promote($upload);
        } catch (\Throwable $e) {
            return back()->with('error', 'Import failed, nothing was saved: ' . $e->getMessage());
        }
        return back()->with('success', "Imported: {$c['customers']} new customers, {$c['shipments']} shipments, {$c['items']} items, {$c['payments']} payments.");
    }

    public function destroy(LegacyUpload $upload)
    {
        if ($upload->status === 'imported') {
            return back()->with('error', 'Already imported into live tables. Delete is blocked.');
        }
        Storage::disk('local')->delete($upload->file_path);
        $upload->delete(); // rows, payments, customers cascade
        return redirect()->route('legacy.index')->with('success', 'Upload deleted.');
    }

    public function download(LegacyUpload $upload)
    {
        return Storage::disk('local')->download($upload->file_path, $upload->file_name);
    }

    // ---------------------------------------------------------------
    // OLD REPORT (reads only from legacy_* tables)
    // ---------------------------------------------------------------
    public function report(Request $request, LegacyUpload $upload)
    {
        if ($upload->status === 'staged') {
            return redirect()->route('legacy.show', $upload)->with('error', 'Approve the upload to see the report.');
        }

        $byShipment = $upload->rows()
            ->selectRaw('shipment_no, warehouse, COUNT(*) items, SUM(weight_kg) weight, SUM(price) billed, SUM(paid_amount) paid, MIN(delivered_at) first_date, MAX(delivered_at) last_date')
            ->groupBy('shipment_no', 'warehouse')->orderBy('shipment_no')->get();

        $credits = LegacyCustomer::where('upload_id', $upload->id)->pluck('unapplied_credit', 'customer_code');

        $byCustomer = $upload->rows()
            ->selectRaw('customer_code, customer_name, COUNT(*) items, SUM(weight_kg) weight, SUM(price) billed, SUM(paid_amount) paid')
            ->groupBy('customer_code', 'customer_name')->orderBy('customer_name')->get()
            ->each(fn ($c) => $c->credit = (float) ($credits[$c->customer_code] ?? 0));

        // drill-down: ?shipment=179GZ or ?customer=CUST-014
        $detail = null; $detailTitle = null;
        if ($ship = $request->input('shipment')) {
            $detail = $upload->rows()->where('shipment_no', $ship)->orderBy('customer_name')->orderBy('old_row')->get();
            $detailTitle = "Shipment {$ship}";
        } elseif ($cc = $request->input('customer')) {
            $detail = $upload->rows()->where('customer_code', $cc)->orderBy('delivered_at')->orderBy('old_row')->get();
            $detailTitle = 'Customer ' . ($detail->first()->customer_name ?? $cc);
        }

        $totalCredit = (float) $credits->sum();
        return view('admin.legacy.report', compact('upload', 'byShipment', 'byCustomer', 'detail', 'detailTitle', 'totalCredit'));
    }
}
