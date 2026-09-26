@extends('admin.layouts.master')

@section('main_content')

<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Extra Shipping Marks</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li class="active">Customer Extra Shipping Marks</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">

    @if(session('success'))
        <div class="col-sm-12">
            <div class="alert alert-success alert-dismissible fade show">
                <span class="badge badge-pill badge-success">Success</span>
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="col-sm-12">
            <div class="alert alert-danger alert-dismissible fade show">
                <span class="badge badge-pill badge-danger">Error</span>
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        </div>
    @endif

    <div class="col-lg-12">
        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">
                <strong class="card-title">Extra Shipping Marks List</strong>
                <a href="{{ route('customer-extra-shipping-marks.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus"></i> Add Shipping Mark
                </a>
            </div>

            <div class="card-body">

                <!-- Search bar -->
                <form method="GET" action="{{ route('customer-extra-shipping-marks.index') }}" class="mb-3">
                    <div class="input-group" style="max-width: 500px;">
                        <input type="text"
                               name="search"
                               class="form-control"
                               placeholder="Search by customer name, code, or shipping mark..."
                               value="{{ $search ?? '' }}">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">
                                <i class="fa fa-search"></i> Search
                            </button>
                            @if(!empty($search))
                                <a href="{{ route('customer-extra-shipping-marks.index') }}" class="btn btn-secondary">
                                    <i class="fa fa-times"></i> Clear
                                </a>
                            @endif
                        </div>
                    </div>
                    @if(!empty($search))
                        <small class="text-muted d-block mt-2">
                            Showing results for: <strong>{{ $search }}</strong>
                            ({{ $marks->total() }} found)
                        </small>
                    @endif
                </form>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th width="60">ID</th>
                                <th>Customer</th>
                                <th>Customer Code</th>
                                <th>Shipping Mark</th>
                                <th width="150">Created</th>
                                <th width="180">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($marks as $mark)
                                <tr>
                                    <td>{{ $mark->id }}</td>
                                    <td>
                                        @if($mark->customer)
                                            <a href="{{ route('customers.edit', $mark->customer->id) }}">
                                                {{ $mark->customer->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $mark->customer->customer_code ?? '—' }}</td>
                                    <td><strong>{{ $mark->shipping_mark }}</strong></td>
                                    <td>{{ $mark->created_at->format('d M Y, h:i A') }}</td>
                                    <td>
                                        <a href="{{ route('customer-extra-shipping-marks.edit', $mark->id) }}"
                                           class="btn btn-warning btn-sm">
                                            <i class="fa fa-edit"></i> Edit
                                        </a>
                                        <form action="{{ route('customer-extra-shipping-marks.destroy', $mark->id) }}"
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to delete this shipping mark?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        @if(!empty($search))
                                            No shipping marks found for "<strong>{{ $search }}</strong>".
                                        @else
                                            No extra shipping marks found.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $marks->links() }}
                </div>

            </div>

        </div>
    </div>

</div>

@endsection