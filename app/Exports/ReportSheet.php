<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** A single, professionally formatted sheet: title, subtitle, header row, data, totals. */
class ReportSheet implements FromArray, WithTitle, ShouldAutoSize, WithStyles, WithEvents
{
    private const HEADER_ROW = 4;

    public function __construct(
        private string $title,
        private string $heading,
        private ?string $subtitle,
        private array $headings,
        private array $rows,
        private ?array $totals,
        private array $money,
        private array $int
    ) {
    }

    public static function cleanTitle(string $t): string
    {
        $t = trim(str_replace(['\\', '/', '*', '?', ':', '[', ']'], ' ', $t));
        return mb_substr($t === '' ? 'Sheet' : $t, 0, 31);
    }

    /** Stop user typed text from being run as an Excel formula. */
    public static function safe($v)
    {
        if (is_string($v) && $v !== '' && in_array($v[0], ['=', '@'], true)) {
            return "'" . $v;
        }
        if (is_string($v) && strlen($v) > 1 && in_array($v[0], ['+', '-'], true) && !is_numeric($v)) {
            return "'" . $v;
        }
        return $v;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function array(): array
    {
        $cols = max(count($this->headings), 1);
        $pad  = fn (array $r) => array_pad(array_map([self::class, 'safe'], $r), $cols, null);

        $data = [
            $pad([$this->heading]),
            $pad([$this->subtitle ?? ('Generated ' . now()->format('d M Y H:i'))]),
            $pad([]),
            $pad($this->headings),
        ];

        foreach ($this->rows as $r) {
            $data[] = $pad(array_values($r));
        }

        if ($this->totals) {
            $data[] = $pad(array_values($this->totals));
        }

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        $cols = max(count($this->headings), 1);
        $last = Coordinate::stringFromColumnIndex($cols);
        $hr   = self::HEADER_ROW;
        $end  = $hr + count($this->rows) + ($this->totals ? 1 : 0);

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getFont()->setItalic(true)->getColor()->setRGB('5B6B88');

        $sheet->getStyle("A{$hr}:{$last}{$hr}")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F3A8A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);

        if ($end > $hr) {
            $sheet->getStyle("A" . ($hr + 1) . ":{$last}{$end}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D9DFEA']]],
            ]);
        }

        foreach ($this->money as $i) {
            $c = Coordinate::stringFromColumnIndex($i + 1);
            $sheet->getStyle("{$c}" . ($hr + 1) . ":{$c}{$end}")->getNumberFormat()->setFormatCode('#,##0.00');
        }
        foreach ($this->int as $i) {
            $c = Coordinate::stringFromColumnIndex($i + 1);
            $sheet->getStyle("{$c}" . ($hr + 1) . ":{$c}{$end}")->getNumberFormat()->setFormatCode('#,##0');
        }

        if ($this->totals) {
            $sheet->getStyle("A{$end}:{$last}{$end}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8EEFD']],
                'borders' => ['top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '1F3A8A']]],
            ]);
        }

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $cols = max(count($this->headings), 1);
                $last = Coordinate::stringFromColumnIndex($cols);
                $hr   = self::HEADER_ROW;
                $ws   = $event->sheet->getDelegate();

                $ws->freezePane('A' . ($hr + 1));
                if (count($this->rows) > 0) {
                    $ws->setAutoFilter("A{$hr}:{$last}" . ($hr + count($this->rows)));
                }
                $ws->getPageSetup()->setOrientation('landscape');
                $ws->getPageSetup()->setFitToPage(true);
                $ws->getPageSetup()->setFitToWidth(1);
                $ws->getPageSetup()->setFitToHeight(0);
                $ws->getSheetView()->setZoomScale(95);
            },
        ];
    }
}
