<table class="table table-bordered">
    <thead class="thead-light">
        <tr>
            <th>Date</th>
            <th>Delivered Weight (KG)</th>
            <th>Amount Paid (TK)</th>
            <th>Notes</th>
        </tr>
    </thead>

    <tbody>
        @forelse($bill->deliveries as $d)
        <tr>
            <td>{{ \Carbon\Carbon::parse($d->delivery_date)->format('d M Y') }}</td>
            <td>{{ $d->delivered_weight }} KG</td>
            <td>{{ number_format($d->amount, 2) }} TK</td>
            <td>{{ $d->notes ?? '-' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="4" class="text-center">No deliveries yet.</td>
        </tr>
        @endforelse
    </tbody>
</table>
