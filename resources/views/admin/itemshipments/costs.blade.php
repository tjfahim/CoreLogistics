@extends('admin.layouts.master')

@section('main_content')

<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Item Shipment Costs</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="{{ route('itemsshipments.index') }}">Item Shipments</a></li>
                    <li class="active">Costs</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">

    {{-- ====================================================== --}}
    {{-- GLOBAL VALIDATION ERRORS --}}
    {{-- ====================================================== --}}
    @if($errors->any())
        <div class="col-sm-12">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h5 class="mb-2">
                    <i class="fa fa-exclamation-triangle"></i>
                    <strong>Please fix the following errors:</strong>
                </h5>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    @endif

    {{-- ====================================================== --}}
    {{-- SUCCESS / ERROR / INFO FLASH MESSAGES --}}
    {{-- ====================================================== --}}
    @if(session('success'))
        <div class="col-sm-12">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <span class="badge badge-pill badge-success">Success</span> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="col-sm-12">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <span class="badge badge-pill badge-danger">Error</span> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    @endif

    @if(session('info'))
        <div class="col-sm-12">
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <span class="badge badge-pill badge-info">Info</span> {{ session('info') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    @endif

    <div class="row">
        <!-- Item Shipment Info -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <strong>Item Shipment Information</strong>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Shipment Name:</strong> {{ $itemShipment->shipment_name }}
                        </div>
                        <div class="col-md-2">
                            <strong>Total Items:</strong> {{ $itemShipment->total_items }}
                        </div>
                        <div class="col-md-2">
                            <strong>Total Weight:</strong> {{ number_format($itemShipment->total_weight, 0) }} kg
                        </div>
                        <div class="col-md-2">
                            <strong>Total CBM:</strong> {{ number_format($itemShipment->total_cbm, 4) }}
                        </div>
                        <div class="col-md-3">
                            <strong>Status:</strong>
                            <span class="badge badge-{{ $itemShipment->status === 'completed' ? 'success' : ($itemShipment->status === 'cost_calculated' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($itemShipment->status) }}
                            </span>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-4">
                            <strong>Total Cost:</strong> {{ number_format($itemShipment->total_cost, 0) }}
                        </div>
                        <div class="col-md-4">
                            <strong>Total Paid:</strong> {{ number_format($itemShipment->total_paid, 0) }}
                        </div>
                        <div class="col-md-4">
                            <strong>Total Due:</strong> {{ number_format($itemShipment->total_due, 0) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ====================================================== -->
        <!-- Add Cost Form — ✅ ONLY if user has add-shipment-cost -->
        <!-- ====================================================== -->
        @can('add-shipment-cost')
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <strong>Add New Cost</strong>
                </div>
                <div class="card-body">
                    <form action="{{ route('itemshipments.costs.store', $itemShipment->id) }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="cost_type_id">Cost Type <span class="text-danger">*</span></label>
                            <select name="cost_type_id" id="cost_type_id"
                                    class="form-control @error('cost_type_id') is-invalid @enderror" required>
                                <option value="">Select Cost Type</option>
                                @foreach($costTypes as $costType)
                                    <option value="{{ $costType->id }}"
                                        {{ old('cost_type_id') == $costType->id ? 'selected' : '' }}>
                                        {{ $costType->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('cost_type_id')
                                <small class="text-danger d-block">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group" id="agent_field" style="display:none;">
                            <label for="agent_id">Agent</label>
                            <select name="agent_id" id="agent_id"
                                    class="form-control @error('agent_id') is-invalid @enderror">
                                <option value="">Select Agent</option>
                                @foreach($agents as $agent)
                                    <option value="{{ $agent->id }}"
                                        {{ old('agent_id') == $agent->id ? 'selected' : '' }}>
                                        {{ $agent->name }} - {{ $agent->company_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('agent_id')
                                <small class="text-danger d-block">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="row" id="rate_row" style="display:none;">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Rate per KG</label>
                                    <input type="number" step="0.01" name="rate_per_kg" id="rate_per_kg"
                                           value="{{ old('rate_per_kg') }}"
                                           class="form-control @error('rate_per_kg') is-invalid @enderror">
                                    @error('rate_per_kg')
                                        <small class="text-danger d-block">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Total Weight (KG)</label>
                                    <input type="number" step="0.01" name="total_weight" id="total_weight"
                                           value="{{ old('total_weight', $itemShipment->total_weight) }}"
                                           class="form-control @error('total_weight') is-invalid @enderror">
                                    @error('total_weight')
                                        <small class="text-danger d-block">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group" id="amount_row">
                            <label>Amount <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="amount" id="amount"
                                   value="{{ old('amount') }}"
                                   class="form-control @error('amount') is-invalid @enderror" required>
                            @error('amount')
                                <small class="text-danger d-block">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" rows="2"
                                      class="form-control @error('description') is-invalid @enderror"
                                      placeholder="Cost description">{{ old('description') }}</textarea>
                            @error('description')
                                <small class="text-danger d-block">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="cost_date">Cost Date <span class="text-danger">*</span></label>
                            <input type="date" name="cost_date" id="cost_date"
                                   value="{{ old('cost_date', date('Y-m-d')) }}"
                                   class="form-control @error('cost_date') is-invalid @enderror" required>
                            @error('cost_date')
                                <small class="text-danger d-block">{{ $message }}</small>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fa fa-plus"></i> Add Cost
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endcan

        <!-- ====================================================== -->
        <!-- Costs List — column width adjusts if add form hidden  -->
        <!-- ====================================================== -->
        <div class="{{ auth()->user()->hasPermission('add-shipment-cost') ? 'col-md-8' : 'col-md-12' }}">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Item Shipment Costs</strong>
                    <span class="badge badge-info">{{ $itemShipment->costs->count() }} costs</span>
                </div>
                <div class="card-body">
                    @if($itemShipment->costs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Cost Type</th>
                                        <th>Agent</th>
                                        <th>Amount</th>
                                        <th>Paid</th>
                                        <th>Due</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($itemShipment->costs as $cost)
                                    <tr>
                                        <td>{{ $cost->costType->name }}</td>
                                        <td>{{ $cost->agent ? $cost->agent->name : 'N/A' }}</td>
                                        <td>{{ number_format($cost->amount, 0) }}</td>
                                        <td>{{ number_format($cost->paid_amount, 0) }}</td>
                                        <td>{{ number_format($cost->due_amount, 0) }}</td>
                                        <td>
                                            <span class="badge badge-{{ $cost->status === 'paid' ? 'success' : ($cost->status === 'partial' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($cost->status) }}
                                            </span>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($cost->cost_date)->format('M d, Y') }}</td>
                                        <td>
                                            {{-- ✅ Pay button (add-shipment-cost used as "add payment") --}}
                                            @can('add-shipment-cost')
                                                <button type="button" class="btn btn-primary btn-sm"
                                                        data-toggle="modal" data-target="#paymentModal{{ $cost->id }}">
                                                    <i class="fa fa-money-bill"></i> Pay
                                                </button>
                                            @endcan

                                            {{-- ✅ Delete cost --}}
                                            @can('delete-shipment-cost')
                                                <form action="{{ route('itemshipments.costs.destroy', $cost->id) }}"
                                                      method="POST" class="d-inline delete-cost-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan

                                            @cannot('add-shipment-cost')
                                                @cannot('delete-shipment-cost')
                                                    <span class="text-muted">—</span>
                                                @endcannot
                                            @endcannot
                                        </td>
                                    </tr>

                                    <!-- Payment Modal -->
                                    @can('add-shipment-cost')
                                    <div class="modal fade" id="paymentModal{{ $cost->id }}" tabindex="-1" role="dialog">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Add Payment - {{ $cost->costType->name }}</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <form action="{{ route('itemshipments.costs.payments.store', $cost->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label>Due Amount: {{ number_format($cost->due_amount, 0) }}</label>
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="amount{{ $cost->id }}">Payment Amount <span class="text-danger">*</span></label>
                                                            <input type="number"
                                                                   step="0.01"
                                                                   name="amount"
                                                                   id="amount{{ $cost->id }}"
                                                                   class="form-control"
                                                                   @if(!$cost->costType->have_partial_payment)
                                                                       value="{{ number_format($cost->due_amount, 2, '.', '') }}"
                                                                       readonly
                                                                   @endif
                                                                   required
                                                                   max="{{ $cost->due_amount }}">
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="payment_date{{ $cost->id }}">Payment Date <span class="text-danger">*</span></label>
                                                            <input type="date" name="payment_date" id="payment_date{{ $cost->id }}"
                                                                   class="form-control" required value="{{ date('Y-m-d') }}">
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="payment_method{{ $cost->id }}">Payment Method <span class="text-danger">*</span></label>
                                                            <select name="payment_method" id="payment_method{{ $cost->id }}"
                                                                    class="form-control" required>
                                                                <option value="cash">Cash</option>
                                                                <option value="bank">Bank Transfer</option>
                                                                <option value="check">Check</option>
                                                                <option value="mobile_banking">Mobile Banking</option>
                                                            </select>
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="reference_no{{ $cost->id }}">Reference No</label>
                                                            <input type="text" name="reference_no" id="reference_no{{ $cost->id }}"
                                                                   class="form-control" placeholder="Transaction ID/Check No">
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="paid_by{{ $cost->id }}">Paid By</label>
                                                            <input type="text" name="paid_by" id="paid_by{{ $cost->id }}"
                                                                   class="form-control" placeholder="Person name">
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="notes{{ $cost->id }}">Notes</label>
                                                            <textarea name="notes" id="notes{{ $cost->id }}"
                                                                      class="form-control" rows="2"
                                                                      placeholder="Payment notes"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-primary">Add Payment</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @endcan
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info text-center">
                            No costs added yet. Add your first cost using the form.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Payment History -->
            @if($itemShipment->costs->flatMap->payments->count() > 0)
            <div class="card mt-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Payment History</strong>
                    <span class="badge badge-info">{{ $itemShipment->costs->flatMap->payments->count() }} payments</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Cost Type</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Reference</th>
                                    <th>Paid By</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($itemShipment->costs->flatMap->payments->sortByDesc('payment_date') as $payment)
                                <tr>
                                    <td>{{ $payment->itemsShipmentCost->costType->name }}</td>
                                    <td>{{ number_format($payment->amount, 0) }}</td>
                                    <td>{{ ucfirst($payment->payment_method) }}</td>
                                    <td>{{ $payment->reference_no ?? 'N/A' }}</td>
                                    <td>{{ $payment->paid_by ?? 'N/A' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}</td>
                                    <td>
                                        {{-- ✅ Delete payment (uses delete-shipment-cost) --}}
                                        @can('delete-shipment-cost')
                                            <form action="{{ route('itemshipments.payments.destroy', $payment->id) }}"
                                                  method="POST" class="d-inline delete-payment-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endcan
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const costTypeDropdown = document.getElementById("cost_type_id");

    // If add-shipment-cost is not granted, the form isn't in the DOM
    if (!costTypeDropdown) return;

    const agentField       = document.getElementById("agent_field");
    const rateField        = document.getElementById("rate_row");
    const amountField      = document.getElementById("amount_row");

    const amountInput = document.getElementById("amount");
    const rateInput   = document.getElementById("rate_per_kg");
    const weightInput = document.getElementById("total_weight");

    const costTypes = @json($costTypes);

    function isTrue(v) {
        return v === true || v === "true" || v === 1 || v === "1";
    }

    function updateFormUI() {
        const selectedId = costTypeDropdown.value;
        const selectedType = costTypes.find(ct => ct.id == selectedId);

        if (!selectedId || !selectedType) {
            agentField.style.display = "none";
            rateField.style.display  = "none";
            amountField.style.display = "block";
            return;
        }

        const haveAgent = isTrue(selectedType.have_agent);
        agentField.style.display = haveAgent ? "block" : "none";
        if (!haveAgent) {
            const agentSelect = document.getElementById("agent_id");
            if (agentSelect) agentSelect.value = "";
        }

        if (selectedType.type === "per_kg") {
            rateField.style.display   = "block";
            amountField.style.display = "block";
        } else if (selectedType.type === "fixed") {
            rateField.style.display   = "none";
            amountField.style.display = "block";
            rateInput.value = "";
        } else {
            rateField.style.display   = "none";
            amountField.style.display = "block";
        }
    }

    function calculateAmount() {
        const selectedId = costTypeDropdown.value;
        const selectedType = costTypes.find(ct => ct.id == selectedId);
        if (!selectedType || selectedType.type !== "per_kg") return;

        const rate   = parseFloat(rateInput.value)   || 0;
        const weight = parseFloat(weightInput.value) || 0;

        if (rate > 0 && weight > 0) {
            amountInput.value = (rate * weight).toFixed(2);
        }
    }

    costTypeDropdown.addEventListener("change", updateFormUI);
    rateInput.addEventListener("input", calculateAmount);
    weightInput.addEventListener("input", calculateAmount);

    updateFormUI();
});

// Confirm before delete (cost & payment)
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('.delete-cost-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this cost?')) {
                this.submit();
            }
        });
    });

    document.querySelectorAll('.delete-payment-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this payment?')) {
                this.submit();
            }
        });
    });
});
</script>
@endsection