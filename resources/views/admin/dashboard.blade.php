@extends('admin.layouts.master')

@section('main_content')

<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Dashboard</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li class="active">Dashboard</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fa fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="row">

        {{-- Invoice Search --}}
        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <strong><i class="fa fa-file-text-o"></i> Find Invoice</strong>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.invoice.search') }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <input type="text"
                                   name="invoice_number"
                                   class="form-control"
                                   placeholder="e.g. INV20260927143052S12C5"
                                   value="{{ old('invoice_number') }}"
                                   required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fa fa-search"></i> Search Invoice
                        </button>
                    </form>
                    <small class="text-muted d-block mt-2">
                        Paste or type the invoice number to open the corresponding bill.
                    </small>
                </div>
            </div>
        </div>

        {{-- Tracking Search --}}
        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <strong><i class="fa fa-cube"></i> Track Cargo Item</strong>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.tracking.search') }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <input type="text"
                                   name="tracking_no"
                                   class="form-control"
                                   placeholder="e.g. SF1234567890"
                                   value="{{ old('tracking_no') }}"
                                   required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fa fa-search"></i> Search Tracking
                        </button>
                    </form>
                    <small class="text-muted d-block mt-2">
                        Enter the tracking number to view the cargo item's current status.
                    </small>
                </div>
            </div>
        </div>

    </div>

</div>

@endsection