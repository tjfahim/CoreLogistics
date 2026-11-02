<?php

namespace App\Services;

use App\Models\CustomerPayment;
use App\Models\FundMovement;
use App\Models\ItemsShipmentPayment;
use App\Models\OfficeBill;
use App\Models\SalaryRecord;
use App\Models\WarehouseBill;
use Illuminate\Support\Collection;

/**
 * Cash book: how much money we hold in CASH and in BANK (bank transfer, cheque, mobile banking ...).
 * It is cumulative, so what is left today is still there tomorrow.
 *
 *   IN  : customer receipts
 *   OUT : payments to agents / shipment bills, office bills, warehouse bills, salaries
 *         (office / warehouse bills and salaries have no method: paid from cash)
 *   MANUAL (fund_movements): opening balance, cash<->bank transfer, deposit, withdraw
 */
class CashBookService
{
    public static function accountOf(?string $method): string
    {
        return strtolower((string) $method) === 'cash' ? 'cash' : 'bank';
    }

    /** Every money movement, oldest first: date, ts, account, in, out, desc, kind */
    public function transactions(): Collection
    {
        $t = collect();

        foreach (CustomerPayment::with('customer:id,name')->get() as $p) {
            $t->push($this->row($p->payment_date, $p->created_at, self::accountOf($p->payment_method), (float) $p->amount, 0,
                'Received from ' . optional($p->customer)->name . ($p->reference_no ? ' (' . $p->reference_no . ')' : ''), 'receipt'));
        }

        foreach (ItemsShipmentPayment::with('itemsShipmentCost.agent:id,name', 'itemsShipmentCost.costType:id,name')->get() as $p) {
            $c = $p->itemsShipmentCost;
            $t->push($this->row($p->payment_date, $p->created_at, self::accountOf($p->payment_method), 0, (float) $p->amount,
                'Paid: ' . (optional(optional($c)->costType)->name ?? 'shipment cost') . (optional(optional($c)->agent)->name ? ' - ' . $c->agent->name : ''), 'payment'));
        }

        foreach (OfficeBill::get() as $b) {
            $t->push($this->row($b->bill_date ?? $b->created_at, $b->created_at, 'cash', 0, (float) $b->amount,
                'Office bill: ' . ($b->bill_type ?: 'bill') . ($b->note ? ' - ' . $b->note : ''), 'office'));
        }
        foreach (WarehouseBill::get() as $b) {
            $t->push($this->row($b->bill_date, $b->created_at, 'cash', 0, (float) $b->total_amount, 'Warehouse bill' . ($b->note ? ' - ' . $b->note : ''), 'office'));
        }
        foreach (SalaryRecord::get() as $s) {
            $t->push($this->row($s->created_at, $s->created_at, 'cash', 0, (float) $s->amount, 'Salary: ' . $s->employee_name . ' (' . $s->month . '/' . $s->year . ')', 'salary'));
        }

        foreach (FundMovement::all() as $m) {
            $label = FundMovement::TYPES[$m->type] ?? $m->type;
            $note = $label . ($m->note ? ' - ' . $m->note : '');
            if ($m->from_account) {
                $t->push($this->row($m->movement_date, $m->created_at, $m->from_account, 0, (float) $m->amount, $note, 'manual', $m->id));
            }
            if ($m->to_account) {
                $t->push($this->row($m->movement_date, $m->created_at, $m->to_account, (float) $m->amount, 0, $note, 'manual', $m->id));
            }
        }

        // Cash book starts on the date of the first "Opening balance" entry: older receipts / payments
        // (before you started using the cash book) must not push cash below zero.
        $start = $this->startDate();
        $t = $t->filter(fn ($r) => $r['kind'] === 'manual' || ($start && $r['date'] >= $start));

        return $t->sortBy([['date', 'asc'], ['ts', 'asc']])->values();
    }

    /** Date of the first opening-balance entry (null = cash book not set up yet). */
    public function startDate(): ?string
    {
        $d = FundMovement::where('type', 'opening')->min('movement_date');
        return $d ? \Carbon\Carbon::parse($d)->toDateString() : null;
    }

    private function row($date, $created, string $account, float $in, float $out, string $desc, string $kind, ?int $movementId = null): array
    {
        return [
            'date' => \Carbon\Carbon::parse($date)->toDateString(),
            'ts' => $created ? \Carbon\Carbon::parse($created)->format('Y-m-d H:i:s') : '',
            'account' => $account, 'in' => round($in, 2), 'out' => round($out, 2),
            'desc' => $desc, 'kind' => $kind, 'movement_id' => $movementId,
        ];
    }

    /** Balance of each account up to (and including) $upTo (null = everything). */
    public function balances(?string $upTo = null, ?Collection $tx = null): array
    {
        $tx = $tx ?? $this->transactions();
        $b = ['cash' => 0.0, 'bank' => 0.0];
        foreach ($tx as $r) {
            if ($upTo && $r['date'] > $upTo) continue;
            $b[$r['account']] += $r['in'] - $r['out'];
        }
        $b = array_map(fn ($v) => round($v, 2), $b);
        $b['total'] = round($b['cash'] + $b['bank'], 2);
        return $b;
    }

    /** What happened on one day (in / out per account). */
    public function day(string $date, ?Collection $tx = null): array
    {
        $tx = ($tx ?? $this->transactions())->where('date', $date);
        $o = [];
        foreach (['cash', 'bank'] as $a) {
            $o[$a] = ['in' => round($tx->where('account', $a)->sum('in'), 2), 'out' => round($tx->where('account', $a)->sum('out'), 2)];
        }
        return $o;
    }

    /** Statement for a period with running balance per account. */
    public function statement(?string $from, ?string $to): array
    {
        $all = $this->transactions();
        $openingDay = $from ? \Carbon\Carbon::parse($from)->subDay()->toDateString() : null;
        $opening = $from ? $this->balances($openingDay, $all) : ['cash' => 0.0, 'bank' => 0.0, 'total' => 0.0];

        $run = ['cash' => $opening['cash'], 'bank' => $opening['bank']];
        $rows = collect();
        foreach ($all as $r) {
            if ($from && $r['date'] < $from) continue;
            if ($to && $r['date'] > $to) continue;
            $run[$r['account']] = round($run[$r['account']] + $r['in'] - $r['out'], 2);
            $r['balance'] = $run[$r['account']];
            $rows->push($r);
        }

        return [
            'opening' => $opening, 'rows' => $rows,
            'in' => ['cash' => round($rows->where('account', 'cash')->sum('in'), 2), 'bank' => round($rows->where('account', 'bank')->sum('in'), 2)],
            'out' => ['cash' => round($rows->where('account', 'cash')->sum('out'), 2), 'bank' => round($rows->where('account', 'bank')->sum('out'), 2)],
            'closing' => ['cash' => $run['cash'], 'bank' => $run['bank'], 'total' => round($run['cash'] + $run['bank'], 2)],
        ];
    }
}
