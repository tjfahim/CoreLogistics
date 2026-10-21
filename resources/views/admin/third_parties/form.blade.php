@extends('admin.layouts.master')
@section('main_content')
<div class="breadcrumbs"><div class="col-sm-12"><div class="page-header float-left"><div class="page-title"><h1>{{ $party->exists ? 'Edit' : 'Add' }} Third Party</h1></div></div></div></div>
<div class="content mt-3"><div class="col-lg-8">
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
    <div class="card"><div class="card-body">
        <form method="POST" action="{{ $party->exists ? route('third-parties.update', $party->id) : route('third-parties.store') }}">
            @csrf @if($party->exists) @method('PUT') @endif
            <div class="row">
                <div class="col-md-8 form-group"><label class="font-weight-bold">Company name *</label>
                    <input name="name" class="form-control" value="{{ old('name', $party->name) }}" required></div>
                <div class="col-md-4 form-group"><label class="font-weight-bold">Code</label>
                    <input name="code" class="form-control" value="{{ old('code', $party->code) }}" placeholder="ABC"></div>
                <div class="col-md-6 form-group"><label>Contact person</label><input name="contact_person" class="form-control" value="{{ old('contact_person', $party->contact_person) }}"></div>
                <div class="col-md-6 form-group"><label>Phone</label><input name="phone" class="form-control" value="{{ old('phone', $party->phone) }}"></div>
                <div class="col-md-6 form-group"><label>Email</label><input name="email" type="email" class="form-control" value="{{ old('email', $party->email) }}"></div>
                <div class="col-md-6 form-group"><label>Address</label><input name="address" class="form-control" value="{{ old('address', $party->address) }}"></div>
                <div class="col-12 form-group"><label>Note</label><textarea name="note" rows="2" class="form-control">{{ old('note', $party->note) }}</textarea></div>
                <div class="col-12 form-group"><label><input type="checkbox" name="is_active" value="1" {{ old('is_active', $party->is_active) ? 'checked' : '' }}> Active</label></div>
            </div>
            <button class="btn btn-primary"><i class="fa fa-save"></i> Save</button>
            <a href="{{ route('third-parties.index') }}" class="btn btn-secondary">Back</a>
        </form>
    </div></div>
</div></div>
@endsection
