<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        body { color: #111827; font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h1 { margin: 0 0 4px; color: #078729; font-size: 22px; }
        p { margin: 0 0 18px; color: #6b7280; }
        table { width: 100%; border-collapse: collapse; }
        th { padding: 8px; background: #effaf1; color: #06471d; text-align: left; font-size: 10px; }
        td { padding: 8px; border-bottom: 1px solid #e5e7eb; }
        .money { text-align: right; white-space: nowrap; }
        .badge { padding: 3px 7px; border-radius: 999px; background: #f3f4f6; font-size: 10px; }
    </style>
</head>
<body>
    <h1>Foodify Orders Report</h1>
    <p>Generated at {{ $generatedAt->toDayDateTimeString() }} - {{ $orders->count() }} orders</p>

    <table>
        <thead>
            <tr>
                <th>Order</th>
                <th>Customer</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Payment</th>
                <th class="money">Subtotal</th>
                <th class="money">Delivery</th>
                <th class="money">Adjust.</th>
                <th class="money">Total</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orders as $order)
                <tr>
                    <td>{{ $order->order_number }}</td>
                    <td>{{ $order->user?->full_name ?? '-' }}</td>
                    <td>{{ $order->user?->phone ?? '-' }}</td>
                    <td><span class="badge">{{ str($order->status)->replace('_', ' ')->headline() }}</span></td>
                    <td>{{ ucfirst($order->payment_status) }}</td>
                    <td class="money">${{ number_format((float) $order->subtotal, 2) }}</td>
                    <td class="money">${{ number_format((float) $order->delivery_fee, 2) }}</td>
                    <td class="money">${{ number_format((float) $order->manual_adjustment, 2) }}</td>
                    <td class="money">${{ number_format((float) $order->total, 2) }}</td>
                    <td>{{ $order->created_at?->toDateString() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
