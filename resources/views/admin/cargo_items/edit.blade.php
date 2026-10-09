@extends('admin.layouts.master')

@section('main_content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Edit Item #{{ $cargoItem->id }}</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="{{ route('cargo-items.index') }}">Items</a></li>
                    <li class="active">Edit Item</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
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

        @cannot('edit-warehouse-item')
            <div class="alert alert-danger">
                You do not have permission to edit warehouse items.
            </div>
        @endcannot

        @can('edit-warehouse-item')
            <form action="{{ route('cargo-items.update', $cargoItem->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- CARD 1: Basic Information --}}
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <strong><i class="fa fa-info-circle"></i> Basic Information</strong>
                        <a href="{{ route('cargo-items.index') }}" class="btn btn-light btn-sm">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="font-weight-bold">Date</label>
                                    <input type="date" name="date"
                                           value="{{ old('date', optional($cargoItem->date)->format('Y-m-d')) }}"
                                           class="form-control @error('date') is-invalid @enderror">
                                    @error('date') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="font-weight-bold">Printed CTN No.</label>
                                    <input type="text" name="printed_ctn_no"
                                           value="{{ old('printed_ctn_no', $cargoItem->printed_ctn_no) }}"
                                           class="form-control @error('printed_ctn_no') is-invalid @enderror">
                                    @error('printed_ctn_no') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="font-weight-bold">Written CTN No.</label>
                                    <input type="text" name="written_ctn_no"
                                           value="{{ old('written_ctn_no', $cargoItem->written_ctn_no) }}"
                                           class="form-control @error('written_ctn_no') is-invalid @enderror">
                                    @error('written_ctn_no') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="font-weight-bold">Tracking No. <span class="text-danger">*</span></label>
                                    <input type="text" name="tracking_no"
                                           value="{{ old('tracking_no', $cargoItem->tracking_no) }}"
                                           class="form-control @error('tracking_no') is-invalid @enderror" required>
                                    @error('tracking_no') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Goods Name (EN) <span class="text-danger">*</span></label>
                                    <input type="text" name="goods_name_en"
                                           value="{{ old('goods_name_en', $cargoItem->goods_name_en) }}"
                                           class="form-control @error('goods_name_en') is-invalid @enderror" required>
                                    @error('goods_name_en') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Goods Name (Local / 中文)</label>
                                    <input type="text" name="goods_name_local"
                                           value="{{ old('goods_name_local', $cargoItem->goods_name_local) }}"
                                           class="form-control @error('goods_name_local') is-invalid @enderror">
                                    @error('goods_name_local') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CARD 2: Quantity & Weight --}}
                <div class="card mb-3">
                    <div class="card-header bg-info text-white">
                        <strong><i class="fa fa-cubes"></i> Quantity & Weight</strong>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="font-weight-bold">Pack No.</label>
                                    <input type="number" name="pack_no"
                                           value="{{ old('pack_no', $cargoItem->pack_no) }}"
                                           class="form-control @error('pack_no') is-invalid @enderror">
                                    @error('pack_no') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="font-weight-bold">Unit pcs</label>
                                    <input type="number" name="unit_pcs"
                                           value="{{ old('unit_pcs', $cargoItem->unit_pcs) }}"
                                           class="form-control @error('unit_pcs') is-invalid @enderror">
                                    @error('unit_pcs') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="font-weight-bold">Weight (kg)</label>
                                    <input type="number" step="0.001" name="weight_kg"
                                           value="{{ old('weight_kg', $cargoItem->weight_kg) }}"
                                           class="form-control @error('weight_kg') is-invalid @enderror">
                                    @error('weight_kg') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="font-weight-bold">CBM</label>
                                    <input type="number" step="0.000001" name="cbm"
                                           value="{{ old('cbm', $cargoItem->cbm) }}"
                                           class="form-control @error('cbm') is-invalid @enderror">
                                    @error('cbm') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CARD 3: Dimensions --}}
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">
                        <strong><i class="fa fa-arrows-alt"></i> Dimensions (cm)</strong>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">Length (cm)</label>
                                    <input type="number" step="0.01" name="length_cm"
                                           value="{{ old('length_cm', $cargoItem->length_cm) }}"
                                           class="form-control @error('length_cm') is-invalid @enderror">
                                    @error('length_cm') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">Width (cm)</label>
                                    <input type="number" step="0.01" name="width_cm"
                                           value="{{ old('width_cm', $cargoItem->width_cm) }}"
                                           class="form-control @error('width_cm') is-invalid @enderror">
                                    @error('width_cm') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">Height (cm)</label>
                                    <input type="number" step="0.01" name="height_cm"
                                           value="{{ old('height_cm', $cargoItem->height_cm) }}"
                                           class="form-control @error('height_cm') is-invalid @enderror">
                                    @error('height_cm') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CARD 4: Shipping & Status --}}
                <div class="card mb-3">
                    <div class="card-header bg-dark text-white">
                        <strong><i class="fa fa-ship"></i> Shipping & Status</strong>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Shipping Mark</label>
                                    <input type="text" name="shipping_mark"
                                           value="{{ old('shipping_mark', $cargoItem->shipping_mark) }}"
                                           class="form-control @error('shipping_mark') is-invalid @enderror">
                                    @error('shipping_mark') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Warehouse</label>
                                    <input type="text" name="warehouse"
                                           value="{{ old('warehouse', $cargoItem->warehouse) }}"
                                           class="form-control @error('warehouse') is-invalid @enderror">
                                    @error('warehouse') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Status</label>
                                    @php
                                        $statuses = [
                                            'pending'              => 'Pending',
                                            'packed'               => 'Packed',
                                            'shipping_ongoing'     => 'Shipping Ongoing',
                                            'arrived_bd'           => 'Arrived BD',
                                            'waiting_for_customs'  => 'Waiting for Customs',
                                            'ready_to_deliver'     => 'Ready to Deliver',
                                            'delivered'            => 'Delivered',
                                        ];
                                        $currentStatus = old('status', $cargoItem->status);
                                    @endphp
                                    <select name="status" class="form-control @error('status') is-invalid @enderror">
                                        @foreach($statuses as $value => $label)
                                            <option value="{{ $value }}" {{ $currentStatus === $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('status') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold d-block">Copy?</label>
                                    <div class="form-check form-check-inline mt-2">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               name="is_copy"
                                               id="is_copy"
                                               value="1"
                                               {{ old('is_copy', $cargoItem->is_copy) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_copy">
                                            Yes, this is a copy
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CARD 5: Remark --}}
                <div class="card mb-3">
                    <div class="card-header bg-warning text-dark">
                        <strong><i class="fa fa-sticky-note"></i> Remark</strong>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-0">
                            <label class="font-weight-bold">Remark (liquid, battery, powder, etc.)</label>
                            <textarea name="remark"
                                      class="form-control @error('remark') is-invalid @enderror"
                                      rows="3"
                                      placeholder="Any special notes about this item...">{{ old('remark', $cargoItem->remark) }}</textarea>
                            @error('remark') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="card">
                    <div class="card-body d-flex justify-content-between">
                        <a href="{{ route('cargo-items.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fa fa-times"></i> Cancel
                        </a>

                        <div>
                            <button type="submit" class="btn btn-primary btn-sm px-3">
                                <i class="fa fa-save"></i> Update Item
                            </button>
                        </div>
                    </div>
                </div>

            </form>
        @endcan

        {{-- Danger zone: delete — only for delete-warehouse-item --}}
        @can('delete-warehouse-item')
            <div class="card border-danger mt-3">
                <div class="card-header bg-danger text-white">
                    <strong><i class="fa fa-exclamation-triangle"></i> Danger Zone</strong>
                </div>
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Delete this item</strong>
                        <p class="mb-0 text-muted small">Once deleted, this cannot be undone.</p>
                    </div>
                    <form action="{{ route('cargo-items.destroy', $cargoItem->id) }}"
                          method="POST"
                          onsubmit="return confirm('Are you sure you want to delete item #{{ $cargoItem->id }}? This cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm">
                            <i class="fa fa-trash"></i> Delete Item
                        </button>
                    </form>
                </div>
            </div>
        @endcan

    </div>
</div>
@endsection