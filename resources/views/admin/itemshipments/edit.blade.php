@extends('admin.layouts.master')

@section('main_content')

<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Edit Shipment</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="{{ route('itemsshipments.index') }}">Shipments</a></li>
                    <li><a href="{{ route('itemsshipments.show', $itemshipment->id) }}">Shipment Details</a></li>
                    <li class="active">Edit Shipment</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    @cannot('edit-shipments')
    <div class="card">
        <div class="card-body">
            <div class="alert alert-danger mb-0">
                You do not have permission to edit shipments.
            </div>
        </div>
    </div>
@endcannot
    <div class="col-lg-12">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        {{-- ✅ Page-level guard --}}
        @cannot('edit-shipments')
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-danger mb-0">
                        You do not have permission to edit shipments.
                    </div>
                </div>
            </div>
        @endcannot

        @can('edit-shipments')
            <form action="{{ route('itemsshipments.update', $itemshipment->id) }}" method="POST" id="shipmentForm">
                @csrf
                @method('PUT')

                <div class="card mb-3">
                    <div class="card-header bg-primary text-white shipment-header">
                        <strong><i class="fa fa-ship"></i> Shipment Information</strong>
                        <a href="{{ route('itemsshipments.show', $itemshipment->id) }}" class="btn btn-light btn-sm">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Shipment Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('shipment_name') is-invalid @enderror"
                                           id="shipment_name" name="shipment_name"
                                           value="{{ old('shipment_name', $itemshipment->shipment_name) }}" required>
                                    @error('shipment_name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Shipment Date</label>
                                    <input type="date" class="form-control @error('shipment_date') is-invalid @enderror"
                                           id="shipment_date" name="shipment_date"
                                           value="{{ old('shipment_date', $itemshipment->shipment_date ? $itemshipment->shipment_date->format('Y-m-d') : '') }}">
                                    @error('shipment_date')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Status</label>
                                    @php
                                        $currentStatus = old('status', $itemshipment->status);

                                        // ✅ Map each status value → its permission slug
                                        $statusPermissions = [
                                            'draft'               => null,                        // draft = base edit, no extra perm
                                            'pending'             => 'status-ongoing',
                                            'shipped'             => 'status-shipped',
                                            'arrived_bd'          => 'status-arrived-bd',
                                            'waiting_for_customs' => 'status-waiting-for-custom',
                                            'delivered'           => 'status-delivered',
                                            'cancelled'           => 'status-cancelled',
                                        ];

                                        // Labels
                                        $statusLabels = [
                                            'draft'               => 'Draft',
                                            'pending'             => 'Shipment Ongoing',
                                            'shipped'             => 'Shipped',
                                            'arrived_bd'          => 'Arrived BD',
                                            'waiting_for_customs' => 'Waiting for Customs',
                                            'delivered'           => 'Delivered',
                                            'cancelled'           => 'Cancelled',
                                        ];

                                        // Which statuses can this user pick?
                                        $allowedStatuses = [];
                                        foreach ($statusLabels as $value => $label) {
                                            $slug = $statusPermissions[$value] ?? null;

                                            // Always allow "draft" (it's the base edit state)
                                            if ($slug === null) {
                                                $allowedStatuses[$value] = $label;
                                                continue;
                                            }

                                            if (auth()->user()->hasPermission($slug)) {
                                                $allowedStatuses[$value] = $label;
                                            }
                                        }

                                        // Ensure current status is always visible (so user can see it even if they can't change to it)
                                        if (!array_key_exists($currentStatus, $allowedStatuses) && isset($statusLabels[$currentStatus])) {
                                            // Prepend current so it's still shown but they can only keep it
                                            $allowedStatuses = [$currentStatus => $statusLabels[$currentStatus] . ' (current)'] + $allowedStatuses;
                                        }
                                    @endphp

                                    @if(count($allowedStatuses) > 1)
                                        <select class="form-control @error('status') is-invalid @enderror" id="status" name="status">
                                            @foreach($allowedStatuses as $value => $label)
                                                <option value="{{ $value }}" {{ $currentStatus == $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        {{-- Only one status available → show as read-only --}}
                                        <input type="text" class="form-control" value="{{ reset($allowedStatuses) }}" readonly>
                                        <input type="hidden" name="status" value="{{ array_key_first($allowedStatuses) }}">
                                        <small class="text-muted">
                                            <i class="fa fa-lock"></i>
                                            You don't have permission to change this status.
                                        </small>
                                    @endif

                                    @error('status')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-bold">Note</label>
                            <textarea class="form-control @error('note') is-invalid @enderror"
                                      id="note" name="note" rows="2"
                                      placeholder="Optional notes about this shipment">{{ old('note', $itemshipment->note) }}</textarea>
                            @error('note')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header bg-info text-white items-header">
                        <div>
                            <strong><i class="fa fa-cubes"></i> Select Items</strong>
                            <span class="badge badge-light ml-2" id="selectedCountBadge">0 selected</span>
                        </div>
                        <div>
                            <button type="button" class="btn btn-light btn-sm" id="selectAllBtn">
                                <i class="fa fa-check-square"></i> Select All
                            </button>
                            <button type="button" class="btn btn-light btn-sm" id="deselectAllBtn">
                                <i class="fa fa-square"></i> Deselect All
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        @if($availableItems->count() > 0 || $itemshipment->cargoItems->count() > 0)

                            <div class="alert alert-info small">
                                <i class="fa fa-info-circle"></i>
                                <strong>Tip:</strong> Click anywhere on a row to select or deselect the item.
                            </div>

                            <div class="table-responsive">
                                <table id="itemsTable" class="table table-bordered table-hover table-sm" width="100%">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th width="45"><input type="checkbox" id="selectAllItems"></th>
                                            <th>ID</th>
                                            <th>Printed CTN</th>
                                            <th>Written CTN</th>
                                            <th>Tracking No.</th>
                                            <th>Goods Name</th>
                                            <th>Pack No.</th>
                                            <th>Unit Pcs</th>
                                            <th>Weight</th>
                                            <th>Dimensions / CBM</th>
                                            <th>Shipping Mark</th>
                                            <th>Remark</th>
                                            <th>Copy</th>
                                            <th>Warehouse</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($itemshipment->cargoItems as $item)
                                            <tr class="shipment-item-row selected-row" data-item-id="{{ $item->id }}">
                                                <td class="text-center">
                                                    <input type="checkbox" value="{{ $item->id }}" class="item-checkbox" checked>
                                                </td>
                                                <td>{{ $item->id }}</td>
                                                <td>{{ $item->printed_ctn_no }}</td>
                                                <td>{{ $item->written_ctn_no }}</td>
                                                <td>{{ $item->tracking_no }}</td>
                                                <td>
                                                    {{ $item->goods_name_en }}
                                                    @if($item->goods_name_local)
                                                        <br><small class="text-muted">({{ $item->goods_name_local }})</small>
                                                    @endif
                                                </td>
                                                <td>{{ $item->pack_no }}</td>
                                                <td>{{ $item->unit_pcs }}</td>
                                                <td>{{ number_format((float) $item->weight_kg, 3) }}</td>
                                                <td>
                                                    {{ number_format((float) $item->length_cm, 0) }} ×
                                                    {{ number_format((float) $item->width_cm, 0) }} ×
                                                    {{ number_format((float) $item->height_cm, 0) }} =
                                                    {{ number_format((float) $item->cbm, 3) }}
                                                </td>
                                                <td>{{ $item->shipping_mark ?? '-' }}</td>
                                                <td>{{ $item->remark ?? '-' }}</td>
                                                <td>{{ $item->is_copy ? 'Yes' : 'No' }}</td>
                                                <td>{{ $item->warehouse ?? '-' }}</td>
                                            </tr>
                                        @endforeach

                                        @foreach($availableItems as $item)
                                            <tr class="shipment-item-row" data-item-id="{{ $item->id }}">
                                                <td class="text-center">
                                                    <input type="checkbox" value="{{ $item->id }}" class="item-checkbox">
                                                </td>
                                                <td>{{ $item->id }}</td>
                                                <td>{{ $item->printed_ctn_no }}</td>
                                                <td>{{ $item->written_ctn_no }}</td>
                                                <td>{{ $item->tracking_no }}</td>
                                                <td>
                                                    {{ $item->goods_name_en }}
                                                    @if($item->goods_name_local)
                                                        <br><small class="text-muted">({{ $item->goods_name_local }})</small>
                                                    @endif
                                                </td>
                                                <td>{{ $item->pack_no }}</td>
                                                <td>{{ $item->unit_pcs }}</td>
                                                <td>{{ number_format((float) $item->weight_kg, 3) }}</td>
                                                <td>
                                                    {{ number_format((float) $item->length_cm, 0) }} ×
                                                    {{ number_format((float) $item->width_cm, 0) }} ×
                                                    {{ number_format((float) $item->height_cm, 0) }} =
                                                    {{ number_format((float) $item->cbm, 3) }}
                                                </td>
                                                <td>{{ $item->shipping_mark ?? '-' }}</td>
                                                <td>{{ $item->remark ?? '-' }}</td>
                                                <td>{{ $item->is_copy ? 'Yes' : 'No' }}</td>
                                                <td>{{ $item->warehouse ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @error('selected_items')
                                <small class="text-danger d-block mt-2">{{ $message }}</small>
                            @enderror

                        @else
                            <div class="alert alert-warning mb-0">
                                <i class="fa fa-exclamation-triangle"></i> No items available.
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-body action-buttons">
                        <a href="{{ route('itemsshipments.show', $itemshipment->id) }}" class="btn btn-secondary">
                            <i class="fa fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fa fa-save"></i> Update Shipment
                        </button>
                    </div>
                </div>

            </form>
        @endcan
    </div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function () {
    'use strict';

    // Bail out if the form isn't rendered (no edit-shipments permission)
    if (!document.getElementById('shipmentForm')) return;

    const selectedIds = new Set();

    $('#itemsTable tbody .item-checkbox:checked').each(function () {
        const id = String($(this).val());
        if (id !== '') selectedIds.add(id);
    });

    const table = $('#itemsTable').DataTable({
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, 250, -1], [10, 25, 50, 100, 250, 'All']],
        order: [[1, 'desc']],
        columnDefs: [{ orderable: false, targets: 0 }],
        language: {
            search: 'Search items:',
            searchPlaceholder: 'Tracking no, goods name, CTN...',
            lengthMenu: 'Show _MENU_ items',
            info: 'Showing _START_ to _END_ of _TOTAL_ items',
            infoEmpty: 'No items found',
            infoFiltered: '(filtered from _MAX_ total)'
        }
    });

    function applySelectedState() {
        $('#itemsTable tbody tr').each(function () {
            const row = $(this);
            const checkbox = row.find('.item-checkbox');
            if (!checkbox.length) return;

            const id = String(checkbox.val());
            if (selectedIds.has(id)) {
                checkbox.prop('checked', true);
                row.addClass('row-selected');
            } else {
                checkbox.prop('checked', false);
                row.removeClass('row-selected');
            }
        });
        updateSelectedCount();
        updateSelectAllCheckbox();
    }

    function updateSelectedCount() {
        $('#selectedCountBadge').text(selectedIds.size + ' selected');
    }

    function updateSelectAllCheckbox() {
        const visibleRows = table.rows({ search: 'applied' }).nodes();
        const $checkboxes = $(visibleRows).find('.item-checkbox');

        if ($checkboxes.length === 0) {
            $('#selectAllItems').prop('checked', false).prop('indeterminate', false);
            return;
        }

        let checkedCount = 0;
        $checkboxes.each(function () {
            if (selectedIds.has(String($(this).val()))) checkedCount++;
        });

        if (checkedCount === 0) {
            $('#selectAllItems').prop('checked', false).prop('indeterminate', false);
        } else if (checkedCount === $checkboxes.length) {
            $('#selectAllItems').prop('checked', true).prop('indeterminate', false);
        } else {
            $('#selectAllItems').prop('checked', false).prop('indeterminate', true);
        }
    }

    $('#itemsTable').on('draw.dt', function () {
        applySelectedState();
    });

    $('#itemsTable tbody').on('click', 'tr.shipment-item-row', function (e) {
        if ($(e.target).closest('input, button, a, select, textarea, label').length) return;

        const checkbox = $(this).find('.item-checkbox');
        if (!checkbox.length) return;

        const id = String(checkbox.val());
        if (selectedIds.has(id)) {
            selectedIds.delete(id);
        } else {
            selectedIds.add(id);
        }
        applySelectedState();
    });

    $('#itemsTable tbody').on('change', '.item-checkbox', function () {
        const id = String($(this).val());
        if ($(this).is(':checked')) {
            selectedIds.add(id);
        } else {
            selectedIds.delete(id);
        }
        applySelectedState();
    });

    $('#selectAllItems').on('change', function () {
        const checked = $(this).is(':checked');
        const visibleRows = table.rows({ search: 'applied' }).nodes();

        $(visibleRows).find('.item-checkbox').each(function () {
            const id = String($(this).val());
            if (checked) {
                selectedIds.add(id);
            } else {
                selectedIds.delete(id);
            }
        });
        applySelectedState();
    });

    $('#selectAllBtn').on('click', function () {
        const visibleRows = table.rows({ search: 'applied' }).nodes();
        $(visibleRows).find('.item-checkbox').each(function () {
            selectedIds.add(String($(this).val()));
        });
        applySelectedState();
    });

    $('#deselectAllBtn').on('click', function () {
        if (!confirm('Are you sure you want to deselect all items?')) return;
        selectedIds.clear();
        applySelectedState();
    });

    $('#shipmentForm').on('submit', function () {
        $('#shipmentForm').find('input.dynamic-selected-item').remove();

        $('<input>')
            .attr({
                type: 'hidden',
                name: 'selected_items',
                value: Array.from(selectedIds).join(',')
            })
            .addClass('dynamic-selected-item')
            .appendTo('#shipmentForm');

        $('#submitBtn')
            .prop('disabled', true)
            .html('<i class="fa fa-spinner fa-spin"></i> Updating...');
    });

    applySelectedState();
});
</script>

<style>
    .shipment-header,
    .items-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
    }

    .dataTables_wrapper { width: 100%; }
    .dataTables_wrapper .dataTables_filter { text-align: right; margin-bottom: 10px; }
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 6px 10px;
        margin-left: 8px;
        min-width: 240px;
        outline: none;
    }
    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.15);
    }
    .dataTables_wrapper .dataTables_length { margin-bottom: 10px; }
    .dataTables_wrapper .dataTables_length select {
        width: auto;
        display: inline-block;
        margin: 0 6px;
        padding: 4px 8px;
        border: 1px solid #ced4da;
        border-radius: 4px;
    }
    .dataTables_wrapper .dataTables_info {
        padding-top: 8px;
        color: #6c757d;
        font-size: 13px;
    }
    .dataTables_wrapper .dataTables_paginate { padding-top: 8px; }

    #itemsTable {
        width: 100% !important;
        font-size: 13px;
    }
    #itemsTable th,
    #itemsTable td {
        padding: 7px 8px !important;
        vertical-align: middle !important;
    }
    #itemsTable thead th { white-space: nowrap; }

    #itemsTable tbody tr.shipment-item-row {
        cursor: pointer;
        transition: background-color 0.15s ease, box-shadow 0.15s ease;
    }
    #itemsTable tbody tr.shipment-item-row:hover {
        background-color: #eef5ff !important;
    }
    #itemsTable tbody tr.row-selected,
    #itemsTable tbody tr.selected-row {
        background-color: #cfe2ff !important;
        color: #084298;
    }
    #itemsTable tbody tr.row-selected:hover,
    #itemsTable tbody tr.selected-row:hover {
        background-color: #b9d5ff !important;
    }

    #itemsTable .item-checkbox,
    #itemsTable #selectAllItems {
        cursor: pointer;
        width: 16px;
        height: 16px;
    }
    #itemsTable td:first-child,
    #itemsTable th:first-child {
        text-align: center;
        vertical-align: middle !important;
    }

    #selectAllBtn,
    #deselectAllBtn { margin-left: 4px; }

    .action-buttons {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    #selectedCountBadge {
        font-size: 12px;
        padding: 5px 8px;
        color: #17a2b8;
    }

    @media (max-width: 768px) {
        .shipment-header,
        .items-header {
            flex-direction: column;
            align-items: flex-start;
        }
        .dataTables_wrapper .dataTables_filter {
            text-align: left;
            margin-top: 10px;
        }
        .dataTables_wrapper .dataTables_filter input {
            min-width: 180px;
            max-width: 100%;
        }
        .action-buttons {
            flex-direction: column;
            gap: 10px;
            align-items: stretch;
        }
        .action-buttons .btn { width: 100%; }
    }
</style>

@endsection