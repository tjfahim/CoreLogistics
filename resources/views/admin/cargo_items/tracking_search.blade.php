@extends('admin.layouts.master')

@section('main_content')
<div class="mb-2 no-print">
    <a href="{{ route('cargo-items.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back to Warehouse</a>
</div>


<div class="content mt-4">

    <h3>Search Cargo Item by Tracking Number</h3>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('cargo-items.tracking-view') }}" method="POST" class="mt-3">
        @csrf

        <div class="form-group">
            <label>Tracking Number:</label>
            <input type="text" name="tracking_no" class="form-control col-5" style="border:1px solid black" placeholder="Enter Tracking No">
        </div>

        <button type="submit" class="btn btn-primary mt-2">
            <i class="fa fa-search"></i> Search
        </button>

    </form>

</div>

@endsection
