@extends('admin.layouts.master')

@section('main_content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Manage Returns</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="{{ route('itemsshipments.index') }}">Shipments</a></li>
                    <li><a href="{{ route('itemsshipments.show', $itemshipment->id) }}">{{ $itemshipment->shipment_name }}</a></li>
                    <li class="active">Manage Returns</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="col-lg-12">

 
        <div id="toast" class="toast" style="position: fixed; top: 20px; right: 20px; min-width: 250px; z-index: 9999; display: none;">
            <div class="toast-header bg-success text-white">
                <strong class="mr-auto"><i class="fa fa-check-circle"></i> Success</strong>
                <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast">&times;</button>
            </div>
            <div class="toast-body bg-light">
                <span id="toastMessage"></span>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <strong><i class="fa fa-undo"></i> Manage Returns — {{ $itemshipment->shipment_name }}</strong>
                <a href="{{ route('itemsshipments.show', $itemshipment->id) }}" class="btn btn-light btn-sm">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
            </div>

            <div class="card-body">

                @if($itemshipment->cargoItems->count() > 0)

                    <div class="alert alert-info small">
                        <i class="fa fa-info-circle"></i>
                        <strong>Tip:</strong> Select one or more items and click <strong>"Return Selected"</strong> to move them back to Pending.
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <button type="button" class="btn btn-warning btn-sm" id="returnSelectedBtn" disabled>
                                <i class="fa fa-undo"></i> Return Selected (<span id="selectedCount">0</span>)
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="clearSelectionBtn">
                                Clear
                            </button>
                        </div>
                        <div>
                            <span class="text-muted small">
                                Total: <strong>{{ $itemshipment->cargoItems->count() }}</strong> items
                            </span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="itemsTable" class="table table-striped table-bordered table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th style="width: 40px;">
                                        <input type="checkbox" id="selectAllItems">
                                    </th>
                                    <th>ID</th>
                                    <th>Printed CTN</th>
                                    <th>Written CTN</th>
                                    <th>Tracking No.</th>
                                    <th>Goods Name</th>
                                    <th>Pack No.</th>
                                    <th>Unit Pcs</th>
                                    <th>Weight</th>
                                    <th>Status</th>
                                    <th>Returned</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($itemshipment->cargoItems as $item)
                                    <tr data-item-id="{{ $item->id }}"
                                        class="{{ $item->latestReturn ? 'returned-row' : '' }}"
                                        @if($item->latestReturn)
                                            title="Returned {{ $item->latestReturn->return_date?->format('Y-m-d') }} — {{ $item->latestReturn->note ?? 'No note' }}"
                                        @endif>
                                        <td class="text-center">
                                            <input type="checkbox" class="item-checkbox" value="{{ $item->id }}">
                                        </td>
                                        <td>{{ $item->id }}</td>
                                        <td>{{ $item->printed_ctn_no }}</td>
                                        <td>{{ $item->written_ctn_no }}</td>
                                        <td>{{ $item->tracking_no }}</td>
                                        <td>
                                            {{ $item->goods_name_en }}
                                            @if($item->goods_name_local)
                                                <br><small class="text-muted">({{ $item->goods_name_local }})</small>
                                            @endif
                                        </td>
                                        <td>{{ $item->pack_no }}</td>
                                        <td>{{ $item->unit_pcs }}</td>
                                        <td>{{ number_format((float) $item->weight_kg, 3) }}</td>
                                        <td>
                                            @php
                                                $colors = [
                                                    'pending' => 'secondary',
                                                    'packed' => 'info',
                                                    'shipping_ongoing' => 'primary',
                                                    'arrived_bd' => 'success',
                                                    'waiting_for_customs' => 'danger',
                                                    'ready_to_deliver' => 'dark',
                                                    'delivered' => 'success',
                                                ];
                                                $c = $colors[$item->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge badge-{{ $c }}">
                                                {{ strtoupper(str_replace('_', ' ', $item->status)) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($item->latestReturn)
                                                <span class="badge badge-warning">RETURNED</span>
                                                <br>
                                                <small class="text-muted">
                                                    {{ $item->latestReturn->return_date?->format('Y-m-d') }}
                                                </small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                @else
                    <div class="alert alert-warning mb-0">
                        <i class="fa fa-exclamation-triangle"></i> No items in this shipment.
                    </div>
                @endif

            </div>
        </div>

    </div>
</div>

{{-- ============ Return Modal (Custom — no Bootstrap JS needed) ============ --}}
<div id="returnModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:1050; overflow-y:auto; padding:40px 16px;">
    <div style="background:#fff; border-radius:6px; width:100%; max-width:800px; margin:0 auto; box-shadow:0 10px 30px rgba(0,0,0,.25);">
        <div style="padding:12px 16px; background:#ffc107; display:flex; justify-content:space-between; align-items:center;">
            <strong><i class="fa fa-undo"></i> Return Items</strong>
            <button type="button" id="closeReturnModal" style="background:transparent;border:0;font-size:24px;cursor:pointer;line-height:1;">&times;</button>
        </div>
        <div style="padding:16px;">
            <p class="mb-2">
                <strong id="returnCount">0</strong> item(s) will be returned to
                <span class="badge badge-secondary">PENDING</span>.
            </p>

            <div style="max-height: 300px; overflow-y: auto;" class="mb-3">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>ID</th>
                            <th>Tracking</th>
                            <th>Goods</th>
                        </tr>
                    </thead>
                    <tbody id="returnItemsList"></tbody>
                </table>
            </div>

            <div class="form-group">
                <label class="font-weight-bold">Return Date <span class="text-danger">*</span></label>
                <input type="date" id="returnDate" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>

            <div class="form-group mb-0">
                <label class="font-weight-bold">Note</label>
                <textarea id="returnNote" class="form-control" rows="3" placeholder="Reason for return..."></textarea>
            </div>
        </div>
        <div style="padding:12px 16px; text-align:right; border-top:1px solid #e9ecef;">
            <button type="button" class="btn btn-secondary" id="cancelReturnBtn">Cancel</button>
            <button type="button" class="btn btn-warning" id="confirmReturnBtn">
                <i class="fa fa-check"></i> Confirm Return
            </button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table td, .table th {
        padding: 0.5rem !important;
        vertical-align: middle !important;
        text-align: center !important;
    }
    #itemsTable tbody tr { cursor: pointer; }
    #itemsTable tbody tr:hover { background-color: #f8f9fa !important; }

    #itemsTable tbody tr.row-selected,
    #itemsTable tbody tr.row-selected:hover {
        background-color: #cfe2ff !important;
    }

    #itemsTable tbody tr.returned-row {
        background-color: #fff3cd !important;
        border-left: 4px solid #ffc107 !important;
    }
    #itemsTable tbody tr.returned-row:hover {
        background-color: #ffe8a1 !important;
    }

    .toast {
        border-radius: 5px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
</style>
@endpush

@push('scripts')
<script>
// ============================================================
// DEBUG LOGGER — runs immediately, before DOM ready
// ============================================================
(function() {
    var logEl = document.getElementById('debugLog');
    if (!logEl) {
        // DOM not ready yet, wait
        document.addEventListener('DOMContentLoaded', function() {
            window.dbg = function(msg, data) {
                var el = document.getElementById('debugLog');
                if (!el) return;
                var line = document.createElement('div');
                line.textContent = '[' + new Date().toLocaleTimeString() + '] ' + msg +
                                   (data !== undefined ? ' :: ' + JSON.stringify(data) : '');
                el.appendChild(line);
                el.scrollTop = el.scrollHeight;
            };
            runDebugChecks();
        });
    } else {
        window.dbg = function(msg, data) {
            var el = document.getElementById('debugLog');
            if (!el) return;
            var line = document.createElement('div');
            line.textContent = '[' + new Date().toLocaleTimeString() + '] ' + msg +
                               (data !== undefined ? ' :: ' + JSON.stringify(data) : '');
            el.appendChild(line);
            el.scrollTop = el.scrollHeight;
            console.log('[DBG]', msg, data);
        };
        runDebugChecks();
    }

    function runDebugChecks() {
        window.dbg('PAGE LOADED');

        // Check jQuery
        if (typeof window.jQuery === 'undefined') {
            window.dbg('ERROR: jQuery NOT loaded!');
            return;
        }
        window.dbg('jQuery version: ' + window.jQuery.fn.jquery);

        // Check Bootstrap 4 modal API
        window.dbg('$.fn.modal type: ' + typeof window.jQuery.fn.modal);

        // Check DataTables
        window.dbg('$.fn.DataTable type: ' + typeof window.jQuery.fn.DataTable);
        window.dbg('$.fn.dataTable type: ' + typeof window.jQuery.fn.dataTable);

        // Check the table
        window.dbg('#itemsTable found: ' + (document.getElementById('itemsTable') ? 'yes' : 'no'));
        window.dbg('#itemsTable rows: ' + document.querySelectorAll('#itemsTable tbody tr').length);

        // Check critical elements
        ['returnModal', 'returnSelectedBtn', 'confirmReturnBtn', 'selectAllItems'].forEach(function(id) {
            window.dbg('#' + id + ': ' + (document.getElementById(id) ? 'found' : 'MISSING'));
        });
    }
})();
</script>

<script>
// ============================================================
// MAIN SCRIPT
// ============================================================
jQuery(function($) {
    window.dbg && window.dbg('DOM READY — main script starting');

    // ============================================
    // Init DataTable (only if available)
    // ============================================
    var hasDataTable = (typeof $.fn.DataTable === 'function') ||
                       (typeof $.fn.dataTable === 'function');

    if (hasDataTable) {
        try {
            $('#itemsTable').DataTable({
                "pageLength": 50,
                "lengthMenu": [25, 50, 100, 250],
                "order": [[1, 'desc']],
                "columnDefs": [{ "orderable": false, "targets": [0] }]
            });
            window.dbg && window.dbg('DataTable initialized OK');
        } catch (e) {
            window.dbg && window.dbg('DataTable init FAILED: ' + e.message);
        }
    } else {
        window.dbg && window.dbg('DataTables NOT available — table will render as plain HTML');
    }

    // ============================================
    // Selection state
    // ============================================
    var selectedIds = new Set();

    function updateSelected() {
        $('#selectedCount').text(selectedIds.size);
        $('#returnSelectedBtn').prop('disabled', selectedIds.size === 0);

        $('#itemsTable tbody tr').each(function() {
            var id = String($(this).data('item-id'));
            var cb = $(this).find('.item-checkbox');
            if (selectedIds.has(id)) {
                cb.prop('checked', true);
                $(this).addClass('row-selected');
            } else {
                cb.prop('checked', false);
                $(this).removeClass('row-selected');
            }
        });

        window.dbg && window.dbg('updateSelected', { count: selectedIds.size });
    }

    // ============================================
    // Checkbox click
    // ============================================
    $(document).on('change', '.item-checkbox', function(e) {
        e.stopPropagation();
        var id = String($(this).val());
        if ($(this).is(':checked')) {
            selectedIds.add(id);
        } else {
            selectedIds.delete(id);
        }
        window.dbg && window.dbg('checkbox change', { id: id, checked: $(this).is(':checked') });
        updateSelected();
    });

    // ============================================
    // Row click → toggle
    // ============================================
    $(document).on('click', '#itemsTable tbody tr', function(e) {
        if ($(e.target).closest('a, button, form, input, select, label').length) return;

        var id = String($(this).data('item-id'));
        if (selectedIds.has(id)) selectedIds.delete(id);
        else selectedIds.add(id);

        window.dbg && window.dbg('row click', { id: id, nowIn: selectedIds.has(id) });
        updateSelected();
    });

    // ============================================
    // Select all
    // ============================================
    $(document).on('change', '#selectAllItems', function() {
        var checked = $(this).is(':checked');
        $('#itemsTable tbody tr').each(function() {
            var id = String($(this).data('item-id'));
            if (checked) selectedIds.add(id);
            else selectedIds.delete(id);
        });
        window.dbg && window.dbg('select-all', { checked: checked });
        updateSelected();
    });

    // ============================================
    // Clear selection
    // ============================================
    $('#clearSelectionBtn').on('click', function() {
        selectedIds.clear();
        $('#selectAllItems').prop('checked', false);
        window.dbg && window.dbg('clear selection');
        updateSelected();
    });

    // ============================================
    // Custom modal open/close
    // ============================================
    function openModal() {
        document.getElementById('returnModal').style.display = 'block';
        document.body.style.overflow = 'hidden';
        window.dbg && window.dbg('modal OPENED');
    }

    function closeModal() {
        document.getElementById('returnModal').style.display = 'none';
        document.body.style.overflow = '';
        window.dbg && window.dbg('modal CLOSED');
    }

    $('#closeReturnModal, #cancelReturnBtn').on('click', closeModal);

    // Click backdrop → close
    $('#returnModal').on('click', function(e) {
        if (e.target === this) closeModal();
    });

    // Esc key → close
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('#returnModal').is(':visible')) {
            closeModal();
        }
    });

    // ============================================
    // Return Selected button
    // ============================================
    $(document).on('click', '#returnSelectedBtn', function() {
        window.dbg && window.dbg('#returnSelectedBtn clicked', { size: selectedIds.size });

        if (selectedIds.size === 0) {
            window.dbg && window.dbg('no items selected — ignoring');
            return;
        }

        var html = '';
        $('#itemsTable tbody tr').each(function() {
            var id = String($(this).data('item-id'));
            if (selectedIds.has(id)) {
                var tds = $(this).find('td');
                html += '<tr>' +
                            '<td>' + tds.eq(1).text() + '</td>' +
                            '<td>' + tds.eq(4).text() + '</td>' +
                            '<td>' + tds.eq(5).text() + '</td>' +
                        '</tr>';
            }
        });

        $('#returnItemsList').html(html);
        $('#returnCount').text(selectedIds.size);
        $('#returnNote').val('');
        $('#returnDate').val(new Date().toISOString().substring(0, 10));

        openModal();
    });

    // ============================================
    // Confirm Return button
    // ============================================
    $(document).on('click', '#confirmReturnBtn', function() {
        var ids = Array.from(selectedIds).join(',');
        var returnDate = $('#returnDate').val();
        var note = $('#returnNote').val();

        window.dbg && window.dbg('Confirm Return clicked', { ids: ids, returnDate: returnDate });

        if (!returnDate) {
            alert('Return date is required.');
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Returning...');

        var url = '{{ route("itemsshipments.return-items", $itemshipment->id) }}';
        window.dbg && window.dbg('POST → ' + url);

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                item_ids: ids,
                return_date: returnDate,
                note: note
            },
            success: function(res) {
                window.dbg && window.dbg('AJAX success', res);
                if (res && res.success) {
                    closeModal();
                    showToast(res.message);
                    setTimeout(function() { location.reload(); }, 800);
                } else {
                    showToast((res && res.message) || 'Failed to return items.');
                    $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Confirm Return');
                }
            },
            error: function(xhr) {
                window.dbg && window.dbg('AJAX error', { status: xhr.status, text: xhr.responseText });
                var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Failed to return items.';
                showToast(msg);
                $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Confirm Return');
            }
        });
    });

    // ============================================
    // Toast
    // ============================================
    function showToast(msg) {
        $('#toastMessage').text(msg);
        $('#toast').fadeIn();
        setTimeout(function() { $('#toast').fadeOut(); }, 3000);
    }

    $(document).on('click', '#toast .close', function() {
        $('#toast').fadeOut();
    });

    updateSelected();
    window.dbg && window.dbg('Main script initialized');
});
</script>
@endpush