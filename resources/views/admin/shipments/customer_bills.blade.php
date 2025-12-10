@extends('admin.layouts.master')

@section('main_content')

<div class="container mt-4">

    <h3 class="mb-3">Customer Bills for Shipment: {{ $shipment->shipment_code }}</h3>

    <a href="{{ route('shipments.index') }}" class="btn btn-secondary mb-3">
        <i class="fa fa-arrow-left"></i> Back
    </a>

    <table class="table table-bordered table-striped" id="billTable">
        <thead class="thead-dark">
            <tr>
                <th>Customer</th>
                <th>Total Weight (KG)</th>
                <th>Delivered Weight (KG)</th>
                <th>Remaining Weight (KG)</th>
                <th>Total Price</th>
                <th>Paid</th>
                <th>Due</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bills as $bill)
                <tr>
                    <td>{{ $bill->customer->name }}</td>
                    <td>{{ number_format($bill->total_weight,2) }}</td>
                    <td>{{ number_format($bill->total_weight - $bill->remaining_weight,2) }}</td>
                    <td>{{ number_format($bill->remaining_weight,2) }}</td>
                    <td>{{ number_format($bill->total_price,2) }}</td>
                    <td>{{ number_format($bill->paid_amount,2) }}</td>
                    <td>{{ number_format($bill->due_amount,2) }}</td>
                    <td>
                        <span class="badge bg-{{ $bill->status=='paid'?'success':($bill->status=='partial'?'warning':'danger') }}">
                            {{ ucfirst($bill->status) }}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-primary btn-sm payBtn"
                                data-id="{{ $bill->id }}"
                                data-customer="{{ $bill->customer->name }}"
                                data-due="{{ $bill->due_amount }}"
                                data-weight="{{ $bill->remaining_weight }}"
                                data-toggle="modal"
                                data-target="#payModal">
                            <i class="fa fa-money-bill"></i> Pay
                        </button>
                         <button class="btn btn-info btn-sm historyBtn"
                        data-id="{{ $bill->id }}"
                        data-url="{{ route('customerBill.history', $bill->id) }}"
                        data-toggle="modal"
                        data-target="#historyModal">
                        <i class="fa fa-history"></i> History
                </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</div>


<div class="modal fade" id="payModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="payForm">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Customer Payment</h5>
                    <button type="button" class="close text-white" data-bs-dismiss="modal">×</button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label>Customer</label>
                        <input type="text" id="payCustomer" class="form-control" readonly>
                    </div>

                    <div class="mb-3">
                        <label>Due Amount</label>
                        <input type="text" id="payDue" class="form-control" readonly>
                    </div>

                    <div class="mb-3">
                        <label>Delivery Weight (KG)</label>
                        <input type="number" step="0.01" name="delivery_weight" required class="form-control">
                    </div>

                    <div class="mb-3">
                        <label>Pay Amount</label>
                        <input type="number" name="amount" required class="form-control">
                    </div>

                    <div class="mb-3">
                        <label>Delivery Date</label>
                        <input type="date" name="delivery_date" required class="form-control">
                    </div>

                    <div class="mb-3">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-success">Submit Payment</button>
                </div>
            </div>
        </form>
    </div>
</div>


<!-- History Modal -->
<div class="modal fade" id="historyModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title">Payment & Delivery History</h5>
                <button type="button" class="close text-white" data-dismiss="modal">×</button>
            </div>

            <div class="modal-body" id="historyBody">
                <!-- Filled by jQuery -->
            </div>

        </div>
    </div>
</div>


<script>
$(document).ready(function() {

 
    // Apply DataTables
    $('#billTable').DataTable({
        pageLength: 10,
        order: [[0,'asc']]
    });
  $('.payBtn').click(function() {
        let billId = $(this).data('id');
        let customer = $(this).data('customer');
        let due = $(this).data('due');
        let remaining = $(this).data('weight');

        $('#payCustomer').val(customer);
        $('#payDue').val(parseFloat(due).toFixed(2));
        $('input[name="delivery_weight"]').attr('max', remaining).val(remaining);

        let action = "{{ route('customerBill.pay', ':id') }}".replace(':id', billId);
        $('#payForm').attr('action', action);
    });
    // Load History
  $('.historyBtn').on('click', function() {
    let url = $(this).data('url');

    $('#historyBody').html('<div class="text-center p-3">Loading...</div>');

    $.get(url, function(res) {
        $('#historyBody').html(res);
    });
});

});
</script>

@endsection
