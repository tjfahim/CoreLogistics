@php $customer = $customer ?? null; @endphp

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    <div class="col-md-6 form-group">
        <label>Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $customer->name ?? '') }}" required>
    </div>
    <div class="col-md-6 form-group">
        <label>Number</label>
        <input type="text" name="number" class="form-control" value="{{ old('number', $customer->number ?? '') }}">
    </div>
</div>

<button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Customer</button>
<a href="{{ route('old-customers.index') }}" class="btn btn-secondary">Cancel</a>
