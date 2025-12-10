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
                    <li><a href="{{ route('shipments.index') }}">Shipments</a></li>
                    <li class="active">Edit Shipment</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="col-lg-12">
        <div class="card">

            <div class="card-header d-flex justify-content-between">
                <strong>Edit Shipment</strong>
                <a href="{{ route('shipments.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
            </div>

            <div class="card-body">

                <form action="{{ route('shipments.update', $shipment->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Shipment Code --}}
                    <div class="form-group">
                        <label class="font-weight-bold">Shipment Code</label>
                        <input type="text"
                               name="shipment_code"
                               class="form-control @error('shipment_code') is-invalid @enderror"
                               value="{{ old('shipment_code', $shipment->shipment_code) }}">
                        @error('shipment_code')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Select Boxes (Checkboxes) --}}
                    <div class="form-group">
                        <label class="font-weight-bold">Select Boxes</label>
                        <div class="row">
                            @foreach($boxes as $box)
                                <div class="col-md-3 ">
                                    <div class="form-check">
                                        <input class="form-check-input box-checkbox" type="checkbox" 
                                               name="box_ids[]" value="{{ $box->id }}" 
                                               data-weight="{{ $box->total_weight }}"
                                               id="box_{{ $box->id }}" style="margin-top: 7px;"
                                               {{ in_array($box->id, $shipment->boxes->pluck('id')->toArray()) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="box_{{ $box->id }}">
                                            {{ $box->box_code }} - {{ $box->total_weight }} kg
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('box_ids')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Auto Weight --}}
                    <div class="form-group">
                        <label class="font-weight-bold">Total Weight</label>
                        <input type="number" step="0.01" readonly
                               id="total_weight"
                               name="total_weight"
                               value="{{ $shipment->boxes->sum('total_weight') }}"
                               class="form-control">
                    </div>

                    {{-- Status --}}
                    <div class="form-group">
                        <label class="font-weight-bold">Status</label>
                        <select name="status" class="form-control @error('status') is-invalid @enderror">
                            <option value="pending" {{ $shipment->status=='pending' ? 'selected':'' }}>Pending</option>
                            <option value="freight_complete" {{ $shipment->status=='freight_complete' ? 'selected':'' }}>Freight Complete</option>
                            <option value="cnf_complete" {{ $shipment->status=='cnf_complete' ? 'selected':'' }}>CNF Complete</option>
                            <option value="airport_warehouse_complete" {{ $shipment->status=='airport_warehouse_complete' ? 'selected':'' }}>Airport Warehouse Complete</option>
                            <option value="our_warehouse" {{ $shipment->status=='our_warehouse' ? 'selected':'' }}>Our Warehouse</option>
                        </select>
                        @error('status')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <button class="btn btn-primary btn-sm px-4 mt-3">
                        <i class="fa fa-save"></i> Update Shipment
                    </button>

                </form>

            </div>

        </div>
    </div>
</div>

<script>
const checkboxes = document.querySelectorAll('.box-checkbox');
const totalWeightInput = document.getElementById('total_weight');

function updateTotalWeight(){
    let total = 0;
    checkboxes.forEach(c => {
        if(c.checked){
            total += parseFloat(c.dataset.weight);
        }
    });
    totalWeightInput.value = total.toFixed(2);
}

checkboxes.forEach(cb => cb.addEventListener('change', updateTotalWeight));
</script>

@endsection
