<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Shipment Report - {{ $report['shipment']->shipment_name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; padding: 20px; }
        h1 { font-size: 20px; margin-bottom: 4px; color: #2b3965; }
        h2 { font-size: 14px; margin: 16px 0 8px; color: #2b3965; border-bottom: 2px solid #2b3965; padding-bottom: 4px; }
        .header { text-align: center; margin-bottom: 20px; }
        .meta { color: #666; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background-color: #2b3965; color: #fff; font-weight: bold; }
        tr:nth-child(even) td { background-color: #f9f9f9; }
        .text-right { text-align: right; }
        .summary-box { border: 1px solid #2b3965; padding: 10px; margin-bottom: 12px; background: #f4f6fb; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 4px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; color: #fff; font-size: 10px; }
        .badge-paid { background: #1cc88a; }
        .badge-partial { background: #f6c23e; }
        .badge-pending { background: #e74a3b; }
        .footer { text-align: center; margin-top: 20px; font-size: 9px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Shipment Cost Report</h1>
        <div class="meta">{{ $report['shipment']->shipment_name }}</div>
        <div class="meta">Date: {{ $report['shipment']->shipment_date }} | Status: {{ ucfirst($report['shipment']->status) }}</div>
    </div>

    <h2>Financial Summary</h2>
    <table>
        <tr>
            <th>Total Cost</th>
            <th>Total Paid</th>
            <th>Total Due</th>
            <th>Payment Progress</th>
        </tr>
        <tr>
            <td>{{ number_format($report['summary']['total_cost'], 0) }}</td>
            <td>{{ number_format($report['summary']['total_paid'], 0) }}</td>
            <td>{{ number_format($report['summary']['total_due'], 0) }}</td>
            <td>
                @if($report['summary']['total_cost'] > 0)
                    {{ number_format(($report['summary']['total_paid'] / $report['summary']['total_cost']) * 100, 1) }}%
                @else
                    0%
                @endif
            </td>
        </tr>
    </table>

    <h2>Shipment Details</h2>
    <table>
        <tr>
            <th>Total Items</th>
            <th>Total Weight</th>
            <th>Total CBM</th>
        </tr>
        <tr>
            <td>{{ $report['summary']['total_items'] }}</td>
            <td>{{ number_format($report['summary']['total_weight'], 0) }} kg</td>
            <td>{{ number_format($report['summary']['total_cbm'], 4) }}</td>
        </tr>
    </table>

    <h2>Cost Type Summary</h2>
    <table>
        <thead>
            <tr>
                <th>Cost Type</th>
                <th class="text-right">Amount</th>
                <th class="text-right">Paid</th>
                <th class="text-right">Due</th>
                <th class="text-right">Count</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report['cost_type_wise_summary'] as $costType => $data)
            <tr>
                <td>{{ $costType }}</td>
                <td class="text-right">{{ number_format($data['total_amount'], 0) }}</td>
                <td class="text-right">{{ number_format($data['total_paid'], 0) }}</td>
                <td class="text-right">{{ number_format($data['total_due'], 0) }}</td>
                <td class="text-right">{{ $data['count'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if(count($report['agent_wise_summary']) > 0)
    <h2>Agent Summary</h2>
    <table>
        <thead>
            <tr>
                <th>Agent</th>
                <th class="text-right">Amount</th>
                <th class="text-right">Paid</th>
                <th class="text-right">Due</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report['agent_wise_summary'] as $agentName => $data)
            <tr>
                <td>{{ $agentName }}</td>
                <td class="text-right">{{ number_format($data['total_amount'], 0) }}</td>
                <td class="text-right">{{ number_format($data['total_paid'], 0) }}</td>
                <td class="text-right">{{ number_format($data['total_due'], 0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <h2>Cost Details</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Cost Type</th>
                <th>Agent</th>
                <th class="text-right">Amount</th>
                <th class="text-right">Paid</th>
                <th class="text-right">Due</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report['shipment']->costs as $i => $cost)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $cost->costType->name }}</td>
                <td>{{ $cost->agent ? $cost->agent->name : 'N/A' }}</td>
                <td class="text-right">{{ number_format($cost->amount, 0) }}</td>
                <td class="text-right">{{ number_format($cost->paid_amount, 0) }}</td>
                <td class="text-right">{{ number_format($cost->due_amount, 0) }}</td>
                <td>{{ ucfirst($cost->status) }}</td>
                <td>{{ \Carbon\Carbon::parse($cost->cost_date)->format('M d, Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ now()->format('M d, Y H:i') }}
    </div>
</body>
</html>