@extends('admin.layouts.master')

@section('main_content')
<div class="container-fluid">
   <div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        Receive Payment: {{ $customer->name }} ({{ $customer->customer_code }})
        <br>
        <small class="text-muted">Shipment: {{ $itemshipment->shipment_name }}</small>
    </h2>
    <div class="btn-group">
        <a href="{{ route('ledger.customer', $customer->id) }}" 
           class="btn btn-secondary">
            Back
        </a>
        <a href="{{ route('payments.history', ['customer' => $customer, 'itemshipment' => $itemshipment]) }}" 
           class="btn btn-info">
            Payment History
        </a>
    </div>
</div>
    
    <!-- Payment Summary - USE THE VARIABLES FROM CONTROLLER -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>Total Bill</h5>
                    <h3>{{ number_format($totalAmount, 0) }} tk</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Total Paid</h5>
                    <h3>{{ number_format($totalPaid, 0) }} tk</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5>Total Due</h5>
                    <h3>{{ number_format($totalDue, 0) }} tk</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <h5>Unpaid Items</h5>
                    <h3>{{ $items->count() }}</h3>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Payment Form -->
    <div class="card">
        <div class="card-header">
            <h4>Select Items to Pay</h4>
            <p class="mb-0">Select one or more items to pay. You must pay full amount for each selected item.</p>
        </div>
        <div class="card-body">
           
                <form id="paymentForm" action="{{ route('payments.store', ['customer' => $customer, 'itemshipment' => $itemshipment]) }}" method="POST">
    @csrf
                
                <!-- Item Selection Table -->
                @if($items->count() > 0)
                <div class="table-responsive mb-4">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" id="selectAll">
                                </th>
                                <th>#</th>
                                <th>Product Name</th>
                                <th>Tracking No</th>
                                <th>Weight (kg)</th>
                                <th>Fixed Price</th>
                                <th>Price per kg</th>
                                <th>Total (tk)</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $index => $item)
                            <tr>
                                <td>
                                    <input type="checkbox" 
                                           name="selected_items[]" 
                                           value="{{ $item->id }}"
                                           class="item-checkbox"
                                           data-price="{{ $item->total_price }}"
                                           {{ $item->paid_at ? 'disabled' : '' }}>
                                </td>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $item->goods_name_en }}</strong>
                                    @if($item->goods_name_local)
                                    <br><small class="text-muted">{{ $item->goods_name_local }}</small>
                                    @endif
                                </td>
                                <td>{{ $item->tracking_no }}</td>
                                <td>{{ number_format($item->billing_weight, 0) }}</td>
                                <td>
                                    @if($item->price)
                                    {{ number_format($item->price, 0) }}
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->price_per_kg)
                                    {{ number_format($item->price_per_kg, 0) }}
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="item-price">
                                    <strong>{{ number_format($item->total_price, 0) }}</strong>
                                </td>
                                <td>
                                    @if($item->paid_at)
                                        <span class="badge bg-success">Paid</span>
                                        <br><small class="text-muted">{{ $item->paid_at->format('d M, Y') }}</small>
                                    @else
                                        <span class="badge bg-warning">Unpaid</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="7" class="text-end">
                                    <strong>Selected Items Total:</strong>
                                </td>
                                <td id="selectedTotal" class="text-success fw-bold">0.00 tk</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="7" class="text-end">
                                    <strong>Remaining Due:</strong>
                                </td>
                                <td id="remainingDue" class="text-danger fw-bold">
                                    {{ number_format($totalDue, 0) }} tk
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> All items are already paid for this customer.
                </div>
                @endif
                
                <!-- Payment Details -->
                <div class="border p-3 rounded mb-4">
                    <h5>Payment Details</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="payment_method" class="form-label">
                                    Payment Method <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('payment_method') is-invalid @enderror" 
                                        id="payment_method" 
                                        name="payment_method" 
                                        required>
                                    <option value="">Select Method</option>
                                    <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="mobile_banking" {{ old('payment_method') == 'mobile_banking' ? 'selected' : '' }}>Mobile Banking</option>
                                    <option value="cheque" {{ old('payment_method') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                    <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="payment_date" class="form-label">
                                    Payment Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       class="form-control @error('payment_date') is-invalid @enderror" 
                                       id="payment_date" 
                                       name="payment_date" 
                                       value="{{ old('payment_date', date('Y-m-d')) }}"
                                       required>
                                @error('payment_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="reference_no" class="form-label">
                                    Reference No (Optional)
                                </label>
                                <input type="text" 
                                       class="form-control @error('reference_no') is-invalid @enderror" 
                                       id="reference_no" 
                                       name="reference_no" 
                                       value="{{ old('reference_no') }}"
                                       placeholder="e.g., Cheque No, Transaction ID">
                                @error('reference_no')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">
                            Notes (Optional)
                        </label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" 
                                  name="notes" 
                                  rows="2"
                                  placeholder="Any additional notes about this payment...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <!-- Hidden amount field -->
                <input type="hidden" name="amount" id="paymentAmount" value="0">
                
                <div class="d-flex justify-content-between">
                    <a href="{{ route('customers.bill', $customer) }}" 
                       class="btn btn-secondary">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-success" id="submitBtn" disabled>
                        <i class="fa fa-money-bill-wave"></i> 
                        Pay <span id="selectedCount">0</span> Item(s) - 
                        <span id="selectedTotalBtn">0.00</span> tk
                    </button>
                </div>
            </form>
        </div>
    </div>
    
</div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('selectAll');
        const itemCheckboxes = document.querySelectorAll('.item-checkbox:not(:disabled)');
        const selectedTotalElement = document.getElementById('selectedTotal');
        const remainingDueElement = document.getElementById('remainingDue');
        const selectedCountElement = document.getElementById('selectedCount');
        const selectedTotalBtnElement = document.getElementById('selectedTotalBtn');
        const paymentAmountElement = document.getElementById('paymentAmount');
        const submitBtn = document.getElementById('submitBtn');
        
        let totalDue = {{ $totalDue }};
        let selectedTotal = 0;
        let selectedCount = 0;
        
        // Only enable select all if there are items to select
        if (itemCheckboxes.length > 0) {
            selectAllCheckbox.disabled = false;
            
            // Select All functionality
            selectAllCheckbox.addEventListener('change', function() {
                itemCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                    toggleRowHighlight(checkbox);
                });
                calculateSelectedTotal();
            });
            
            // Individual checkbox change
            itemCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    toggleRowHighlight(this);
                    calculateSelectedTotal();
                    
                    // Uncheck "Select All" if not all are checked
                    if (!this.checked && selectAllCheckbox.checked) {
                        selectAllCheckbox.checked = false;
                    }
                    
                    // Check if all are checked
                    const allChecked = Array.from(itemCheckboxes).every(cb => cb.checked);
                    selectAllCheckbox.checked = allChecked;
                });
            });
        } else {
            selectAllCheckbox.disabled = true;
        }
        
        // Toggle row highlight
        function toggleRowHighlight(checkbox) {
            const row = checkbox.closest('tr');
            if (checkbox.checked) {
                row.classList.add('selected');
            } else {
                row.classList.remove('selected');
            }
        }
        
        // Calculate selected total
        function calculateSelectedTotal() {
            selectedTotal = 0;
            selectedCount = 0;
            
            itemCheckboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    const price = parseFloat(checkbox.getAttribute('data-price'));
                    selectedTotal += price;
                    selectedCount++;
                }
            });
            
            // Update display
            selectedTotalElement.textContent = selectedTotal.toFixed(2) + ' tk';
            selectedCountElement.textContent = selectedCount;
            selectedTotalBtnElement.textContent = selectedTotal.toFixed(2);
            paymentAmountElement.value = selectedTotal;
            
            // Update remaining due
            const remainingDue = totalDue - selectedTotal;
            remainingDueElement.textContent = remainingDue.toFixed(2) + ' tk';
            
            // Enable/disable submit button
            submitBtn.disabled = selectedCount === 0;
            
            // Update button text color
            if (selectedCount > 0) {
                submitBtn.classList.remove('btn-secondary');
                submitBtn.classList.add('btn-success');
            } else {
                submitBtn.classList.remove('btn-success');
                submitBtn.classList.add('btn-secondary');
            }
        }
        
        // Form validation
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            if (selectedCount === 0) {
                e.preventDefault();
                alert('Please select at least one item to pay.');
                return false;
            }
            
            // Check if any disabled checkboxes are somehow checked
            const disabledCheckboxes = document.querySelectorAll('.item-checkbox:disabled');
            let hasDisabledChecked = false;
            disabledCheckboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    hasDisabledChecked = true;
                }
            });
            
            if (hasDisabledChecked) {
                e.preventDefault();
                alert('One or more selected items have already been paid. Please refresh the page.');
                return false;
            }
            
            if (selectedTotal <= 0) {
                e.preventDefault();
                alert('Invalid payment amount.');
                return false;
            }
            
            if (selectedTotal > totalDue) {
                e.preventDefault();
                alert('Payment amount cannot exceed total due amount.');
                return false;
            }
            
            // Confirm payment
            if (!confirm(`Confirm payment for ${selectedCount} item(s) totaling ${selectedTotal.toFixed(2)} tk?`)) {
                e.preventDefault();
                return false;
            }
            
            return true;
        });
        
        // Set default date to today
        if (!document.getElementById('payment_date').value) {
            document.getElementById('payment_date').value = new Date().toISOString().split('T')[0];
        }
        
        // Auto-select if only one item and it's not disabled
        if (itemCheckboxes.length === 1) {
            itemCheckboxes[0].checked = true;
            toggleRowHighlight(itemCheckboxes[0]);
            calculateSelectedTotal();
        }
    });
</script>