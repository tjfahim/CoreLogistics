<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;

class CargoItemsImport implements ToArray, WithHeadingRow, WithStartRow
{
    public function headingRow(): int
    {
        return 2;
    }

    public function startRow(): int
    {
        return 3;
    }

    public function array(array $array)
    {
        $data = [];

        foreach ($array as $row) {
            if (empty($row['trackings']) && empty($row['goods_name'])) {
                continue;
            }

            $date = $this->parseMmDdDate($row['date_mm_dd'] ?? null);
            $copyVal = strtolower(trim($row['copy_or_no'] ?? ''));
            $isCopy = in_array($copyVal, ['yes', 'y', '1', 'copy'], true);

            $data[] = [
                'date'             => $date,
                'goods_received'   => $row['goods_received'] ?? 0,
                'printed_ctn_no'   => $row['printed_ctn_no'] ?? null,
                'written_ctn_no'   => $row['written_ctn_no'] ?? null,
                'tracking_no'      => $row['trackings'] ?? null,
                'goods_name_en'    => $row['goods_name'] ?? null,
                'goods_name_local' => $row['pin_ming'] ?? null,
                'pack_no'          => $row['pack_no'] ?? 0,
                'unit_pcs'         => $row['unit_pcs'] ?? 0,
                'weight_kg'        => $row['weight_kg'] ?? 0,
                'length_cm'        => $row['l_cm'] ?? null,
                'width_cm'         => $row['w_cm'] ?? null,
                'height_cm'        => $row['h_cm'] ?? null,
                'cbm'              => $row['cbm'] ?? null,
                'shipping_mark'    => $row['shipping_mark'] ?? null,
                'remark'           => $row['remark_for_liquid_battery_or_powder'] ?? null,
                'warehouse'        => $row['ware_house'] ?? null,
                'is_copy'          => $isCopy,
            ];
        }

        return $data;
    }

    protected function parseMmDdDate($value) 
    {
        if (!$value) return null;
        
        if (is_numeric($value)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
        }
        
        if (preg_match('/(\d+)\.(\d+)/', $value, $m)) {
            return \Carbon\Carbon::create(now()->year, $m[1], $m[2]);
        }

        return null;
    }
}