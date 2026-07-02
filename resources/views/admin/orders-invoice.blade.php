<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $order->order_number }}</title>
    <link rel="stylesheet" href="{{ asset('admin-dashboard.css') }}">
</head>
<body class="invoice-body">
    @php
        $formatMoney = fn (float|int $value): string => ((float) $value < 0 ? '-$' : '$').number_format(abs((float) $value), 2);
    @endphp

    <main class="invoice-page">
        <header class="invoice-header">
            <div><h1>Foodify Invoice</h1><p>{{ $order->order_number }}</p></div>
            <button class="crud-button primary print-hidden" onclick="window.print()" type="button">Print Invoice</button>
        </header>
        <section class="panel">
            <ul class="detail-list">
                <li><span>Customer</span><strong>{{ $order->user?->full_name }}</strong></li>
                <li><span>Phone</span><strong>{{ $order->user?->phone }}</strong></li>
                <li><span>Address</span><strong>{{ $order->delivery_address ?? '-' }}</strong></li>
                <li><span>Status</span><strong>{{ str($order->status)->replace('_', ' ')->headline() }}</strong></li>
            </ul>
        </section>
        <section class="panel table-panel">
            <table>
                <thead><tr><th>Meal</th><th>Qty</th><th>Unit</th><th>Total</th></tr></thead>
                <tbody>
                    @foreach($order->orderItems as $item)
                        <tr><td>{{ $item->meal_name }}</td><td>{{ $item->quantity }}</td><td>{{ $formatMoney($item->unit_price) }}</td><td>{{ $formatMoney($item->total) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </section>
        <section class="panel invoice-totals">
            <p><span>Subtotal</span><strong>{{ $formatMoney($order->subtotal) }}</strong></p>
            <p><span>Delivery Fee</span><strong>{{ $formatMoney($order->delivery_fee) }}</strong></p>
            <p><span>Adjustment</span><strong>{{ $formatMoney($order->manual_adjustment) }}</strong></p>
            <p class="grand-total"><span>Total</span><strong>{{ $formatMoney($order->total) }}</strong></p>
        </section>
    </main>
</body>
</html>
