<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * One Excel file, many sheets. Every report / list builds plain arrays and hands them here.
 *
 * $sheets = [[
 *   'title'    => 'Customers',            // sheet tab name (max 31 chars)
 *   'heading'  => 'Customer Ledger',      // big title in row 1 (optional, defaults to title)
 *   'subtitle' => 'Period: 01 Jan - 31 Jan', // row 2 (optional)
 *   'headings' => ['Name', 'Billed', ...],
 *   'rows'     => [['Rahim', 1200.5, ...], ...],
 *   'totals'   => ['TOTAL', 1200.5, ...],   // optional last row
 *   'money'    => [1, 2],                  // zero-based column indexes shown as 1,234.56
 *   'int'      => [3],                     // zero-based column indexes shown as whole numbers
 * ]]
 */
class SheetsExport implements WithMultipleSheets
{
    public function __construct(private array $sheets)
    {
    }

    public function sheets(): array
    {
        $out  = [];
        $used = [];

        foreach ($this->sheets as $s) {
            $name = ReportSheet::cleanTitle($s['title'] ?? 'Sheet');
            $base = $name;
            $n    = 2;
            while (in_array(mb_strtolower($name), $used, true)) {
                $name = mb_substr($base, 0, 28) . ' ' . $n++;
            }
            $used[] = mb_strtolower($name);

            $out[] = new ReportSheet(
                $name,
                $s['heading'] ?? ($s['title'] ?? $name),
                $s['subtitle'] ?? null,
                $s['headings'] ?? [],
                $s['rows'] ?? [],
                $s['totals'] ?? null,
                $s['money'] ?? [],
                $s['int'] ?? []
            );
        }

        return $out;
    }
}
