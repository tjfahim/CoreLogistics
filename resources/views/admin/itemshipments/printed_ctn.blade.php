@extends('admin.layouts.master')

@section('main_content')

<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Update Printed CTN Numbers</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="{{ route('itemsshipments.index') }}">Shipments</a></li>
                    <li>
                        <a href="{{ route('itemsshipments.show', $itemshipment->id) }}">
                            {{ $itemshipment->shipment_name }}
                        </a>
                    </li>
                    <li class="active">Update CTN</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <span class="badge badge-pill badge-success">Success</span> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <span class="badge badge-pill badge-danger">Error</span> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <strong>Printed CTN Numbers - {{ $itemshipment->shipment_name }}</strong>
            <div>
                <a href="{{ route('itemsshipments.index') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Back
                </a>

                {{-- ✅ Print button — guarded by print-ctn --}}
                @can('print-ctn')
                    <button type="button" class="btn btn-info" onclick="printCTNTable()">
                        <i class="fa fa-print"></i> Print
                    </button>
                @endcan
            </div>
        </div>

        <div class="card-body">

            {{-- ✅ Guard entire page with view-ctn --}}
            @cannot('view-ctn')
                <div class="alert alert-danger">
                    You do not have permission to view CTN numbers.
                </div>
            @endcannot

            @can('view-ctn')

                @can('edit-ctn')
                    <form action="{{ route('itemshipments.auto-assign-ctn', $itemshipment->id) }}" method="POST"
                          class="form-inline mb-3 p-2 border rounded bg-light"
                          onsubmit="return confirm('সব CTN এই ফরম্যাটে অটো অ্যাসাইন ও সেভ হবে। ঠিক আছে?')">
                        @csrf
                        <label class="mr-2 font-weight-bold">CTN Format:</label>
                        <input type="text" name="ctn_format" class="form-control mr-2"
                               value="{{ old('ctn_format', $ctnSuggestion) }}"
                               placeholder="e.g. AB-100" required>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-magic"></i> Auto Assign &amp; Save All
                        </button>
                        <small class="text-muted ml-3">
                            ফরম্যাটের শেষে নাম্বার দিন (AB-100 → AB-100, AB-101...). একই গ্রুপের আইটেম একই CTN পাবে।
                        </small>
                    </form>
                @endcan

                {{-- Form is only rendered when user can edit-ctn --}}
                @can('edit-ctn')
                    <form action="{{ route('itemshipments.update-ctn', $itemshipment->id) }}" method="POST">
                        @csrf
                @endcan

                <div class="d-flex justify-content-between mb-3">
                    <h5>Update Printed CTN Numbers</h5>

                    {{-- ✅ Save button — only for edit-ctn --}}
                    @can('edit-ctn')
                        <button class="btn btn-success">
                            <i class="fa fa-save"></i> Save All
                        </button>
                    @endcan
                </div>

                @php
                    // Solid colors for groups
                    $colors = [
                        '#ff6b6b', '#1dd1a1', '#54a0ff', '#feca57', '#5f27cd',
                        '#10ac84', '#ff9f43', '#2e86de', '#ee5253', '#222f3e',
                        '#0abde3', '#576574', '#8395a7', '#ff6348', '#1e3799',
                    ];
                    $colorIndex = 0;
                @endphp

                @if($groupedItems->count() > 0)
                <div class="table-responsive" id="ctnTableToPrint">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Old Printed CTN</th>
                                <th>New Printed CTN</th>
                                <th>Written CTN</th>
                                <th>Goods Name</th>
                                <th>Weight</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($groupedItems as $ctn => $items)
                                @if($items->count() > 1)
                                    @php
                                        $bg = $colors[$colorIndex % count($colors)];
                                        $colorIndex++;
                                    @endphp
                                @else
                                    @php $bg = ''; @endphp
                                @endif

                                @foreach($items as $item)
                                <tr style="background: {{ $bg }}">
                                    <td>{{ $loop->parent->iteration }}.{{ $loop->iteration }}</td>
                                    <td><strong>{{ $item->printed_ctn_no ?? '-' }}</strong></td>
                                    <td>
                                        {{-- ✅ Show editable input only if user can edit-ctn, otherwise show text --}}
                                        @can('edit-ctn')
                                            <input type="text"
                                                   name="printed_ctn[{{ $item->id }}]"
                                                   value="{{ $item->printed_ctn_no }}"
                                                   class="form-control form-control-sm"
                                                   placeholder="Enter new CTN (nullable)">
                                        @else
                                            {{ $item->printed_ctn_no ?? '-' }}
                                        @endcan
                                    </td>
                                    <td>{{ $item->written_ctn_no ?? '-' }}</td>
                                    <td>{{ $item->goods_name_en }}</td>
                                    <td>{{ number_format($item->weight_kg, 0) }} kg</td>
                                    <td>
                                        <span class="badge badge-{{ $item->status == 'packed' ? 'info' : 'secondary' }}">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @else
                    <div class="alert alert-warning text-center">
                        No cargo items found.
                    </div>
                @endif

                {{-- ✅ Save button (bottom) — only for edit-ctn --}}
                @can('edit-ctn')
                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fa fa-save"></i> Save All Changes
                        </button>
                    </div>
                @endcan

                @can('edit-ctn')
                    </form>
                @endcan

            @endcan

        </div>
    </div>

</div>

<script>
function printCTNTable() {
    var el = document.getElementById('ctnTableToPrint');
    if (!el) return;

    var printContents = el.innerHTML;
    var originalContents = document.body.innerHTML;

    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
    location.reload();
}
</script>

<style>
.table td, .table th {
    vertical-align: middle !important;
}
.table {
    --bs-table-bg: transparent;
}
</style>

@endsection