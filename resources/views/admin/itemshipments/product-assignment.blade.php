@extends('admin.layouts.master')

@section('main_content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Customer Assignment</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="{{ route('itemsshipments.index') }}">Shipments</a></li>
                    <li><a href="{{ route('itemsshipments.show', $itemshipment->id) }}">{{ $itemshipment->shipment_name }}</a></li>
                    <li class="active">Customer Assignment</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Customer Assignment - {{ $itemshipment->shipment_name }}</strong>
                <div>
                     <a href="{{ route('itemsshipments.index') }}" 
               class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Back
            </a>
                    <a href="{{ route('customers.index') }}" class="btn btn-info btn-sm">
                        <i class="fa fa-users"></i> Manage Customers
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Shipment Information -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6>Total Items</h6>
                                <h4 class="text-primary">{{ $itemshipment->cargoItems->count() }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6>Assigned Items</h6>
                                <h4 class="text-success">{{ $itemshipment->cargoItems->whereNotNull('customer_id')->filter(fn($i) => optional($i->customer)->customer_code !== 'UNKNOWN')->count() }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6>Unassigned Items</h6>
                                <h4 class="text-warning">{{ $itemshipment->cargoItems->filter(fn($i) => is_null($i->customer_id) || optional($i->customer)->customer_code === 'UNKNOWN')->count() }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6>Shipment Status</h6>
                                <span class="badge badge-{{ $itemshipment->status == 'draft' ? 'secondary' : ($itemshipment->status == 'pending' ? 'warning' : 'success') }}">
                                    {{ ucfirst($itemshipment->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <form action="{{ route('itemshipments.customer.assign', $itemshipment->id) }}" method="POST" id="assignmentForm">
                    @csrf
                    
                    <!-- Bulk Assignment Section -->
                    <div class="row mb-4">
                        
                        <div class="col-md-4">
                          
                                     <a href="{{ route('customers.create') }}" class="btn btn-primary btn-lg btn-block">
                                        <i class="fa fa-plus"></i>  Add New Customer
                                    </a>
                        </div>
                        <div class="col-md-4">
                             <a href="{{ route('itemshipments.customer.autoassign', $itemshipment->id) }}" class="btn btn-warning btn-lg btn-block">
                                        <i class="fa fa-plus"></i>  Auto Assign
                                    </a>
                        
                    </div>
                    </div>

                    @if($products->count() > 0)
                        <div class="form-inline mb-2">
                            <label class="mr-2 font-weight-bold" for="sortBy"><i class="fa fa-sort"></i> Sort by:</label>
                            <select id="sortBy" class="form-control form-control-sm mr-2">
                                <option value="">Default (newest first)</option>
                                <option value="mark">Shipping Mark</option>
                                <option value="goods">Goods Name</option>
                                <option value="customer">Customer Name</option>
                            </select>
                            <select id="sortDir" class="form-control form-control-sm">
                                <option value="asc">A &rarr; Z</option>
                                <option value="desc">Z &rarr; A</option>
                            </select>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead class="thead-dark">
                                    <tr>
                                        <th width="50">
                                            <input type="checkbox" id="selectAll">
                                        </th>
                                        <th>ID</th>
                                        <th>Printed CTN</th>
                                        <th>Goods Name</th>
                                                                                    <th>Shipping Mark</th>

                                        <th>Weight (kg)</th>
                                        <th>Current Customer</th>
                                        <th>Assign Customer</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($products as $product)
                                    <tr data-mark="{{ \Illuminate\Support\Str::lower($product->shipping_mark) }}"
                                        data-goods="{{ \Illuminate\Support\Str::lower($product->goods_name_en) }}"
                                        data-customer="{{ \Illuminate\Support\Str::lower(optional($product->customer)->name) }}"
                                        class="{{ (is_null($product->customer_id) || optional($product->customer)->customer_code === 'UNKNOWN') ? 'table-warning' : '' }}">
                                        <td>
                                            <input type="checkbox" name="product_ids[]" value="{{ $product->id }}" class="product-checkbox">
                                        </td>
                                        <td>{{ $product->id }}</td>
                                        <td>{{ $product->printed_ctn_no }}</td>
                                        <td>{{ $product->goods_name_en }}</td>
                                        <th>{{ $product->shipping_mark }}</th>

                                        <td>{{ $product->weight_kg }}</td>
                                        <td>
                                            @if($product->customer)
                                                <span class="badge badge-success">
                                                    {{ $product->customer->customer_code }} - {{ $product->customer->name }}
                                                </span>
                                            @else
                                                <span class="badge badge-warning">Unassigned</span>
                                            @endif
                                        </td>
                                        <td>
                                            <select name="assignments[{{ $product->id }}][customer_id]" class="form-control form-control-sm customer-select">
                                                <option value="">Select Customer</option>
                                                @foreach($customers as $customer)
                                                    <option value="{{ $customer->id }}" 
                                                        {{ $product->customer_id == $customer->id ? 'selected' : '' }}>
                                                        {{ $customer->customer_code }} - {{ $customer->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $product->status === 'pending' ? 'secondary' : 'info' }}">
                                                {{ ucfirst($product->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-3">
                            {{ $products->links('pagination::bootstrap-4') }}
                        </div>

                        <!-- Submit Button -->
                        <div class="text-center mt-3">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fa fa-save"></i> Save All Assignments
                            </button>
                        </div>
                    @else
                        <div class="alert alert-warning text-center">
                            No products found in this shipment.
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .table td {
        padding: 0.5rem;
        vertical-align: middle;
    }
    .customer-select {
        min-width: 200px;
    }
</style>

<script>
(function () {
    var tbody = document.querySelector('#assignmentForm table tbody');
    if (!tbody) return;
    var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
    rows.forEach(function (r, i) { r.dataset.orig = i; });
    function apply() {
        var key = document.getElementById('sortBy').value;
        var dir = document.getElementById('sortDir').value === 'desc' ? -1 : 1;
        var sorted = rows.slice().sort(function (a, b) {
            if (!key) return a.dataset.orig - b.dataset.orig;
            var x = a.dataset[key] || '', y = b.dataset[key] || '';
            // empty values always last
            if (!x && y) return 1; if (x && !y) return -1;
            return x.localeCompare(y, undefined, {numeric: true}) * dir || a.dataset.orig - b.dataset.orig;
        });
        sorted.forEach(function (r) { tbody.appendChild(r); });
    }
    document.getElementById('sortBy').addEventListener('change', apply);
    document.getElementById('sortDir').addEventListener('change', apply);
})();
</script>
<script>
// Select all checkboxes
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.product-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

// Select all products without customer
function selectAllWithoutCustomer() {
    const checkboxes = document.querySelectorAll('.product-checkbox');
    checkboxes.forEach(checkbox => {
        const row = checkbox.closest('tr');
        if (row.classList.contains('table-warning')) {
            checkbox.checked = true;
        }
    });
}

// Bulk assign function
function bulkAssign() {
    const customerId = document.getElementById('bulkCustomer').value;
    if (!customerId) {
        alert('Please select a customer first.');
        return;
    }

    const selectedProducts = [];
    const checkboxes = document.querySelectorAll('.product-checkbox:checked');
    
    if (checkboxes.length === 0) {
        alert('Please select at least one product.');
        return;
    }

    checkboxes.forEach(checkbox => {
        selectedProducts.push(checkbox.value);
    });

    // Create a form for bulk assignment
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("itemshipments.customer.bulk-assign", $itemshipment->id) }}';
    
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    form.appendChild(csrfToken);

    const customerInput = document.createElement('input');
    customerInput.type = 'hidden';
    customerInput.name = 'customer_id';
    customerInput.value = customerId;
    form.appendChild(customerInput);

    selectedProducts.forEach(productId => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'product_ids[]';
        input.value = productId;
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
}
</script>
@endsection