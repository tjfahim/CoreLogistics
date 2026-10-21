<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryRecord extends Model
{
    protected $fillable = ['year', 'month', 'employee_id', 'employee_name', 'office_id', 'office_name', 'amount'];

    public function employee() { return $this->belongsTo(Employee::class); }

    /**
     * Freeze this month's salary sheet from the current employees (only for the
     * current month, only once). Later changes/deletions of employees therefore
     * affect NEXT month, never an already-recorded month.
     */
    public static function ensureCurrentMonth(): void
    {
        $y = (int) now()->format('Y');
        $m = (int) now()->format('n');

        if (self::where('year', $y)->where('month', $m)->exists()) {
            return;
        }

        foreach (Employee::with('office:id,name')->get() as $e) {
            self::create([
                'year' => $y, 'month' => $m,
                'employee_id' => $e->id, 'employee_name' => $e->name,
                'office_id' => $e->office_id, 'office_name' => optional($e->office)->name,
                'amount' => $e->salary,
            ]);
        }
    }

    /** @return array{total: float, count: int, is_estimate: bool} */
    public static function monthTotal(int $year, int $month): array
    {
        if ($year === (int) now()->format('Y') && $month === (int) now()->format('n')) {
            self::ensureCurrentMonth();
        }

        $q = self::where('year', $year)->where('month', $month);
        if ($q->exists()) {
            return ['total' => (float) $q->sum('amount'), 'count' => $q->count(), 'is_estimate' => false];
        }

        // month has no recorded sheet (before this feature existed): fall back to today's employees
        return ['total' => (float) Employee::sum('salary'), 'count' => Employee::count(), 'is_estimate' => true];
    }
}
