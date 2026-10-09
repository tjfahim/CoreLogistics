@extends('admin.layouts.master')

@section('main_content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Warehouse Manage</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li class="active">Warehouse</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    @if(session('success'))
        <div class="col-sm-12">
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="col-sm-12">
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        </div>
    @endif

    <div id="toast" class="toast" style="position: fixed; top: 20px; right: 20px; min-width: 250px; z-index: 9999; display: none;">
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
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong class="card-title">
                        Item List
                        <span class="badge badge-info ml-2" id="statusBadge">
                            {{ strtoupper(str_replace('_', ' ', $currentPageStatus ?? 'all')) }}
                        </span>
                    </strong>
                    <div>
                        {{-- ✅ Imported Files — view-warehouse --}}
                        @can('view-warehouse')
                            <a href="{{ route('cargo-items.files.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fa fa-folder"></i> Imported Files
                            </a>
                        @endcan

                        {{-- ✅ Add Item — add-warehouse-item --}}
                        @can('add-warehouse-item')
                            <a href="{{ route('cargo-items.create') }}" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus"></i> Add Item
                            </a>
                        @endcan
                    </div>
                </div>

                <div class="btn-group btn-group-sm flex-wrap" role="group" id="filterButtons">
                    <button type="button" class="btn btn-secondary filter-btn" data-status="pending">Pending</button>
                    <button type="button" class="btn btn-info filter-btn" data-status="packed">Packed</button>
                    <button type="button" class="btn btn-primary filter-btn" data-status="shipping_ongoing">Shipping Ongoing</button>
                    <button type="button" class="btn btn-danger filter-btn" data-status="waiting_for_customs">Waiting for Customs</button>
                    <button type="button" class="btn btn-success filter-btn" data-status="arrived_bd">Arrived BD</button>
                    <button type="button" class="btn btn-dark filter-btn" data-status="ready_to_deliver">Ready to Deliver</button>
                    <button type="button" class="btn btn-success filter-btn" data-status="delivered">Delivered</button>
                    <button type="button" class="btn btn-warning filter-btn active" data-status="all">All</button>
                </div>

                <div class="row mt-2">
                    <div class="col-md-6">
                        <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Search tracking, goods, CTN...">
                    </div>
                    <div class="col-md-6 text-right">
                        <select id="perPageSelect" class="form-control form-control-sm d-inline-block" style="width: auto;">
                            <option value="25">25 per page</option>
                            <option value="50" selected>50 per page</option>
                            <option value="100">100 per page</option>
                            <option value="250">250 per page</option>
                        </select>
                    </div>
                </div>

                <div class="mt-2">
                    <small class="text-muted">
                        <i class="fa fa-info-circle"></i>
                        <strong>Tip:</strong> Click any <em>Pending</em> or <em>Packed</em> row to toggle its status.
                    </small>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table id="cargoItemsTable" class="table table-striped table-bordered table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>ID</th>
                                <th>Date</th>
                                <th>Printed CTN NO.</th>
                                <th>Written CTN NO.</th>
                                <th>Trackings</th>
                                <th>Goods Name</th>
                                <th>Pack NO.</th>
                                <th>Unit Pcs</th>
                                <th>Weight (kg)</th>
                                <th>L, W, H</th>
                                <th>CBM</th>
                                <th>Shipping Mark</th>
                                <th>Remark</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <tr><td colspan="15" class="text-center">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-2">
                    <div id="paginationInfo" class="text-muted small"></div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0" id="pagination"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Permissions injected from Blade → used by JS to show/hide action buttons in rows
window.CARGO_PERMS = {
    canEdit:   @json(auth()->user()->hasPermission('edit-warehouse-item')),
    canDelete: @json(auth()->user()->hasPermission('delete-warehouse-item')),
    canToggle: @json(auth()->user()->hasPermission('edit-warehouse-item')) // toggle = edit
};

$(document).ready(function () {
    let currentStatus = "{{ $currentPageStatus ?? 'all' }}";
    let currentPage = 1;
    let currentSearch = '';
    let isProcessing = false;

    $('#filterButtons .filter-btn').removeClass('active');
    $('#filterButtons .filter-btn[data-status="' + currentStatus + '"]').addClass('active');

    loadItems();

    $(document).on('click', '.filter-btn', function () {
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');

        currentStatus = $(this).data('status');
        currentPage = 1;
        $('#statusBadge').text(currentStatus.replace(/_/g, ' ').toUpperCase());
        loadItems();
    });

    let searchTimer;
    $('#searchInput').on('input', function () {
        clearTimeout(searchTimer);
        const val = $(this).val();
        searchTimer = setTimeout(function () {
            currentSearch = val;
            currentPage = 1;
            loadItems();
        }, 350);
    });

    $('#perPageSelect').on('change', function () {
        currentPage = 1;
        loadItems();
    });

    function loadItems() {
        const perPage = $('#perPageSelect').val();

        $('#tableBody').html('<tr><td colspan="15" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>');

        $.ajax({
            url: '{{ route("cargo-items.ajax-items") }}',
            type: 'GET',
            data: {
                status: currentStatus,
                page: currentPage,
                per_page: perPage,
                search: currentSearch
            },
            success: function (res) {
                renderRows(res.data);
                renderPagination(res.meta);
            },
            error: function () {
                $('#tableBody').html('<tr><td colspan="15" class="text-center text-danger">Failed to load items.</td></tr>');
            }
        });
    }

    function renderRows(items) {
        if (!items || items.length === 0) {
            $('#tableBody').html('<tr><td colspan="15" class="text-center text-muted">No items found.</td></tr>');
            return;
        }

        const statusColors = {
            'pending': 'secondary',
            'packed': 'info',
            'ready_to_ship': 'primary',
            'shipping_ongoing': 'primary',
            'arrived_bd': 'success',
            'waiting_for_customs': 'danger',
            'ready_to_deliver': 'dark',
            'delivered': 'success'
        };

        let html = '';
        const P = window.CARGO_PERMS;

        items.forEach(function (item) {
            const color = statusColors[item.status] || 'secondary';
            const rowClass = item.status === 'packed' ? 'table-primary' : '';
            const returnedClass = item.is_returned ? 'returned-row' : '';
            const dateStr = item.date ? item.date.substring(5, 10) : '-';
            const goodsLocal = item.goods_name_local ? '<br><small class="text-muted">(' + item.goods_name_local + ')</small>' : '';

            let titleAttr = '';
            if (item.is_returned) {
                const note = (item.return_note || 'No note').replace(/"/g, '&quot;');
                const rdate = item.return_date || '';
                titleAttr = 'title="Returned ' + rdate + ' — ' + note + '"';
            }

            // Build action buttons based on permissions
            let actions = '';
            if (P.canEdit) {
                actions += '<a href="/cargo-items/' + item.id + '/edit" class="btn btn-warning btn-sm" title="Edit"><i class="fa fa-edit"></i></a> ';
            }
            if (P.canDelete) {
                actions += '<button class="btn btn-danger btn-sm delete-btn" data-id="' + item.id + '" title="Delete"><i class="fa fa-trash"></i></button>';
            }
            if (!P.canEdit && !P.canDelete) {
                actions = '<span class="text-muted">—</span>';
            }

            html += '<tr data-item-id="' + item.id + '" ' +
                    'class="selectable-row ' + rowClass + ' ' + returnedClass + '" ' +
                    'data-status="' + item.status + '" ' + titleAttr + '>' +
                    '<td>' + item.id + '</td>' +
                    '<td>' + dateStr + '</td>' +
                    '<td>' + (item.printed_ctn_no ?? '') + '</td>' +
                    '<td>' + (item.written_ctn_no ?? '') + '</td>' +
                    '<td>' + (item.tracking_no ?? '') + '</td>' +
                    '<td>' + (item.goods_name_en ?? '') + goodsLocal + '</td>' +
                    '<td>' + (item.pack_no ?? '') + '</td>' +
                    '<td>' + (item.unit_pcs ?? '') + '</td>' +
                    '<td>' + (item.weight_kg ?? '0.000') + '</td>' +
                    '<td>' + Math.round(item.length_cm) + ', ' + Math.round(item.width_cm) + ', ' + Math.round(item.height_cm) + '</td>' +
                    '<td>' + parseFloat(item.cbm || 0).toFixed(6) + '</td>' +
                    '<td>' + (item.shipping_mark ?? '-') + '</td>' +
                    '<td>' + (item.remark ?? '-') + '</td>' +
                    '<td><span class="badge badge-' + color + ' status-badge" style="padding: 0.5em 0.75em; font-size: 0.85em;">' +
                        item.status.replace(/_/g, ' ').toUpperCase() +
                    '</span>' +
                    (item.is_returned ? ' <span class="badge badge-warning">RETURNED</span>' : '') +
                    '</td>' +
                    '<td>' + actions + '</td>' +
                    '</tr>';
        });

        $('#tableBody').html(html);
    }

    function renderPagination(meta) {
        if (!meta || meta.total === 0) {
            $('#paginationInfo').text('');
            $('#pagination').html('');
            return;
        }

        $('#paginationInfo').text('Showing ' + meta.from + ' to ' + meta.to + ' of ' + meta.total + ' items');

        let html = '';
        const cur = meta.current_page;
        const last = meta.last_page;

        html += '<li class="page-item ' + (cur <= 1 ? 'disabled' : '') + '">' +
                '<a class="page-link" href="#" data-page="' + (cur - 1) + '">Prev</a></li>';

        let start = Math.max(1, cur - 2);
        let end = Math.min(last, cur + 2);

        if (start > 1) {
            html += '<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>';
            if (start > 2) html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }

        for (let i = start; i <= end; i++) {
            html += '<li class="page-item ' + (i === cur ? 'active' : '') + '">' +
                    '<a class="page-link" href="#" data-page="' + i + '">' + i + '</a></li>';
        }

        if (end < last) {
            if (end < last - 1) html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            html += '<li class="page-item"><a class="page-link" href="#" data-page="' + last + '">' + last + '</a></li>';
        }

        html += '<li class="page-item ' + (cur >= last ? 'disabled' : '') + '">' +
                '<a class="page-link" href="#" data-page="' + (cur + 1) + '">Next</a></li>';

        $('#pagination').html(html);
    }

    $(document).on('click', '#pagination .page-link', function (e) {
        e.preventDefault();
        const page = parseInt($(this).data('page'));
        if (page && page !== currentPage) {
            currentPage = page;
            loadItems();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });

    // Row click → toggle Pending ↔ Packed (requires edit-warehouse-item)
    $(document).on('click', '#cargoItemsTable tbody tr.selectable-row', function (e) {
        if ($(e.target).closest('a, button, form, input, select').length) return;
        if (!window.CARGO_PERMS.canToggle) return;   // ✅ permission check

        const row = $(this);
        const status = row.data('status');

        if (!['pending', 'packed'].includes(status)) return;
        if (isProcessing) return;

        const itemId = row.data('item-id');
        toggleItem(itemId, row, status);
    });

    function toggleItem(itemId, row, currentStatus) {
        isProcessing = true;

        getOrCreateDraftShipment().then(function (shipment) {
            $.ajax({
                url: '{{ route("itemsshipments.toggle-item") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    shipment_id: shipment.id,
                    item_id: itemId
                },
                success: function (response) {
                    if (response.success) {
                        if (response.action === 'added') {
                            row.addClass('table-primary').data('status', 'packed');
                            row.find('.status-badge').removeClass().addClass('badge badge-info status-badge').text('PACKED');
                            showToast('Item added to shipment (Packed)');
                        } else {
                            row.removeClass('table-primary').data('status', 'pending');
                            row.find('.status-badge').removeClass().addClass('badge badge-secondary status-badge').text('PENDING');
                            showToast('Item removed from shipment (Pending)');
                        }
                    }
                    isProcessing = false;
                },
                error: function (xhr) {
                    if (xhr.status === 403) {
                        showToast('You do not have permission to toggle status.');
                    } else {
                        showToast('Error updating shipment');
                    }
                    isProcessing = false;
                }
            });
        }).catch(function () {
            showToast('Error creating shipment');
            isProcessing = false;
        });
    }

    function getOrCreateDraftShipment() {
        return new Promise(function (resolve, reject) {
            $.ajax({
                url: '{{ route("itemsshipments.get-or-create-draft") }}',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function (res) {
                    if (res.success) resolve(res.shipment);
                    else reject();
                },
                error: reject
            });
        });
    }

    // Delete (requires delete-warehouse-item)
    $(document).on('click', '.delete-btn', function () {
        if (!window.CARGO_PERMS.canDelete) return;
        if (!confirm('Are you sure you want to delete this cargo item?')) return;

        const id = $(this).data('id');

        $.ajax({
            url: '/cargo-items/' + id,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                _method: 'DELETE'
            },
            success: function () {
                showToast('Item deleted');
                loadItems();
            },
            error: function (xhr) {
                if (xhr.status === 403) {
                    showToast('You do not have permission to delete items.');
                } else {
                    showToast('Failed to delete item');
                }
            }
        });
    });

    function showToast(message) {
        $('#toastMessage').text(message);
        $('#toast').fadeIn();
        setTimeout(function () { $('#toast').fadeOut(); }, 3000);
    }

    $(document).on('click', '#toast .close', function () {
        $('#toast').fadeOut();
    });
});
</script>

<style>
    .table td, .table th {
        padding: 0.5rem !important;
        vertical-align: middle !important;
        text-align: center !important;
    }
    #cargoItemsTable tbody tr.returned-row {
        background-color: #fff3cd !important;
        border-left: 4px solid #ffc107 !important;
    }
    #cargoItemsTable tbody tr.returned-row:hover {
        background-color: #ffe8a1 !important;
    }
    .selectable-row { cursor: pointer; }
    .filter-btn.active { box-shadow: 0 0 0 2px #007bff inset; font-weight: bold; }
    .table-primary { background-color: #007bff !important; color: white; }
    .selectable-row:hover { background-color: #f8f9fa !important; }
    .table-primary:hover { background-color: #0069d9 !important; }
    .table td:last-child { cursor: default; }
    .toast { border-radius: 5px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
    .table-primary .status-badge { color: #000; }
</style>
@endsection