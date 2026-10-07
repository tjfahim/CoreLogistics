<?php

namespace App\Services;

use App\Models\CargoItem;
use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\CustomerPaymentItem;
use App\Models\ItemsShipment;
use App\Models\LegacyCustomer;
use App\Models\LegacyPayment;
use App\Models\LegacyRow;
use App\Models\LegacyUpload;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LegacyImportService
{
    /** Balance (money) at or below this counts as fully paid (old file has rounding dues like 0.2). */
    private const PAID_TOLERANCE = 5.0;

    // =====================================================================
    // STEP 1: read the Excel file and store it in the legacy_* tables
    // =====================================================================
    public function stage(string $fullPath, array $meta): LegacyUpload
    {
        $book = IOFactory::load($fullPath);

        foreach (['Cargo_Items', 'Payments', 'Customers'] as $name) {
            if (!$book->getSheetByName($name)) {
                throw new \RuntimeException("Sheet '{$name}' not found. Upload the *_import_ready.xlsx file.");
            }
        }

        $items     = $this->sheetToAssoc($book->getSheetByName('Cargo_Items'));
        $payments  = $this->sheetToAssoc($book->getSheetByName('Payments'));
        $customers = $this->sheetToAssoc($book->getSheetByName('Customers'));

        return DB::transaction(function () use ($book, $items, $payments, $customers, $meta) {
            $upload = LegacyUpload::create($meta + [
                'source_totals' => $this->readSourceTotals($book),
            ]);

            // ---- rows ----
            $seenTracking = [];
            $now = now();
            $batch = [];
            $errorRows = $warnRows = 0;
            $sumW = $sumBilled = $sumPaid = 0;

            foreach ($items as $r) {
                if (!filled($r['customer_code'] ?? null) && !filled($r['tracking_no'] ?? null)) {
                    continue; // blank/total line
                }

                $row = [
                    'upload_id'       => $upload->id,
                    'old_row'         => $this->int($r['old_row'] ?? null),
                    'delivered_at'    => $this->date($r['delivered_at'] ?? null),
                    'customer_code'   => $this->str($r['customer_code'] ?? null),
                    'customer_name'   => $this->str($r['customer_name'] ?? null),
                    'shipment_no'     => $this->str($r['shipment_no'] ?? null),
                    'warehouse'       => $this->str($r['warehouse'] ?? null),
                    'shipping_mark'   => $this->str($r['shipping_mark'] ?? null),
                    'tracking_no'     => $this->str($r['tracking_no'] ?? null),
                    'tracking_source' => $this->str($r['tracking_source'] ?? null),
                    'goods_name_en'   => $this->str($r['goods_name_en'] ?? null),
                    'pack_no'         => $this->int($r['pack_no'] ?? null),
                    'pack_note'       => $this->str($r['pack_note'] ?? null),
                    'weight_kg'       => (float) ($r['weight_kg'] ?? 0),
                    'price_per_kg'    => is_numeric($r['price_per_kg'] ?? null) ? (float) $r['price_per_kg'] : null,
                    'price'           => (float) ($r['price'] ?? 0),
                    'receiver_name'   => $this->str($r['receiver_name'] ?? null),
                    'receiver_phone'  => $this->phone($r['receiver_phone'] ?? null),
                    'status'          => $this->str($r['status'] ?? null) ?: 'delivered',
                    'paid_amount'     => (float) ($r['paid_amount'] ?? 0),
                    'name_as_written' => $this->str($r['name_as_written'] ?? null),
                    'review_flag'     => $this->str($r['review_flag'] ?? null),
                    'import_status'   => 'staged',
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ];

                // ---- validation ----
                $errors = $warnings = [];
                if (!$row['customer_code'])              $errors[]   = 'Customer missing';
                if (!$row['shipment_no'])                $errors[]   = 'Shipment missing';
                if (!$row['tracking_no'])                $errors[]   = 'Tracking no missing';
                if (!$row['goods_name_en'])              $errors[]   = 'Goods name missing';
                if ($row['weight_kg'] <= 0)              $errors[]   = 'Weight is zero';
                if ($row['paid_amount'] > $row['price'] + 0.01) $errors[] = 'Paid is more than price';
                if ($row['tracking_no']) {
                    if (isset($seenTracking[$row['tracking_no']])) $errors[] = 'Duplicate tracking no in file';
                    $seenTracking[$row['tracking_no']] = true;
                }
                if (!$row['delivered_at'])               $warnings[] = 'No delivery date';
                if (!$row['receiver_phone'])             $warnings[] = 'No receiver phone';
                elseif (!preg_match('/^01[3-9]\d{8}$/', $row['receiver_phone'])) $warnings[] = 'Receiver phone looks wrong';
                if ($row['pack_no'] === null)            $warnings[] = 'Carton count missing';
                if ($row['review_flag'])                 $warnings[] = 'Flagged in Excel: ' . $row['review_flag'];

                $row['issues'] = json_encode(['errors' => $errors, 'warnings' => $warnings]);
                $row['error_count'] = count($errors);
                $row['warning_count'] = count($warnings);
                $errors   && $errorRows++;
                $warnings && !$errors && $warnRows++;

                $sumW += $row['weight_kg']; $sumBilled += $row['price']; $sumPaid += $row['paid_amount'];
                $batch[] = $row;

                if (count($batch) >= 200) { LegacyRow::insert($batch); $batch = []; }
            }
            if ($batch) LegacyRow::insert($batch);

            // ---- payments ----
            foreach ($payments as $p) {
                if (!filled($p['customer_code'] ?? null) || !is_numeric($p['amount'] ?? null)) continue; // TOTAL / check lines
                LegacyPayment::create([
                    'upload_id'      => $upload->id,
                    'customer_code'  => $this->str($p['customer_code']),
                    'customer_name'  => $this->str($p['customer_name'] ?? null),
                    'shipment_no'    => $this->str($p['shipment_no'] ?? null),
                    'payment_date'   => $this->date($p['payment_date'] ?? null),
                    'amount'         => (float) $p['amount'],
                    'payment_method' => $this->str($p['payment_method'] ?? null) ?: 'cash',
                    'reference_no'   => $this->str($p['reference_no'] ?? null),
                    'notes'          => $this->str($p['notes'] ?? null),
                ]);
            }

            // ---- customers ----
            foreach ($customers as $c) {
                if (!filled($c['customer_code'] ?? null)) continue; // TOTAL line
                LegacyCustomer::create([
                    'upload_id'        => $upload->id,
                    'customer_code'    => $this->str($c['customer_code']),
                    'name'             => $this->str($c['name'] ?? null) ?: $this->str($c['customer_code']),
                    'phone'            => $this->phone($c['phone (fill in)'] ?? null),
                    'aliases'          => $this->str($c['names written in old file'] ?? null),
                    'receivers'        => $this->str($c['receivers seen (name + phone)'] ?? null),
                    'unapplied_credit' => (float) ($c['unapplied_credit'] ?? 0),
                    'client_dues_list' => is_numeric($c['client_dues_list (old, bottom of Sheet1)'] ?? null)
                        ? (float) $c['client_dues_list (old, bottom of Sheet1)'] : null,
                ]);
            }

            $upload->update([
                'total_rows'   => $upload->rows()->count(),
                'error_rows'   => $errorRows,
                'warning_rows' => $warnRows,
                'total_weight' => $sumW,
                'total_billed' => $sumBilled,
                'total_paid'   => $sumPaid,
            ]);

            return $upload;
        });
    }

    /**
     * Checks shown on the review page: does what we stored match what the Excel says?
     */
    public function checks(LegacyUpload $u): array
    {
        $src      = $u->source_totals ?? [];
        $credit   = (float) $u->customers()->sum('unapplied_credit');
        $payments = (float) $u->payments()->sum('amount');

        $c = [
            ['Item lines',       $u->total_rows,   $src['lines']    ?? null, 0],
            ['Total weight (kg)', $u->total_weight, $src['weight']   ?? null, 3],
            ['Total billed',      $u->total_billed, $src['billed']   ?? null, 2],
            ['Payments received (allocated + unapplied credit)', $u->total_paid + $credit, $src['payments'] ?? null, 2],
        ];
        $out = [];
        foreach ($c as [$label, $ours, $theirs, $dec]) {
            $out[] = ['label' => $label, 'ours' => $ours, 'excel' => $theirs,
                      'ok' => $theirs === null ? null : abs($ours - $theirs) < 0.01 + (0.5 * 10 ** -$dec)];
        }
        $out[] = ['label' => 'Payments sheet total = paid on rows', 'ours' => $payments, 'excel' => $u->total_paid,
                  'ok' => abs($payments - $u->total_paid) < 0.01];
        return $out;
    }

    // =====================================================================
    // STEP 3 (optional): copy approved legacy data into the LIVE tables
    // =====================================================================
    public function promote(LegacyUpload $u): array
    {
        if ($u->status === 'imported') throw new \RuntimeException('Already imported.');
        if ($u->error_rows > 0)        throw new \RuntimeException('Fix the error rows first.');

        $hasReceiver = Schema::hasColumn('cargo_items', 'receiver_name');
        $hasDelivered = Schema::hasColumn('cargo_items', 'delivered_at');
        $count = ['customers' => 0, 'shipments' => 0, 'items' => 0, 'payments' => 0];

        DB::transaction(function () use ($u, $hasReceiver, $hasDelivered, &$count) {
            // 1. customers
            $customerId = [];
            foreach ($u->customers as $lc) {
                $cust = Customer::where('customer_code', $lc->customer_code)->first();
                if (!$cust) {
                    $cust = new Customer();
                    $fill = ['customer_code' => $lc->customer_code, 'name' => $lc->name];
                    if (Schema::hasColumn('customers', 'phone')) $fill['phone'] = $lc->phone;
                    $cust->forceFill($fill)->save();
                    $count['customers']++;
                }
                $customerId[$lc->customer_code] = $cust->id;
                $lc->update(['customer_id' => $cust->id]);
            }

            // 2. shipments
            $shipmentId = [];
            foreach ($u->rows()->select('shipment_no')->distinct()->pluck('shipment_no') as $no) {
                $date = $u->rows()->where('shipment_no', $no)->max('delivered_at');
                $sh = ItemsShipment::where('shipment_name', $no)->first();
                if (!$sh) {
                    $sh = new ItemsShipment();
                    $sh->forceFill(['shipment_name' => $no, 'shipment_date' => $date, 'status' => 'delivered',
                                    'note' => "Legacy import #{$u->id}"])->save();
                    $count['shipments']++;
                }
                $shipmentId[$no] = $sh->id;
            }

            // 3. cargo items (+ link to shipment)
            $itemFor = [];
            foreach ($u->rows()->orderBy('id')->get() as $r) {
                $paidFully = ($r->price - $r->paid_amount) <= self::PAID_TOLERANCE;
                $data = [
                    'tracking_no'   => $r->tracking_no,
                    'goods_name_en' => $r->goods_name_en,
                    'shipping_mark' => $r->shipping_mark,
                    'pack_no'       => $r->pack_no ?? 0,
                    'weight_kg'     => $r->weight_kg,
                    'cbm'           => 0,
                    'warehouse'     => $r->warehouse,
                    'status'        => 'delivered',
                    'customer_id'   => $customerId[$r->customer_code] ?? null,
                    'price_per_kg'  => $r->price_per_kg,
                    'price'         => $r->price,
                    'paid_at'       => $paidFully ? ($r->delivered_at ?? now()) : null,
                    'is_copy'       => false,
                ];
                if ($hasReceiver)  { $data['receiver_name'] = $r->receiver_name; $data['receiver_phone'] = $r->receiver_phone; }
                if ($hasDelivered) { $data['delivered_at'] = $r->delivered_at; }

                $item = new CargoItem();
                $item->forceFill($data)->save();
                ItemsShipment::find($shipmentId[$r->shipment_no])->cargoItems()->attach($item->id);

                $r->update(['cargo_item_id' => $item->id, 'import_status' => 'imported']);
                $itemFor[$r->customer_code][$r->shipment_no][$r->delivered_at?->toDateString()][] = $r;
                $count['items']++;
            }

            // 4. payments (one CustomerPayment per customer + shipment + date, items allocated from legacy rows)
            foreach ($u->payments as $p) {
                $rows = collect($itemFor[$p->customer_code][$p->shipment_no][$p->payment_date?->toDateString()] ?? [])
                    ->filter(fn ($r) => $r->paid_amount > 0);
                if ($rows->isEmpty()) continue;

                $due = $u->rows()->where('customer_code', $p->customer_code)->where('shipment_no', $p->shipment_no)
                        ->sum('price') - $u->rows()->where('customer_code', $p->customer_code)
                        ->where('shipment_no', $p->shipment_no)->sum('paid_amount');

                $pay = new CustomerPayment();
                $pay->forceFill([
                    'customer_id'     => $customerId[$p->customer_code],
                    'itemshipment_id' => $shipmentId[$p->shipment_no],
                    'amount'          => $p->amount,
                    'payment_method'  => $p->payment_method,
                    'payment_date'    => $p->payment_date,
                    'reference_no'    => $p->reference_no,
                    'notes'           => $p->notes,
                    'remaining_due'   => max($due, 0),
                ])->save();

                foreach ($rows as $r) {
                    $pi = new CustomerPaymentItem();
                    $pi->forceFill(['customer_payment_id' => $pay->id, 'cargo_item_id' => $r->cargo_item_id, 'amount' => $r->paid_amount])->save();
                }
                $p->update(['customer_payment_id' => $pay->id]);
                $count['payments']++;
            }

            $u->update(['status' => 'imported', 'imported_at' => now()]);
        });

        return $count;
    }

    // =====================================================================
    // helpers
    // =====================================================================
    private function sheetToAssoc(Worksheet $ws): array
    {
        $data = $ws->toArray(null, true, false, false); // calculate formulas, raw values
        $head = array_map(fn ($h) => trim((string) $h), array_shift($data));
        $out = [];
        foreach ($data as $r) {
            if (!array_filter($r, fn ($v) => $v !== null && $v !== '')) continue;
            $r = array_slice(array_pad($r, count($head), null), 0, count($head));
            $out[] = array_combine($head, $r);
        }
        return $out;
    }

    private function readSourceTotals($book): ?array
    {
        $ws = $book->getSheetByName('Summary');
        if (!$ws) return null;
        $get = fn ($cell) => is_numeric($v = $ws->getCell($cell)->getCalculatedValue()) ? (float) $v : null;
        return ['lines' => $get('C5'), 'weight' => $get('C6'), 'billed' => $get('C7'), 'payments' => $get('C8')];
    }

    private function str($v): ?string
    {
        $v = trim((string) $v);
        return $v === '' ? null : $v;
    }

    private function int($v): ?int
    {
        return is_numeric($v) ? (int) $v : null;
    }

    private function phone($v): ?string
    {
        $p = preg_replace('/\D/', '', (string) $v);
        if ($p === '') return null;
        if (strlen($p) === 10 && $p[0] === '1') $p = '0' . $p;
        return $p;
    }

    private function date($v): ?string
    {
        if ($v === null || $v === '') return null;
        if (is_numeric($v)) return Carbon::instance(Date::excelToDateTimeObject($v))->toDateString();
        try { return Carbon::parse($v)->toDateString(); } catch (\Throwable $e) { return null; }
    }
}
