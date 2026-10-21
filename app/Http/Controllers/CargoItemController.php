<?php

namespace App\Http\Controllers;

use App\Imports\CargoItemsImport;
use App\Models\CargoItem;
use App\Models\ImportedExcelFile;
use App\Models\ItemsShipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class CargoItemController extends Controller
{
public function index()
{
    return view('admin.cargo_items.index', [
        'currentPageStatus' => 'all'
    ]);
}
public function pendingItems()
{
    return view('admin.cargo_items.index', ['currentPageStatus' => 'pending']);
}

public function packedItems()
{
    return view('admin.cargo_items.index', ['currentPageStatus' => 'packed']);
}

public function arrivedBdItems()
{
    return view('admin.cargo_items.index', ['currentPageStatus' => 'arrived_bd']);
}

public function bangladeshWarehouseItems()
{
    return view('admin.cargo_items.index', ['currentPageStatus' => 'bangladesh_warehouse']);
}

public function otherStatusPage($status)
{
    return view('admin.cargo_items.index', ['currentPageStatus' => $status]);
}

public function ajaxItems(Request $request)
{
    $status = $request->input('status', 'all');
    $search = $request->input('search', '');
    $perPage = (int) $request->input('per_page', 50);
    $page = (int) $request->input('page', 1);

    $statusGroups = [
        'pending'              => ['pending'],
        'packed'               => ['packed'],
        'shipping_ongoing'     => ['shipping_ongoing'],
        'arrived_bd'           => ['arrived_bd', 'waiting_for_customs', 'ready_to_deliver'],
        'waiting_for_customs'  => ['waiting_for_customs'],
        'ready_to_deliver'     => ['ready_to_deliver'],
        'delivered'            => ['delivered'],
        'bangladesh_warehouse' => ['ready_to_deliver', 'delivered'],
    ];

    $query = CargoItem::query()->with('latestReturn');

    if ($status !== 'all' && isset($statusGroups[$status])) {
        $query->whereIn('status', $statusGroups[$status]);
    } elseif ($status !== 'all') {
        $query->where('status', $status);
    }

    if ($search !== '') {
        $query->where(function ($q) use ($search) {
            $q->where('tracking_no', 'like', "%{$search}%")
              ->orWhere('goods_name_en', 'like', "%{$search}%")
              ->orWhere('printed_ctn_no', 'like', "%{$search}%")
              ->orWhere('written_ctn_no', 'like', "%{$search}%")
              ->orWhere('receipt_no', 'like', "%{$search}%");
        });
    }

    $items = $query->orderBy('id', 'desc')->paginate($perPage, ['*'], 'page', $page);

    $data = collect($items->items())->map(function ($item) {
        $arr = $item->toArray();
        $arr['is_returned'] = (bool) $item->latestReturn;
        $arr['return_note'] = $item->latestReturn->note ?? null;
        $arr['return_date'] = $item->latestReturn?->return_date?->format('Y-m-d');
        return $arr;
    });

    return response()->json([
        'data' => $data,
        'meta' => [
            'current_page' => $items->currentPage(),
            'last_page'    => $items->lastPage(),
            'per_page'     => $items->perPage(),
            'total'        => $items->total(),
            'from'         => $items->firstItem(),
            'to'           => $items->lastItem(),
        ],
    ]);
}
    public function create()
    {
        return view('admin.cargo_items.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'items'                   => ['required', 'array', 'min:1'],
            'items.*.date'            => ['nullable', 'date'],
            'items.*.goods_received'  => ['nullable', 'integer', 'min:0'],
            'items.*.printed_ctn_no'  => ['nullable', 'string', 'max:50'],
            'items.*.written_ctn_no'  => ['nullable', 'string', 'max:50'],
            'items.*.receipt_no'      => ['nullable', 'string', 'max:50'],
            'items.*.tracking_no'     => ['required', 'string', 'max:100'],
            'items.*.goods_name_en'   => ['required', 'string', 'max:255'],
            'items.*.goods_name_local'=> ['nullable', 'string', 'max:255'],
            'items.*.pack_no'         => ['nullable', 'integer', 'min:0'],
            'items.*.unit_pcs'        => ['nullable', 'integer', 'min:0'],
            'items.*.weight_kg'       => ['nullable', 'numeric', 'min:0'],
            'items.*.length_cm'       => ['nullable', 'numeric', 'min:0'],
            'items.*.width_cm'        => ['nullable', 'numeric', 'min:0'],
            'items.*.height_cm'       => ['nullable', 'numeric', 'min:0'],
            'items.*.cbm'             => ['nullable', 'numeric', 'min:0'],
            'items.*.shipping_mark'   => ['nullable', 'string', 'max:255'],
            'items.*.remark'          => ['nullable', 'string', 'max:500'],
            'items.*.warehouse'       => ['nullable', 'string', 'max:255'],
            'items.*.is_copy'         => ['nullable'],
        ]);

        // Check duplicates (manual entry)
        $duplicates = [];
       $seen = [];
foreach ($validated['items'] as $index => $itemData) {
    $key = $this->buildRowKey($itemData);
    if ($key === null) continue;
    $exists = $this->rowExistsInDatabase($itemData);
    $isDuplicate = $exists || isset($seen[$key]);
    if ($isDuplicate) {
        $duplicates[] = [
            'row' => $index + 1,
            'tracking_no' => $itemData['tracking_no'],
            'goods_name_en' => $itemData['goods_name_en'],
            'reason' => $exists ? 'Already in database' : 'Duplicate in form',
        ];
    }
    $seen[$key] = true;
}


        // If duplicate action was requested
        $duplicateAction = $request->input('duplicate_action', 'skip');

        DB::transaction(function () use ($validated, $duplicateAction) {
            $seen = [];
            foreach ($validated['items'] as $itemData) {
                $itemData['is_copy'] = !empty($itemData['is_copy']);

                $key = strtolower(trim(($itemData['tracking_no'] ?? '') . '|' . ($itemData['goods_name_en'] ?? '')));
                if (empty($itemData['tracking_no'])) continue;

                $exists = CargoItem::where('tracking_no', $itemData['tracking_no'])
                    ->where('goods_name_en', $itemData['goods_name_en'])
                    ->exists();

                $isDuplicate = $exists || isset($seen[$key]);
                $seen[$key] = true;

                if ($isDuplicate && $duplicateAction === 'skip') {
                    continue;
                }

                CargoItem::create($itemData);
            }
        });

        return redirect()
            ->route('cargo-items.index')
            ->with('success', 'Cargo items created successfully.');
    }

    public function edit(CargoItem $cargoItem)
    {
        return view('admin.cargo_items.edit', compact('cargoItem'));
    }

    public function update(Request $request, CargoItem $cargoItem)
    {
        $data = $request->validate([
            'date'             => ['nullable', 'date'],
            'goods_received'   => ['nullable', 'integer', 'min:0'],
            'printed_ctn_no'   => ['nullable', 'string', 'max:50'],
            'written_ctn_no'   => ['nullable', 'string', 'max:50'],
            'receipt_no'       => ['nullable', 'string', 'max:50'],
            'tracking_no'      => ['required', 'string', 'max:100'],
            'goods_name_en'    => ['required', 'string', 'max:255'],
            'goods_name_local' => ['nullable', 'string', 'max:255'],
            'pack_no'          => ['nullable', 'integer', 'min:0'],
            'unit_pcs'         => ['nullable', 'integer', 'min:0'],
            'weight_kg'        => ['nullable', 'numeric', 'min:0'],
            'length_cm'        => ['nullable', 'numeric', 'min:0'],
            'width_cm'         => ['nullable', 'numeric', 'min:0'],
            'height_cm'        => ['nullable', 'numeric', 'min:0'],
            'cbm'              => ['nullable', 'numeric', 'min:0'],
            'shipping_mark'    => ['nullable', 'string', 'max:255'],
            'remark'           => ['nullable', 'string', 'max:500'],
            'warehouse'        => ['nullable', 'string', 'max:255'],
            'is_copy'          => ['nullable'],
            'status'           => ['nullable', 'in:pending,packed,ready_to_ship,shipping_ongoing,arrived_bd,waiting_for_customs,ready_to_deliver,delivered'],
        ]);

        // a cancelled item must be restored via the Restore button, not the status dropdown
        if ($cargoItem->status === 'cancelled') {
            unset($data['status']);
        }

        $data['is_copy'] = $request->boolean('is_copy');

        $cargoItem->update($data);

        return redirect()
            ->route('cargo-items.index')
            ->with('success', 'Cargo item updated successfully.');
    }

    /** Cancel an item (removes it from any shipment, keeps the record in the Cancelled list). */
    public function cancel(Request $request, CargoItem $cargoItem)
    {
        \Illuminate\Support\Facades\Gate::authorize('edit-warehouse-item');

        $data = $request->validate(['cancel_reason' => ['nullable', 'string', 'max:255']]);

        if ($cargoItem->status === 'cancelled') {
            return back()->with('error', 'Item is already cancelled.');
        }
        if ($cargoItem->status === 'delivered') {
            return back()->with('error', 'Delivered items cannot be cancelled.');
        }

        DB::transaction(function () use ($cargoItem, $data) {
            $cargoItem->itemshipments()->detach();
            $cargoItem->update([
                'status_before_cancel' => $cargoItem->status,
                'status'               => 'cancelled',
                'cancelled_at'         => now(),
                'cancel_reason'        => $data['cancel_reason'] ?? null,
                'box_no'               => null,
            ]);
        });

        return redirect()->route('cargo-items.status', 'cancelled')
            ->with('success', 'Item #' . $cargoItem->id . ' cancelled.');
    }

    /** Bring a cancelled item back as pending. */
    public function restore(CargoItem $cargoItem)
    {
        \Illuminate\Support\Facades\Gate::authorize('edit-warehouse-item');

        if ($cargoItem->status !== 'cancelled') {
            return back()->with('error', 'Item is not cancelled.');
        }

        $cargoItem->update([
            'status'               => 'pending',
            'cancelled_at'         => null,
            'cancel_reason'        => null,
            'status_before_cancel' => null,
        ]);

        return redirect()->route('cargo-items.index')->with('success', 'Item #' . $cargoItem->id . ' restored to pending.');
    }

    public function destroy(CargoItem $cargoItem)
    {
        $cargoItem->delete();

        return redirect()
            ->route('cargo-items.index')
            ->with('success', 'Cargo item deleted successfully.');
    }

    // ============================================
    // EXCEL IMPORT
    // ============================================

    /**
     * Handle Excel upload - store file and redirect to preview
     */
    public function import(Request $request)
    {
        $request->validate([
            // Check the extension, not the sniffed MIME type: some valid .xlsx files
            // (e.g. saved by WPS / older Excel / exported tools) are detected as
            // application/octet-stream and would fail the `mimes` rule.
            'file'  => [
                'required',
                'file',
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (!in_array($ext, ['xlsx', 'xls'], true)) {
                        $fail('The file must be an Excel file (.xlsx or .xls).');
                    }
                },
            ],
            'title' => ['nullable', 'string', 'max:255'],
            'note'  => ['nullable', 'string', 'max:1000'],
        ]);

        $uploadedFile = $request->file('file');

        // Store file permanently
        $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9.\-_]/', '_', $uploadedFile->getClientOriginalName());
        $filePath = $uploadedFile->storeAs('imported_excel', $fileName, 'local');

        // Save file record
        $excelFile = ImportedExcelFile::create([
            'file_name'   => $uploadedFile->getClientOriginalName(),
            'file_path'   => $filePath,
            'title'       => $request->input('title') ?: $uploadedFile->getClientOriginalName(),
            'note'        => $request->input('note'),
            'uploaded_by' => auth()->id(),
        ]);

        // Parse file for preview
        try {
            $transformedItems = $this->parseExcel($uploadedFile);
        } catch (\RuntimeException $e) {
            Storage::disk('local')->delete($filePath);
            $excelFile->delete();

            return back()->withInput()->with('error', $e->getMessage());
        }

        // Detect duplicates
        $duplicates = $this->detectDuplicates($transformedItems);

        // Update file stats
        $excelFile->update([
            'total_rows'     => count($transformedItems),
            'duplicate_rows' => count($duplicates),
        ]);

        // Store preview data in session
        session([
            'import_preview_' . $excelFile->id => [
                'items'      => $transformedItems,
                'duplicates' => $duplicates,
                'file_id'    => $excelFile->id,
            ]
        ]);

        return redirect()->route('cargo-items.import.preview', $excelFile->id);
    }

    /**
     * Show import preview with duplicate warnings
     */
   public function importPreview($fileId)
{
    $sessionKey = 'import_preview_' . $fileId;
    $preview = session($sessionKey);

    if (!$preview) {
        return redirect()->route('cargo-items.create')
            ->with('error', 'Preview data expired. Please upload again.');
    }

    $excelFile = ImportedExcelFile::findOrFail($fileId);

    // Build list of unique rows and their duplicate status
    $duplicateKeys = [];
    foreach ($preview['duplicates'] as $dup) {
        $duplicateKeys[] = strtolower(
            ($dup['tracking_no'] ?? '') . '|' .
            ($dup['goods_name_en'] ?? '') . '|' .
            ($dup['printed_ctn_no'] ?? '') . '|' .
            ($dup['written_ctn_no'] ?? '') . '|' .
            ($dup['weight_kg'] ?? '')
        );
    }

    $uniqueItems = [];
    foreach ($preview['items'] as $i => $item) {
        $key = strtolower(
            ($item['tracking_no'] ?? '') . '|' .
            ($item['goods_name_en'] ?? '') . '|' .
            ($item['printed_ctn_no'] ?? '') . '|' .
            ($item['written_ctn_no'] ?? '') . '|' .
            ($item['weight_kg'] ?? '')
        );

        // Only include if NOT in duplicate list (based on row number)
        $isDuplicate = false;
        foreach ($preview['duplicates'] as $dup) {
            if ($dup['row'] === ($i + 1)) {
                $isDuplicate = true;
                break;
            }
        }
        if (!$isDuplicate) {
            $uniqueItems[] = array_merge($item, ['_row' => $i + 1]);
        }
    }

    return view('admin.cargo_items.import_preview', [
        'excelFile'    => $excelFile,
        'items'        => $preview['items'],
        'duplicates'   => $preview['duplicates'],
        'uniqueItems'  => $uniqueItems,
    ]);
}

    /**
     * Confirm import - save items to DB (with skip duplicates option)
     */
 public function importConfirm(Request $request, $fileId)
{
    $sessionKey = 'import_preview_' . $fileId;
    $preview = session($sessionKey);

    if (!$preview) {
        return redirect()->route('cargo-items.create')
            ->with('error', 'Preview data expired. Please upload again.');
    }

    $items = $preview['items'];
    $skipDuplicates = $request->input('skip_duplicates', '1') === '1';

    $importedCount = 0;
    $skippedCount = 0;

    DB::transaction(function () use ($items, $skipDuplicates, &$importedCount, &$skippedCount) {
        $seen = [];
        foreach ($items as $itemData) {
            $key = $this->buildRowKey($itemData);
            if ($key === null) continue;

            $exists = $this->rowExistsInDatabase($itemData);
            $isDuplicate = $exists || isset($seen[$key]);

            if ($isDuplicate && $skipDuplicates) {
                $seen[$key] = true;
                $skippedCount++;
                continue;
            }

            CargoItem::create($itemData);
            $seen[$key] = true;
            $importedCount++;
        }
    });

    $excelFile = ImportedExcelFile::findOrFail($fileId);
    $excelFile->update([
        'imported_rows'  => $importedCount,
        'duplicate_rows' => $skippedCount,
    ]);

    session()->forget($sessionKey);

    return redirect()
        ->route('cargo-items.index')
        ->with('success', "{$importedCount} cargo items imported successfully from '{$excelFile->title}'. Skipped {$skippedCount} duplicates.");
}
    /**
     * Cancel import preview
     */
    public function importCancel($fileId)
    {
        session()->forget('import_preview_' . $fileId);

        return redirect()->route('cargo-items.create')
            ->with('info', 'Import cancelled.');
    }

    /**
     * Detect duplicate rows (both in file and in DB)
     */
   /**
 * Detect duplicate rows (both in file and in DB)
 * 
 * Duplicate = same tracking_no AND same goods_name_en AND same printed_ctn_no
 *             AND same written_ctn_no AND same weight_kg
 * 
 * This handles the case where one tracking_no has multiple cartons/goods.
 */
private function detectDuplicates($items)
{
    $duplicates = [];
    $seen = [];

    foreach ($items as $index => $item) {
        $key = $this->buildRowKey($item);

        if ($key === null) {
            continue; // skip empty keys
        }

        // Check DB
        $existsInDb = $this->rowExistsInDatabase($item);

        $isDuplicateInFile = isset($seen[$key]);

        if ($existsInDb || $isDuplicateInFile) {
            $duplicates[] = [
                'row'           => $index + 1,
                'tracking_no'   => $item['tracking_no'] ?? '',
                'goods_name_en' => $item['goods_name_en'] ?? '',
                'printed_ctn_no'=> $item['printed_ctn_no'] ?? '',
                'written_ctn_no'=> $item['written_ctn_no'] ?? '',
                'weight_kg'     => $item['weight_kg'] ?? '',
                'reason'        => $existsInDb
                    ? 'Already exists in database'
                    : 'Duplicate within the file',
            ];
        }

        $seen[$key] = true;
    }

    return $duplicates;
}

private function buildRowKey($item)
{
    $tracking = trim((string)($item['tracking_no'] ?? ''));
    if ($tracking === '') {
        return null;
    }

    // Normalize: trim + lowercase + collapse whitespace
    $norm = function ($v) {
        if ($v === null) return '';
        $v = strtolower(trim((string)$v));
        return preg_replace('/\s+/', ' ', $v); // collapse multiple spaces
    };

    // Normalize numbers to fixed precision so 34 vs 34.0 vs "34.000" match
    $num = function ($v) {
        if ($v === null || $v === '') return '';
        return number_format((float)$v, 4, '.', ''); // up to 4 decimals
    };

    $parts = [
        $norm($tracking),
        $norm($item['goods_name_en']  ?? ''),
        $norm($item['printed_ctn_no'] ?? ''),
        $norm($item['written_ctn_no'] ?? ''),
        $num($item['pack_no']         ?? ''),
        $num($item['weight_kg']       ?? ''),
    ];

    return implode('|', $parts);
}
/**
 * Check if a row already exists in DB using the same multi-field key.
 */
private function rowExistsInDatabase($item)
{
    $tracking = trim((string)($item['tracking_no'] ?? ''));
    if ($tracking === '') {
        return false;
    }

    return CargoItem::whereRaw('TRIM(tracking_no) = ?', [$tracking])
        ->whereRaw('TRIM(COALESCE(goods_name_en, "")) = ?', [trim((string)($item['goods_name_en'] ?? ''))])
        ->whereRaw('TRIM(COALESCE(printed_ctn_no, "")) = ?', [trim((string)($item['printed_ctn_no'] ?? ''))])
        ->whereRaw('TRIM(COALESCE(written_ctn_no, "")) = ?', [trim((string)($item['written_ctn_no'] ?? ''))])
        ->where('pack_no', intval($item['pack_no'] ?? 0))
        ->where('weight_kg', floatval($item['weight_kg'] ?? 0))
        ->exists();
}
    // ============================================
    // IMPORTED FILES MANAGEMENT (view / delete only)
    // ============================================

    public function filesIndex()
    {
        $files = ImportedExcelFile::with('uploader')->latest()->paginate(20);
        return view('admin.cargo_items.files_index', compact('files'));
    }

    public function filesView(ImportedExcelFile $file)
    {
        if (!Storage::disk('local')->exists($file->file_path)) {
            return redirect()->route('cargo-items.files.index')
                ->with('error', 'File not found on storage.');
        }

        // Parse the file to show data
        $fullPath = Storage::disk('local')->path($file->file_path);

        try {
            $transformedItems = $this->parseExcel($fullPath);
        } catch (\RuntimeException $e) {
            return redirect()->route('cargo-items.files.index')->with('error', $e->getMessage());
        }

        return view('admin.cargo_items.files_view', compact('file', 'transformedItems'));
    }

    public function filesDownload(ImportedExcelFile $file)
    {
        if (!Storage::disk('local')->exists($file->file_path)) {
            return redirect()->route('cargo-items.files.index')
                ->with('error', 'File not found.');
        }

        return Storage::disk('local')->download($file->file_path, $file->file_name);
    }

    public function filesDestroy(ImportedExcelFile $file)
    {
        if (Storage::disk('local')->exists($file->file_path)) {
            Storage::disk('local')->delete($file->file_path);
        }

        $file->delete();

        return redirect()->route('cargo-items.files.index')
            ->with('success', 'File deleted successfully.');
    }

    // ============================================
    // HELPERS (existing)
    // ============================================

    /**
     * Header aliases (normalised) => internal field.
     * The packing list has a "totals" row above the real header row, so the
     * header row is detected by looking for the Tracking + Goods_Name columns.
     */
    private const EXCEL_COLUMNS = [
        'received_date'  => ['received_date', 'goods_received', 'date'],
        'printed_ctn_no' => ['printed_ctn_no', 'printed_ctn'],
        'written_ctn_no' => ['written_ctn_no', 'written_ctn'],
        'receipt_no'     => ['receipt_no', 'receipt'],
        'tracking'       => ['tracking', 'trackings', 'tracking_no'],
        'goods_name'     => ['goods_name', 'goodsname'],
        'goods_name_local' => ['品名', 'goods_name_local'],
        'pack_no'        => ['pack_no', 'pack'],
        'unit_pcs'       => ['unit_pcs', 'unit_pc', 'pcs'],
        'weight_kg'      => ['weight_kg', 'weight'],
        'length_cm'      => ['l_cm', 'l', 'length_cm', 'length'],
        'width_cm'       => ['w_cm', 'w', 'width_cm', 'width'],
        'height_cm'      => ['h_cm', 'h', 'height_cm', 'height'],
        'cbm'            => ['cbm'],
        'shipping_mark'  => ['shipping_mark', 'shippingmark'],
        'remark'         => ['remark', 'remark_for_liquidbattery_or_powder'],
        'copy'           => ['copy', 'copy_or_no'],
        'warehouse'      => ['warehouse', 'ware_house'],
    ];

    /**
     * Read the first sheet into a plain 0-indexed array, safely.
     *
     * WPS/Excel files often style whole columns (e.g. <col min="18" max="16377" style=..>).
     * PhpSpreadsheet then creates a cell for every one of those ~16,000 columns on every
     * row, which exhausts memory. So we read data only (no styles), skip empty cells,
     * only load columns A-Z / the first 20,000 rows, and only the first sheet.
     *
     * @param  \Illuminate\Http\UploadedFile|string  $source
     */
    private function readSheetRows($source): array
    {
        $path = is_string($source) ? $source : $source->getRealPath();

        $filter = new class implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter {
            public function readCell($columnAddress, $row, $worksheetName = ''): bool
            {
                return $row <= 20000
                    && \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($columnAddress) <= 26;
            }
        };

        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $reader->setReadEmptyCells(false);
        $reader->setReadFilter($filter);

        $sheetNames = $reader->listWorksheetNames($path);
        if (!empty($sheetNames)) {
            $reader->setLoadSheetsOnly([$sheetNames[0]]);
        }

        $spreadsheet = $reader->load($path);
        $rows = $spreadsheet->getSheet(0)->toArray(null, false, false, false);

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $rows;
    }

    /**
     * Read the first sheet and return items ready for CargoItem::create().
     *
     * @param  \Illuminate\Http\UploadedFile|string  $source
     * @throws \RuntimeException when the header row cannot be found
     */
    private function parseExcel($source): array
    {
        $rows = $this->readSheetRows($source);

        $normalize = function ($v) {
            $v = mb_strtolower(trim((string) $v));
            return preg_replace('/[\s\-]+/u', '_', $v);
        };

        // Find the header row (within the first 10 rows) and map columns
        $headerIndex = null;
        $map = [];
        foreach (array_slice($rows, 0, 10, true) as $i => $row) {
            $headers = array_map($normalize, array_values($row));
            $found = [];
            foreach (self::EXCEL_COLUMNS as $field => $aliases) {
                foreach ($headers as $col => $h) {
                    if ($h !== '' && in_array($h, $aliases, true)) {
                        $found[$field] = $col;
                        break;
                    }
                }
            }
            if (isset($found['tracking'], $found['goods_name'])) {
                $headerIndex = $i;
                $map = $found;
                break;
            }
        }

        if ($headerIndex === null) {
            throw new \RuntimeException(
                'Could not find the header row. The sheet must have "Tracking" and "Goods_Name" columns.'
            );
        }

        $items = [];
        foreach (array_slice($rows, $headerIndex + 1) as $row) {
            $row = array_values($row);
            $get = fn (string $field) => isset($map[$field]) ? ($row[$map[$field]] ?? null) : null;

            if ($this->cellText($get('tracking')) === null || $this->cellText($get('goods_name')) === null) {
                continue; // empty / incomplete row
            }

            $items[] = $this->transformRow($get);
        }

        return $items;
    }

    /** Cell -> trimmed string or null (also fixes big numbers like 7.7E+14). */
    private function cellText($value): ?string
    {
        if ($value === null) return null;
        if (is_float($value) && floor($value) == $value && abs($value) < 1e15) {
            $value = sprintf('%.0f', $value);
        }
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    /** "10.09" / 10.09 / "5.12 " (MM.DD) or an Excel serial -> Y-m-d or null */
    private function parseReceivedDate($value): ?string
    {
        if ($value === null || $value === '') return null;

        // Excel date serial
        if (is_numeric($value) && (float) $value > 100) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }

        // MM.DD typed as a number loses its trailing zero only for 2 decimals max
        $text = is_float($value) ? sprintf('%.2f', $value) : trim((string) $value);
        $parts = explode('.', $text);
        if (count($parts) !== 2 || !ctype_digit($parts[0]) || !ctype_digit($parts[1])) {
            return null;
        }

        $month = (int) $parts[0];
        $day   = (int) $parts[1];
        $year  = (int) date('Y');

        if (!checkdate($month, $day, $year)) return null;

        // The sheet has no year: a date more than a month ahead must be last year's
        if (strtotime(sprintf('%04d-%02d-%02d', $year, $month, $day)) > strtotime('+31 days')) {
            $year--;
            if (!checkdate($month, $day, $year)) return null;
        }

        return sprintf('%04d-%02d-%02d', $year, $month, $day);
    }

    /** "2-1" -> 2, "10-6" -> 10, 3 -> 3 */
    private function leadingInt($value): int
    {
        return preg_match('/^\s*(\d+)/', (string) $value, $m) ? (int) $m[1] : 0;
    }

    private function transformRow(callable $get): array
    {
        $date = $this->parseReceivedDate($get('received_date'));

        $length = (float) $get('length_cm');
        $width  = (float) $get('width_cm');
        $height = (float) $get('height_cm');
        $cbm    = $this->calculateCBM($get('cbm'), $length, $width, $height);

        $copyText = strtolower((string) $this->cellText($get('copy')));
        $isCopy   = in_array($copyText, ['yes', 'copy', 'true', '1', 'y'], true);

        $remark = $this->cellText($get('remark'));
        // Anything else typed in the Copy column (e.g. "battery") is really a remark
        if ($copyText !== '' && !$isCopy && !in_array($copyText, ['no', 'n', 'false', '0'], true)) {
            $remark = trim(($remark ? $remark . ' / ' : '') . $this->cellText($get('copy')));
        }

        return [
            'date'             => $date,
            'goods_received'   => $date ? 1 : 0,
            'receipt_no'       => $this->cellText($get('receipt_no')),
            'printed_ctn_no'   => $this->cellText($get('printed_ctn_no')),
            'written_ctn_no'   => $this->cellText($get('written_ctn_no')),
            'tracking_no'      => $this->cellText($get('tracking')),
            'goods_name_en'    => $this->cellText($get('goods_name')),
            'goods_name_local' => $this->cellText($get('goods_name_local')),
            'pack_no'          => $this->leadingInt($get('pack_no')),
            'unit_pcs'         => $this->leadingInt($get('unit_pcs')),
            'weight_kg'        => (float) $get('weight_kg'),
            'length_cm'        => $length,
            'width_cm'         => $width,
            'height_cm'        => $height,
            'cbm'              => $cbm,
            'shipping_mark'    => $this->cellText($get('shipping_mark')),
            'remark'           => $remark,
            'warehouse'        => $this->cellText($get('warehouse')),
            'is_copy'          => $isCopy,
        ];
    }

    private function calculateCBM($cbmCell, float $length, float $width, float $height): float
    {
        if (is_numeric($cbmCell) && (float) $cbmCell > 0) {
            return (float) $cbmCell;
        }

        if ($length > 0 && $width > 0 && $height > 0) {
            return round(($length * $width * $height) / 1000000, 6);
        }

        return 0;
    }

    public function trackingSearchForm()
    {
        if (auth()->check()) {
            return view('admin.cargo_items.tracking_search');
        } else {
            return view('admin.cargo_items.tracking_search_public');
        }
    }

    public function trackingView(Request $request)
    {
        $request->validate([
            'tracking_no' => 'required|string',
        ]);

$item = CargoItem::where('tracking_no', $request->tracking_no)
    ->orWhere('receipt_no', $request->tracking_no)
    ->first();
        if (!$item) {
            return redirect()->route('cargo-items.tracking-search')
                ->with('error', 'No item found with Tracking No: ' . $request->tracking_no);
        }

        if (auth()->check()) {
            return view('admin.cargo_items.tracking_view', compact('item'));
        } else {
            return view('admin.cargo_items.tracking_view_public', compact('item'));
        }
    }
}