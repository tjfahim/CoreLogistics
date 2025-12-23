@extends('admin.layouts.master')

@section('main_content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-ti                      <h1>Items Manage</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li class="active">Items</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    @if(session('success'))
        <div class="col-sm-12">
            <div class="alert alert-success">{{ session('success') }}</div>
        </div>
    @endif

    <!-- Success Message Toast -->
    <div id="successToast" class="toast" style="position: fixed; top: 20px; right: 20px; min-width: 250px; z-index: 9999; display: none;">
        <div class="toast-header bg-success text-white">
            <strong class="mr-auto"><i class="fa fa-check-circle"></i> Success</strong>
            <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast">&times;</button>
        </div>
        <div class="toast-body bg-light">
            <span id="toastMessage"></span>
        </div>
    </div>

    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong class="card-title">Item List</strong>
                <div>
                    <!-- <button id="selectAllBtn" class="btn btn-info btn-sm mr-2">
                        <i class="fa fa-check-square"></i> Select All
                    </button> -->
                    <a href="{{ route('cargo-items.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Add Item
                    </a>
                </div>
            </div>

            <div class="card-body">
                <table id="cargoItemsTable" class="table table-striped table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Date 2025</th>
                            <th>Printed CTN NO.</th>
                            <th>Written CTN NO.</th>
                            <th>Trackings</th>
                            <th>Goods Name</th>
                            <th>Pack NO.</th>
                            <th>Unit Pcs</th>
                            <th>Weight (kg)</th>
                            <th>Length, Width, Height</th>
                            <th>CBM</th>
                            <th>Shipping Mark</th>
                            <th>Remark</th>
                            <th>Copy or NO</th>
                            <th>Ware House</th>
                            <th>ActionEdit</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($items as $item)
                        <tr data-item-id="{{ $item->id }}" 
                            class="selectable-row {{ $item->status == 'packed' ? 'table-primary' : '' }}"
                            data-status="{{ $item->status }}">
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->date ? $item->date->format('m-d') : '-' }}</td>
                            <td>{{ $item->printed_ctn_no }}</td>
                            <td>{{ $item->written_ctn_no }}</td>
                            <td>{{ $item->tracking_no }}</td>
                            <td>{{ $item->goods_name_en }} {{ $item->goods_name_local ? '(' . $item->goods_name_local . ')' : '' }}</td>
                            <td>{{ $item->pack_no }}</td>
                            <td>{{ $item->unit_pcs }}</td>
                            <td>{{ $item->weight_kg ?? '0.000' }}</td>
                            <td>{{ number_format($item->length_cm, 0) }}, {{ number_format($item->width_cm, 0) }}, {{ number_format($item->height_cm, 0) }}</td>
                            <td>{{ number_format($item->cbm ?? 0, 6) }}</td>
                            <td>{{ $item->shipping_mark ?? '-' }}</td>
                            <td>{{ $item->remark ?? '-' }}</td>
                            <td>{{ $item->is_copy ? 'Yes' : 'No' }}</td>
                            <td>{{ $item->warehouse ?? '-' }}</td>
                            <td>
                                <a href="{{ route('cargo-items.edit', $item->id) }}" class="btn btn-warning btn-sm">
                                    <i class="fa fa-edit"></i> 
                                </a>
                                <form action="{{ route('cargo-items.destroy', $item->id) }}"
                                      method="POST"
                                      class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm"
                                            onclick="return confirm('Are you sure you want to delete this cargo item?')">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let isProcessing = false; 
    const table = $('#cargoItemsTable').DataTable({
        "pageLength": 50, 
        "lengthMenu": [ 50, 100, 200, 500, 1000, 1500, 2500 ],
        "columnDefs": [
            { "orderable": false, "targets": [0, 15] }
        ]
    });
 
    // Show success toast message
    function showSuccessMessage(message) {
        $('#toastMessage').text(message);
        $('#successToast').fadeIn();
        setTimeout(function() {
            $('#successToast').fadeOut();
        }, 3000);
    }

    // Handle row click for selection (excluding action column)
    $('#cargoItemsTable tbody').on('click', 'tr.selectable-row', function(e) {
        // Don't trigger selection if clicking on action buttons
        if ($(e.target).closest('a, button').length) {
            return;
        }

        const itemId = $(this).data('item-id');
        const row = $(this);
        const status = row.data('status');

        // Prevent multiple API calls while processing
        if (isProcessing) {
            showSuccessMessage('Please wait, processing previous selection...');
            return;
        }
        
        // Toggle item - if packed, remove it; if pending, add it
        toggleItemInShipment(itemId, row, status);
    });

    // Toggle item in shipment (add if not exists, remove if exists)
    function toggleItemInShipment(itemId, row, currentStatus) {
        isProcessing = true;
        
        // First, ensure we have a draft shipment
        getOrCreateDraftShipment().then(function(shipment) {
            // Then toggle the item
            $.ajax({
                url: '{{ route("itemsshipments.toggle-item") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    shipment_id: shipment.id,
                    item_id: itemId
                },
                success: function(response) {
                    if (response.success) {
                        // Update row based on new status
                        if (response.action === 'added') {
                            row.addClass('table-primary');
                            row.data('status', 'packed');
                            showSuccessMessage('Item added to shipment');
                        } else {
                            row.removeClass('table-primary');
                            row.data('status', 'pending');
                            showSuccessMessage('Item removed from shipment');
                        }
                    }
                    isProcessing = false;
                },
                error: function(xhr) {
                    console.error('Error toggling item in shipment:', xhr.responseText);
                    showSuccessMessage('Error updating shipment');
                    isProcessing = false;
                }
            });
        }).catch(function(error) {
            showSuccessMessage('Error creating shipment');
            isProcessing = false;
        });
    }

    // Select All functionality (only select pending items)
    $('#selectAllBtn').click(function() {
        $('#cargoItemsTable tbody tr.selectable-row').each(function() {
            const itemId = $(this).data('item-id');
            const status = $(this).data('status');
            const row = $(this);
            
            if (status !== 'packed' && !isProcessing) {
                toggleItemInShipment(itemId, row, status);
            }
        });
    });

    // Get or create draft shipment with Promise
    function getOrCreateDraftShipment() {
        return new Promise(function(resolve, reject) {
            $.ajax({
                url: '{{ route("itemsshipments.get-or-create-draft") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        resolve(response.shipment);
                    } else {
                        reject('Failed to create draft shipment');
                    }
                },
                error: function(xhr) {
                    console.error('Error creating draft shipment:', xhr.responseText);
                    reject('Error creating draft shipment');
                }
            });
        });
    }

    // Close toast when clicking X
    $('.close').click(function() {
        $('#successToast').fadeOut();
    });
});
</script>

<style>
    .table td, .table th {
        padding: 0.5rem !important;
        vertical-align: middle !important;
        text-align: center !important;
        cursor: pointer;
    }
    
#successToast').fadeOut();
    });
});
</script>

<style>
    .table td, .table th {
        padding: 0.5rem !important;
        vertical-align: middle !important;
        text-align: center !important;
        cursor: pointer;
    }
    
    /* Blue for packed items (persistent) */
    .table-primary {
        background-color: #007bff !important;
        color: white;
    }
    
    .selectable-row:hover {
        background-color: #f8f9fa !important;
    }
    
    .table-primary:hover {
        background-color: #0069d9 !important;
    }
    
    /* Make action column not selectable */
    .table td:last-child {
        cursor: default;
    }

    /* Toast styling */
    .toast {
        border-radius: 5px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
</style>
@endsection