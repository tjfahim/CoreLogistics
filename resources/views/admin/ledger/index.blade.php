@extends('admin.layouts.master')

@section('main_content')

<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title"><h1>Customer Ledger</h1></div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li class="active">Accounting / Ledger</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">

    {{-- Grand totals --}}
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h6>Total Billed (All Customers)</h6>
                    <h3>Tk {{ number_format($grandTotalBilled, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h6>Total Collected</h6>
                    <h3 class="text-success">Tk {{ number_format($grandTotalPaid, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h6>Total Outstanding</h6>
                    <h3 class="text-danger">Tk {{ number_format($grandTotalDue, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong class="card-title">Customer Balances</strong>
                    <form method="GET" class="form-inline">
                        <input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm mr-2" placeholder="Search customer, code, phone...">
                        <button class="btn btn-sm btn-primary" type="submit"><i class="fa fa-search"></i></button>
                    </form>
                </div>

                <div class="card-body table-responsive">
                    <table id="ledgerTable" class="table table-striped table-bordered table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>Customer</th>
                                <th>Code</th>
                                <th>Shipments</th>
                                <th>Total Billed</th>
                                <th>Total Paid</th>
                                <th>Total Due</th>
                                <th>Last Payment</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ledgerRows as $row)
                                <tr>
                                    <td>{{ $row['customer']->name }}</td>
                                    <td>{{ $row['customer']->customer_code }}</td>
                                    <td>{{ $row['shipment_count'] }}</td>
                                    <td>Tk {{ number_format($row['total_billed'], 2) }}</td>
                                    <td class="text-success">Tk {{ number_format($row['total_paid'], 2) }}</td>
                                    <td class="text-danger">Tk {{ number_format($row['total_due'], 2) }}</td>
                                    <td>{{ $row['last_payment_date'] ? \Carbon\Carbon::parse($row['last_payment_date'])->format('Y-m-d') : '—' }}</td>
                                    <td>
                                        @if($row['status'] === 'clear')
                                            <span class="badge badge-success">Clear</span>
                                        @elseif($row['status'] === 'due')
                                            <span class="badge badge-danger">Due</span>
                                        @else
                                            <span class="badge badge-secondary">No Billing</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('ledger.customer', $row['customer']->id) }}" class="btn btn-sm btn-info">
                                            <i class="fa fa-book"></i> View Ledger
                                        </a>
                                        @can('add-payment')
                                            @if($row['total_due'] > 0)
                                                <a href="{{ route('payments.receive', $row['customer']->id) }}" class="btn btn-sm btn-success">
                                                    <i class="fa fa-money"></i> Receive
                                                </a>
                                            @endif
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center text-muted">No customers found</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
$(document).ready(function() {
    $('#ledgerTable').DataTable({
        "order": [[5, "desc"]],
        "columnDefs": [{ "orderable": false, "targets": 8 }],
        "pageLength": 25,
        "lengthMenu": [10, 25, 50, 100],
        "responsive": true
    });
});
</script>
@endsection