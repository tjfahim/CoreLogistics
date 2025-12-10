@extends('admin.layouts.master')

@section('main_content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Edit Item</h1>
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
        <div class="card">
            <div class="card-header d-flex justify-content-between ">
                <strong>Edit Cargo Item</strong>
                <a href="{{ route('cargo-items.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
            </div>

            <div class="card-body">
                <form action="{{ route('cargo-items.update', $cargoItem->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Date</label>
                                <input type="date" name="date"
                                       value="{{ old('date', optional($cargoItem->date)->format('Y-m-d')) }}"
                                       class="form-control @error('date') is-invalid @enderror">
                                @error('date') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Printed CTN No.</label>
                                <input type="text" name="printed_ctn_no"
                                       value="{{ old('printed_ctn_no', $cargoItem->printed_ctn_no) }}"
                                       class="form-control @error('printed_ctn_no') is-invalid @enderror">
                                @error('printed_ctn_no') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Written CTN No.</label>
                                <input type="text" name="written_ctn_no"
                                       value="{{ old('written_ctn_no', $cargoItem->written_ctn_no) }}"
                                       class="form-control @error('written_ctn_no') is-invalid @enderror">
                                @error('written_ctn_no') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                    </div> <!-- row -->

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Tracking No.</label>
                                <input type="text" name="tracking_no"
                                       value="{{ old('tracking_no', $cargoItem->tracking_no) }}"
                                       class="form-control @error('tracking_no') is-invalid @enderror">
                                @error('tracking_no') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Goods Name (EN)</label>
                                <input type="text" name="goods_name_en"
                                       value="{{ old('goods_name_en', $cargoItem->goods_name_en) }}"
                                       class="form-control @error('goods_name_en') is-invalid @enderror">
                                @error('goods_name_en') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Goods Name (Local / 中文)</label>
                                <input type="text" name="goods_name_local"
                                       value="{{ old('goods_name_local', $cargoItem->goods_name_local) }}"
                                       class="form-control @error('goods_name_local') is-invalid @enderror">
                                @error('goods_name_local') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                    </div> <!-- row -->

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Pack No.</label>
                                <input type="number" name="pack_no"
                                       value="{{ old('pack_no', $cargoItem->pack_no) }}"
                                       class="form-control @error('pack_no') is-invalid @enderror">
                                @error('pack_no') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Unit pcs</label>
                                <input type="number" name="unit_pcs"
                                       value="{{ old('unit_pcs', $cargoItem->unit_pcs) }}"
                                       class="form-control @error('unit_pcs') is-invalid @enderror">
                                @error('unit_pcs') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Weight (kg)</label>
                                <input type="number" step="0.001" name="weight_kg"
                                       value="{{ old('weight_kg', $cargoItem->weight_kg) }}"
                                       class="form-control @error('weight_kg') is-invalid @enderror">
                                @error('weight_kg') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">CBM</label>
                                <input type="number" step="0.000001" name="cbm"
                                       value="{{ old('cbm', $cargoItem->cbm) }}"
                                       class="form-control @error('cbm') is-invalid @enderror">
                                @error('cbm') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                    </div> <!-- row -->

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Length (cm)</label>
                                <input type="number" step="0.01" name="length_cm"
                                       value="{{ old('length_cm', $cargoItem->length_cm) }}"
                                       class="form-control @error('length_cm') is-invalid @enderror">
                                @error('length_cm') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Width (cm)</label>
                                <input type="number" step="0.01" name="width_cm"
                                       value="{{ old('width_cm', $cargoItem->width_cm) }}"
                                       class="form-control @error('width_cm') is-invalid @enderror">
                                @error('width_cm') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Height (cm)</label>
                                <input type="number" step="0.01" name="height_cm"
                                       value="{{ old('height_cm', $cargoItem->height_cm) }}"
                                       class="form-control @error('height_cm') is-invalid @enderror">
                                @error('height_cm') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                    </div> <!-- row -->

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Shipping Mark</label>
                                <input type="text" name="shipping_mark"
                                       value="{{ old('shipping_mark', $cargoItem->shipping_mark) }}"
                                       class="form-control @error('shipping_mark') is-invalid @enderror">
                                @error('shipping_mark') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Warehouse</label>
                                <input type="text" name="warehouse"
                                       value="{{ old('warehouse', $cargoItem->warehouse) }}"
                                       class="form-control @error('warehouse') is-invalid @enderror">
                                @error('warehouse') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                    </div> <!-- row -->

                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Remark (liquid, battery, powder, etc.)</label>
                        <textarea name="remark"
                                  class="form-control @error('remark') is-invalid @enderror"
                                  rows="3">{{ old('remark', $cargoItem->remark) }}</textarea>
                        @error('remark') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold d-block">Copy?</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="is_copy"
                                   id="is_copy"
                                   value="1"
                                   {{ old('is_copy', $cargoItem->is_copy) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_copy">Yes (Copy)</label>
                        </div>
                    </div>

                       <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">Status</label>
                                <input type="text" name="status"
                                       value="{{ old('status', $cargoItem->status) }}"
                                       class="form-control @error('status') is-invalid @enderror">
                                @error('status') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                    <div class="d-flex justify-content-between mt-3">
                        <button class="btn btn-primary btn-sm">
                            <i class="fa fa-save"></i> Update Item
                        </button>
                    </div>
                </form>
            </div> <!-- card-body -->
        </div>
    </div>
</div>
@endsection