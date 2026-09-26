<div class="return-items-info">
    <p class="mb-2">
        <strong>{{ $items->count() }}</strong> item(s) will be returned to <span class="badge badge-secondary">PENDING</span>.
    </p>

    <div class="table-responsive mb-3" style="max-height: 300px; overflow-y: auto;">
        <table class="table table-sm table-bordered mb-0">
            <thead class="thead-light">
                <tr>
                    <th>ID</th>
                    <th>Tracking</th>
                    <th>Goods</th>
                    <th>Current Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->tracking_no }}</td>
                        <td>
                            {{ $item->goods_name_en }}
                            @if($item->goods_name_local)
                                <br><small class="text-muted">({{ $item->goods_name_local }})</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-secondary">
                                {{ strtoupper(str_replace('_', ' ', $item->status)) }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <input type="hidden" id="returnItemIds" value="{{ $items->pluck('id')->implode(',') }}">

    <div class="form-group">
        <label class="font-weight-bold">Return Date <span class="text-danger">*</span></label>
        <input type="date" id="returnDate" class="form-control" value="{{ date('Y-m-d') }}" required>
    </div>

    <div class="form-group mb-0">
        <label class="font-weight-bold">Note</label>
        <textarea id="returnNote" class="form-control" rows="3" placeholder="Reason for return..."></textarea>
    </div>
</div>