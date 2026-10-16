@extends('admin.layouts.master')

@section('main_content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Add Items (Bulk)</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="{{ route('cargo-items.index') }}">Items</a></li>
                    <li class="active">Add Items</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="col-lg-12">

        @cannot('add-warehouse-item')
            <div class="alert alert-danger">
                You do not have permission to add warehouse items.
            </div>
        @endcannot

        @can('add-warehouse-item')

            {{-- ===================== Excel Import ===================== --}}
            @can('import-warehouse-file')
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Import from Excel (.xlsx)</strong>
                    @can('view-warehouse')
                        <a href="{{ route('cargo-items.files.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fa fa-folder"></i> Imported Files
                        </a>
                    @endcan
                </div>
                <div id="duplicateCheckResult" class="alert d-none mt-3"></div>

                <div class="card-body">
                    <form action="{{ route('cargo-items.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label class="font-weight-bold">Title (optional)</label>
                                    <input type="text" name="title" value="{{ old('title') }}"
                                           class="form-control form-control-sm"
                                           placeholder="e.g. Shipment Jan 2025">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label class="font-weight-bold">Note (optional)</label>
                                    <input type="text" name="note" value="{{ old('note') }}"
                                           class="form-control form-control-sm"
                                           placeholder="e.g. From supplier ABC">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label class="font-weight-bold">Excel File</label>
                                    <input type="file" name="file"
                                           class="form-control form-control-sm @error('file') is-invalid @enderror"
                                           accept=".xlsx,.xls">
                                    @error('file') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>
                        </div>

                        <button class="btn btn-info btn-sm mt-2">
                            <i class="fa fa-upload"></i> Upload & Preview
                        </button>
                    </form>
                </div>
            </div>
            @endcan

            {{-- ===================== Manual Bulk Entry ===================== --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <strong>Add New Items (Manual)</strong>
                    <a href="{{ route('cargo-items.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fa fa-arrow-left"></i> Back
                    </a>
                </div>

                <div class="card-body">
                    @if ($errors->has('items') || $errors->has('items.0.tracking_no'))
                        <div class="alert alert-danger">
                            Please fix errors in the rows below and try again.
                        </div>
                    @endif

                    <form action="{{ route('cargo-items.store') }}" method="POST">
                        @csrf

                        <div class="table-responsive">
                            <table class="table table-bordered table-sm" id="itemsTable">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Date</th>
                                        <th>Receipt No.</th>
                                        <th>Printed CTN</th>
                                        <th>Written CTN</th>
                                        <th>Tracking No.</th>
                                        <th>GoodsName (EN)</th>
                                        <th>Goods Name (Local)</th>
                                        <th>Pack No.</th>
                                        <th>Unit pcs</th>
                                        <th>Weight (kg)</th>
                                        <th>L</th>
                                        <th>W</th>
                                        <th>H</th>
                                        <th>CBM</th>
                                        <th>Shipping Mark</th>
                                        <th>Ware House</th>
                                        <th>Remark</th>
                                        <th>Copy</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="items-body">
                                    <tr>
                                        <td><input type="date" name="items[0][date]" value="{{ now()->format('Y-m-d') }}" class="form-control form-control-sm"></td>
                                        <td><input type="text" name="items[0][receipt_no]" class="form-control form-control-sm" style="min-width:110px"></td>
                                        <td><input type="text" name="items[0][printed_ctn_no]" class="form-control form-control-sm"></td>
                                        <td><input type="text" name="items[0][written_ctn_no]" class="form-control form-control-sm"></td>
                                        <td><input type="text" name="items[0][tracking_no]" class="form-control form-control-sm" required></td>
                                        <td><input type="text" name="items[0][goods_name_en]" class="form-control form-control-sm" required></td>
                                        <td><input type="text" name="items[0][goods_name_local]" class="form-control form-control-sm"></td>
                                        <td><input type="number" name="items[0][pack_no]" class="form-control form-control-sm" value=""></td>
                                        <td><input type="number" name="items[0][unit_pcs]" class="form-control form-control-sm" value=""></td>
                                        <td><input type="number" step="0.001" name="items[0][weight_kg]" class="form-control form-control-sm"></td>
                                        <td><input type="number" step="0.01" name="items[0][length_cm]" class="form-control form-control-sm"></td>
                                        <td><input type="number" step="0.01" name="items[0][width_cm]" class="form-control form-control-sm"></td>
                                        <td><input type="number" step="0.01" name="items[0][height_cm]" class="form-control form-control-sm"></td>
                                        <td><input type="number" step="0.000001" name="items[0][cbm]" class="form-control form-control-sm"></td>
                                        <td><input type="text" name="items[0][shipping_mark]" class="form-control form-control-sm"></td>
                                        <td><input type="text" name="items[0][warehouse]" class="form-control form-control-sm"></td>
                                        <td><input type="text" name="items[0][remark]" class="form-control form-control-sm"></td>
                                        <td class="text-center"><input type="checkbox" name="items[0][is_copy]" value="1"></td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger btn-sm remove-row">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <button type="button" id="addRow" class="btn btn-success btn-sm mb-3">
                            <i class="fa fa-plus"></i> Add Row
                        </button>
                        <div class="d-flex justify-content-between mt-3">
                            <button class="btn btn-primary btn-sm">
                                <i class="fa fa-save"></i> Save All Items
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        @endcan
    </div>
</div>

{{-- Keep all your existing JS --}}
<script>
    (function () {
        let rowIndex = 1;
        document.getElementById('addRow').addEventListener('click', function () {
            const tbody = document.getElementById('items-body');
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><input type="date" name="items[${rowIndex}][date]" value="{{ now()->format('Y-m-d') }}"  class="form-control form-control-sm"></td>
                <td><input type="text" name="items[${rowIndex}][receipt_no]" class="form-control form-control-sm" style="min-width:110px"></td>
                <td><input type="text" name="items[${rowIndex}][printed_ctn_no]" class="form-control form-control-sm"></td>
                <td><input type="text" name="items[${rowIndex}][written_ctn_no]" class="form-control form-control-sm"></td>
                <td><input type="text" name="items[${rowIndex}][tracking_no]" class="form-control form-control-sm" required></td>
                <td><input type="text" name="items[${rowIndex}][goods_name_en]" class="form-control form-control-sm" required></td>
                <td><input type="text" name="items[${rowIndex}][goods_name_local]" class="form-control form-control-sm"></td>
                <td><input type="number" name="items[${rowIndex}][pack_no]" class="form-control form-control-sm" value=""></td>
                <td><input type="number" name="items[${rowIndex}][unit_pcs]" class="form-control form-control-sm" value=""></td>
                <td><input type="number" step="0.001" name="items[${rowIndex}][weight_kg]" class="form-control form-control-sm"></td>
                <td><input type="number" step="0.01" name="items[${rowIndex}][length_cm]" class="form-control form-control-sm"></td>
                <td><input type="number" step="0.01" name="items[${rowIndex}][width_cm]" class="form-control form-control-sm"></td>
                <td><input type="number" step="0.01" name="items[${rowIndex}][height_cm]" class="form-control form-control-sm"></td>
                <td><input type="number" step="0.000001" name="items[${rowIndex}][cbm]" class="form-control form-control-sm"></td>
                <td><input type="text" name="items[${rowIndex}][shipping_mark]" class="form-control form-control-sm"></td>
                <td><input type="text" name="items[${rowIndex}][warehouse]" class="form-control form-control-sm"></td>
                <td><input type="text" name="items[${rowIndex}][remark]" class="form-control form-control-sm"></td>
                <td class="text-center"><input type="checkbox" name="items[${rowIndex}][is_copy]" value=""></td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm remove-row"><i class="fa fa-trash"></i></button>
                </td>`;
            tbody.appendChild(tr);
            rowIndex++;
        });

        document.getElementById('items-body').addEventListener('click', function (e) {
            if (e.target.closest('.remove-row')) {
                const row = e.target.closest('tr');
                if (document.querySelectorAll('#items-body tr').length > 1) row.remove();
            }
        });
    })();

    (function () {
        // Duplicate checker (kept as-is)
        function checkDuplicates() {
            const rows = document.querySelectorAll('#items-body tr');
            const seen = {};
            const duplicates = [];

            rows.forEach((row, i) => {
                const tracking = row.querySelector('input[name*="[tracking_no]"]')?.value?.trim() || '';
                const goods = row.querySelector('input[name*="[goods_name_en]"]')?.value?.trim() || '';
                if (!tracking) return;
                const key = (tracking + '|' + goods).toLowerCase();
                if (seen[key]) {
                    duplicates.push({ row: i + 1, tracking_no: tracking, goods_name: goods, reason: 'Duplicate within form' });
                }
                seen[key] = true;
            });

            const box = document.getElementById('duplicateCheckResult');
            if (!box) return;

            if (duplicates.length > 0) {
                box.className = 'alert alert-warning mt-3';
                let html = `<strong><i class="fa fa-exclamation-triangle"></i> ${duplicates.length} duplicate row(s) in form:</strong><ul class="mb-0 mt-2">`;
                duplicates.forEach(d => { html += `<li>Row ${d.row}: <code>${d.tracking_no}</code> — ${d.goods_name}</li>`; });
                html += '</ul>';
                box.innerHTML = html;
                box.classList.remove('d-none');
            } else {
                box.className = 'alert alert-success mt-3';
                box.innerHTML = '<i class="fa fa-check-circle"></i> No duplicates detected in form.';
                box.classList.remove('d-none');
            }
        }

        const body = document.getElementById('items-body');
        if (body) {
            body.addEventListener('input', function (e) {
                if (e.target.name && (e.target.name.includes('[tracking_no]') || e.target.name.includes('[goods_name_en]'))) {
                    clearTimeout(window.__dupTimer);
                    window.__dupTimer = setTimeout(checkDuplicates, 300);
                }
            });
        }
    })();
</script>
@endsection