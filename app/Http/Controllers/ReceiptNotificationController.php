<?php

namespace App\Http\Controllers;

use App\Exports\SheetsExport;
use App\Models\CargoItem;
use App\Models\ReceiptStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Customer notification tracker.
 * Cargo items are grouped by Receipt_NO; for every warehouse status we remember
 * (manually) whether the customer was told.
 */
class ReceiptNotificationController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Gate::allows('view-notifications'), 403);

        $statuses = CargoItem::STATUS_FLOW;
        $keys     = array_keys($statuses);

        $query = $this->groupedQuery($request, $keys);

        if ($request->boolean('export')) {
            return $this->export($query->get(), $statuses, $keys);
        }

        $receipts   = $query->paginate(50)->withQueryString();
        $receiptNos = $receipts->pluck('receipt_no')->all();

        // customers behind each receipt (empty until the customer is assigned)
        $customers = DB::table('cargo_items as ci')
            ->join('customers as c', 'c.id', '=', 'ci.customer_id')
            ->whereIn('ci.receipt_no', $receiptNos)
            ->select('ci.receipt_no', 'c.id', 'c.name', 'c.customer_code', 'c.phone')
            ->distinct()
            ->get()
            ->groupBy('receipt_no');

        // what was already notified
        $notified = ReceiptStatusNotification::with('user:id,name')
            ->whereIn('receipt_no', $receiptNos)
            ->get()
            ->groupBy('receipt_no')
            ->map(fn ($g) => $g->keyBy('status'));

        $pending = ReceiptStatusNotification::pendingCounts();

        $withoutReceipt = CargoItem::where(fn ($q) => $q->whereNull('receipt_no')->orWhere('receipt_no', ''))->count();

        return view('admin.notifications.index', compact(
            'receipts', 'customers', 'notified', 'statuses', 'keys', 'pending', 'withoutReceipt'
        ));
    }

    /** Tick / untick one cell. Returns JSON for the page to update itself. */
    public function toggle(Request $request)
    {
        abort_unless(Gate::allows('manage-notifications'), 403);

        $data = $request->validate([
            'receipt_no'  => 'required|string|max:50',
            'status'      => ['required', Rule::in(array_keys(CargoItem::STATUS_FLOW))],
            'is_notified' => 'required|boolean',
            'note'        => 'nullable|string|max:255',
        ]);

        if (!CargoItem::where('receipt_no', $data['receipt_no'])->exists()) {
            return response()->json(['ok' => false, 'message' => 'Unknown receipt.'], 422);
        }

        $row = $this->save($data['receipt_no'], $data['status'], (bool) $data['is_notified'], $data['note'] ?? null);

        return response()->json([
            'ok'          => true,
            'is_notified' => $row->is_notified,
            'date'        => optional($row->notified_at)->format('d M, H:i'),
            'user'        => optional($row->user)->name,
            'pending'     => ReceiptStatusNotification::pendingCounts(),
        ]);
    }

    /** Tick / untick one status for many receipts at once. */
    public function bulk(Request $request)
    {
        abort_unless(Gate::allows('manage-notifications'), 403);

        $data = $request->validate([
            'receipt_nos'   => 'required|array|min:1|max:500',
            'receipt_nos.*' => 'string|max:50',
            'status'        => ['required', Rule::in(array_keys(CargoItem::STATUS_FLOW))],
            'is_notified'   => 'required|boolean',
        ]);

        $valid = CargoItem::whereIn('receipt_no', $data['receipt_nos'])->distinct()->pluck('receipt_no');

        DB::transaction(function () use ($valid, $data) {
            foreach ($valid as $receiptNo) {
                $this->save($receiptNo, $data['status'], (bool) $data['is_notified']);
            }
        });

        $label = CargoItem::STATUS_FLOW[$data['status']];

        return back()->with('success', $valid->count() . ' receipt(s) marked ' .
            ($data['is_notified'] ? 'as notified' : 'as NOT notified') . ' for "' . $label . '".');
    }

    private function save(string $receiptNo, string $status, bool $notified, ?string $note = null): ReceiptStatusNotification
    {
        $values = [
            'is_notified' => $notified,
            'notified_at' => $notified ? now() : null,
            'notified_by' => $notified ? Auth::id() : null,
        ];
        if ($note !== null) {
            $values['note'] = $note;
        }

        $row = ReceiptStatusNotification::updateOrCreate(
            ['receipt_no' => $receiptNo, 'status' => $status],
            $values
        );

        return $row->load('user:id,name');
    }

    /** One row per receipt_no with item counts per status. */
    private function groupedQuery(Request $request, array $keys)
    {
        $sums = collect($keys)
            ->map(fn ($k) => "SUM(CASE WHEN ci.status = '{$k}' THEN 1 ELSE 0 END) as s_{$k}")
            ->implode(', ');

        $q = DB::table('cargo_items as ci')
            ->leftJoin('customers as c', 'c.id', '=', 'ci.customer_id')
            ->whereNotNull('ci.receipt_no')
            ->where('ci.receipt_no', '<>', '')
            ->groupBy('ci.receipt_no')
            ->selectRaw("ci.receipt_no, COUNT(*) as items, SUM(ci.weight_kg) as weight,
                         MIN(ci.shipping_mark) as shipping_mark, MAX(ci.updated_at) as last_update,
                         SUM(CASE WHEN ci.customer_id IS NULL THEN 1 ELSE 0 END) as unassigned, {$sums}")
            ->orderByDesc('last_update')
            ->orderByDesc('ci.receipt_no');

        if ($term = trim((string) $request->input('search'))) {
            $like = '%' . $term . '%';
            $q->where(function ($w) use ($like) {
                $w->where('ci.receipt_no', 'like', $like)
                  ->orWhere('ci.shipping_mark', 'like', $like)
                  ->orWhere('c.name', 'like', $like)
                  ->orWhere('c.customer_code', 'like', $like)
                  ->orWhere('c.phone', 'like', $like);
            });
        }

        if ($request->input('assigned') === 'no') {
            $q->havingRaw('SUM(CASE WHEN ci.customer_id IS NULL THEN 1 ELSE 0 END) > 0');
        }

        // status + notified filter: "receipts that reached STATUS and the customer is (not) told"
        $status = $request->input('status');
        if ($status && in_array($status, $keys, true)) {
            $reached = "'" . implode("','", array_slice($keys, array_search($status, $keys, true))) . "'";
            $q->havingRaw("SUM(CASE WHEN ci.status IN ({$reached}) THEN 1 ELSE 0 END) > 0");

            $exists = "EXISTS (SELECT 1 FROM receipt_status_notifications n
                               WHERE n.receipt_no = ci.receipt_no AND n.status = ? AND n.is_notified = 1)";

            if ($request->input('notified') === 'no') {
                $q->havingRaw("NOT {$exists}", [$status]);
            } elseif ($request->input('notified') === 'yes') {
                $q->havingRaw($exists, [$status]);
            }
        }

        return $q;
    }

    private function export($rows, array $statuses, array $keys)
    {
        $receiptNos = $rows->pluck('receipt_no')->all();

        $customers = DB::table('cargo_items as ci')
            ->join('customers as c', 'c.id', '=', 'ci.customer_id')
            ->whereIn('ci.receipt_no', $receiptNos)
            ->select('ci.receipt_no', 'c.name', 'c.phone')
            ->distinct()->get()->groupBy('receipt_no');

        $notified = ReceiptStatusNotification::with('user:id,name')
            ->whereIn('receipt_no', $receiptNos)->get()
            ->groupBy('receipt_no')->map(fn ($g) => $g->keyBy('status'));

        $out = [];
        foreach ($rows as $r) {
            $row = [
                $r->receipt_no,
                $customers->get($r->receipt_no, collect())->pluck('name')->implode(', ') ?: 'Not assigned',
                $customers->get($r->receipt_no, collect())->pluck('phone')->filter()->implode(', '),
                (int) $r->items,
                (float) $r->weight,
            ];

            foreach ($keys as $i => $k) {
                $reached = 0;
                foreach (array_slice($keys, $i) as $kk) {
                    $reached += (int) $r->{'s_' . $kk};
                }
                $n = optional($notified->get($r->receipt_no))->get($k);

                if ($n && $n->is_notified) {
                    $row[] = 'Notified ' . optional($n->notified_at)->format('d M Y H:i') . ($n->user ? ' by ' . $n->user->name : '');
                } elseif ($reached > 0) {
                    $row[] = 'PENDING (' . $reached . '/' . $r->items . ' items)';
                } else {
                    $row[] = '-';
                }
            }

            $out[] = $row;
        }

        $sheet = [
            'title'    => 'Notifications',
            'heading'  => 'Customer Notification Tracker',
            'subtitle' => 'Generated ' . now()->format('d M Y H:i') . ' - ' . count($out) . ' receipt(s)',
            'headings' => array_merge(['Receipt No', 'Customer', 'Phone', 'Items', 'Weight (kg)'], array_values($statuses)),
            'rows'     => $out,
            'money'    => [4],
            'int'      => [3],
        ];

        return Excel::download(new SheetsExport([$sheet]), 'customer-notifications-' . date('Y-m-d') . '.xlsx');
    }
}
