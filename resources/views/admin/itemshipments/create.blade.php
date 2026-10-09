@extends('admin.layouts.master')

@section('main_content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Create Shipment</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="{{ route('itemsshipments.index') }}">Shipments</a></li>
                    <li class="active">Create Shipment</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <strong>Create New Shipment</strong>
            </div>
            <div class="card-body card-block">

                {{-- ✅ Page-level guard --}}
                @cannot('create-shipments')
                    <div class="alert alert-danger mb-0">
                        You do not have permission to create shipments.
                    </div>
                @endcannot

                @can('create-shipments')
                    <form action="{{ route('itemsshipments.store') }}" method="POST">
                        @csrf

                        <div class="row form-group">
                            <div class="col col-md-6">
                                <label for="shipment_name" class="form-label">Shipment Name</label>
                                <input type="text" class="form-control" id="shipment_name" name="shipment_name"
                                       value="{{ old('shipment_name') }}" placeholder="Auto-generated if empty">
                                @error('shipment_name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col col-md-6">
                                <label for="shipment_date" class="form-label">Shipment Date</label>
                                <input type="date" class="form-control" id="shipment_date" name="shipment_date"
                                       value="{{ old('shipment_date') }}">
                                @error('shipment_date')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="row form-group">
                            <div class="col col-md-6">
                                <label for="status" class="form-label">Status</label>
                                @php
                                    // ✅ Status permissions apply on create too
                                    $createStatusPermissions = [
                                        'draft'               => null,
                                        'pending'             => 'status-ongoing',
                                        'waiting_for_customs' => 'status-waiting-for-custom',
                                        'delivered'           => 'status-delivered',
                                    ];
                                    $createStatusLabels = [
                                        'draft'               => 'Draft',
                                        'pending'             => 'Shipment Ongoing',
                                        'waiting_for_customs' => 'Waiting for Customs',
                                        'delivered'           => 'Delivered',
                                    ];
                                    $allowedCreateStatuses = [];
                                    foreach ($createStatusLabels as $value => $label) {
                                        $slug = $createStatusPermissions[$value] ?? null;
                                        if ($slug === null || auth()->user()->hasPermission($slug)) {
                                            $allowedCreateStatuses[$value] = $label;
                                        }
                                    }
                                @endphp

                                @if(count($allowedCreateStatuses) > 0)
                                    <select class="form-control" id="status" name="status">
                                        @foreach($allowedCreateStatuses as $value => $label)
                                            <option value="{{ $value }}" {{ old('status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="text" class="form-control" value="Draft" readonly>
                                    <input type="hidden" name="status" value="draft">
                                @endif
                                @error('status')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="note" class="form-label">Note</label>
                            <textarea class="form-control" id="note" name="note" rows="3"
                                      placeholder="Optional notes about this shipment">{{ old('note') }}</textarea>
                            @error('note')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Select Items</label>
                            @if($items->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="thead-light">
                                            <tr>
                                                <th><input type="checkbox" id="selectAllItems"></th>
                                                <th>ID</th>
                                                <th>Printed CTN NO.</th>
                                                <th>Written CTN NO.</th>
                                                <th>Tracking NO.</th>
                                                <th>Goods Name</th>
                                                <th>Pack No.</th>
                                                <th>Unit Pcs</th>
                                                <th>Weight (kg)</th>
                                                <th>CBM</th>
                                                <th>Shipping Mark</th>
                                                <th>Remark</th>
                                                <th>Copy</th>
                                                <th>Warehouse</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($items as $item)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="selected_items[]" value="{{ $item->id }}"
                                                           class="item-checkbox">
                                                </td>
                                                <td>{{ $item->id }}</td>
                                                <td>{{ $item->printed_ctn_no }}</td>
                                                <td>{{ $item->written_ctn_no }}</td>
                                                <td>{{ $item->tracking_no }}</td>
                                                <td>{{ $item->goods_name_en }}</td>
                                                <td>{{ $item->pack_no }}</td>
                                                <td>{{ $item->unit_pcs }}</td>
                                                <td>{{ $item->weight_kg ?? '0.00' }}</td>
                                                <td>
                                                    {{ number_format($item->length_cm, 0) }}x{{ number_format($item->width_cm, 0) }}x{{ number_format($item->height_cm, 0) }} = {{ number_format($item->cbm ?? 0, 3) }}
                                                </td>
                                                <td>{{ $item->shipping_mark }}</td>
                                                <td>{{ $item->remark }}</td>
                                                <td>{{ $item->is_copy ? 'Yes' : 'No' }}</td>
                                                <td>{{ $item->warehouse }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @error('selected_items')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            @else
                                <div class="alert alert-warning">
                                    No available items found. All items might already be in other shipments.
                                </div>
                            @endif
                        </div>

                        <input type="hidden" name="selected_items" id="selectedItemsInput">

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Create Shipment</button>
                            <a href="{{ route('itemsshipments.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                @endcan

            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Bail if the form isn't rendered
    if (!$('#selectAllItems').length) return;

    $('#selectAllItems').change(function() {
        $('.item-checkbox').prop('checked', $(this).is(':checked'));
        updateSelectedItems();
    });

    $('.item-checkbox').change(function() {
        updateSelectedItems();
    });

    function updateSelectedItems() {
        const selectedItems = [];
        $('.item-checkbox:checked').each(function() {
            selectedItems.push($(this).val());
        });
        $('#selectedItemsInput').val(selectedItems.join(','));
    }

    // Set default date to today
    if ($('#shipment_date').length) {
        $('#shipment_date').val(new Date().toISOString().split('T')[0]);
    }
});
</script>

<style>
    .table td, .table th {
        padding: 0.5rem !important;
        vertical-align: middle !important;
    }
</style>
@endsection