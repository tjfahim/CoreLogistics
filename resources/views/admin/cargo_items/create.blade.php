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

        {{-- Excel import (optional) --}}
        <div class="card mb-3">
            <div class="card-header">
                <strong>Import from Excel (.xlsx)</strong>
            </div>
            <div class="card-body">
                <form action="{{ route('cargo-items.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group mb-2">
                        <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" accept=".xlsx,.xls">
                        @error('file') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <button class="btn btn-info btn-sm">
                        <i class="fa fa-upload"></i> Import Excel
                    </button>
                </form>
            </div>
        </div>

        {{-- Manual bulk entry --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <strong>Add New Items (Manual)</strong>
                <a href="{{ route('cargo-items.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
            </div>

            <div class="card-body">
                {{-- Show global validation error --}}
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
                                {{-- Initial row (index 0) --}}
                                <tr>
                                    <td>
                                        <input type="date" 
                                            name="items[0][date]" 
                                            value="{{ now()->format('Y-m-d') }}" 
                                            class="form-control form-control-sm">
                                    </td>                                    
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
                                    <td class="text-center">
                                        <input type="checkbox" name="items[0][is_copy]" value="1">
                                    </td>
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
            </div> <!-- card-body -->
        </div>
    </div>
</div>

{{-- Simple JS to handle dynamic rows --}}
<script>
    (function () {
        let rowIndex = 1; // next row index (0 already used)

        document.getElementById('addRow').addEventListener('click', function () {
            const tbody = document.getElementById('items-body');

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><input type="date" name="items[${rowIndex}][date]" value="{{ now()->format('Y-m-d') }}"  class="form-control form-control-sm"></td>
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
                <td class="text-center">
                    <input type="checkbox" name="items[${rowIndex}][is_copy]" value="">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm remove-row">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            `;

            tbody.appendChild(tr);
            rowIndex++;
        });

        document.getElementById('items-body').addEventListener('click', function (e) {
            if (e.target.closest('.remove-row')) {
                const row = e.target.closest('tr');
                if (document.querySelectorAll('#items-body tr').length > 1) {
                    row.remove();
                }
            }
        });
    })();
</script>
@endsection