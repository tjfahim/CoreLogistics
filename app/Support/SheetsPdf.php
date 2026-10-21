<?php

namespace App\Support;

use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Renders the very same "sheet" arrays that feed SheetsExport (Excel) as a PDF, so every place
 * that has an Excel download can offer a PDF with one line.
 */
class SheetsPdf
{
    public static function download(array $sheets, string $filename)
    {
        $wide = collect($sheets)->contains(fn ($s) => count($s['headings'] ?? []) > 7);

        return Pdf::loadView('admin.pdf.sheets', ['sheets' => $sheets, 'generated' => now()->format('d M Y H:i')])
            ->setPaper('a4', $wide ? 'landscape' : 'portrait')
            ->download($filename);
    }
}
