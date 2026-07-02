@extends('layouts.admin')

@section('title', $customer->full_name.' - Customer Profile')
@section('page_title', 'Customer Profile')

@section('content')
    <section class="admin-page">
        <div class="page-layout">
            <section class="panel page-main-panel">
                <div class="panel-header compact">
                    <h2>{{ $customer->full_name }}</h2>
                    <a class="crud-button ghost" href="{{ route('admin.orders', ['customer_id' => $customer->id]) }}">View Orders</a>
                </div>
                <ul class="detail-list">
                    <li><span>Full Name</span><strong>{{ $customer->full_name }}</strong></li>
                    <li><span>Phone</span><strong>{{ $customer->phone }}</strong></li>
                    <li><span>Email</span><strong>{{ $customer->email ?? '-' }}</strong></li>
                    <li><span>Birth Date</span><strong>{{ $customer->birth_date?->toDateString() ?? '-' }}</strong></li>
                    <li><span>Primary Address</span><strong>{{ $customer->address ?? '-' }}</strong></li>
                    <li><span>Phone Verified</span><strong>{{ $customer->phone_verified_at ? 'Verified' : 'Not verified' }}</strong></li>
                    <li><span>Status</span><strong>{{ $customer->is_active ? 'Active' : 'Inactive' }}</strong></li>
                    <li><span>Created</span><strong>{{ $customer->created_at?->toDayDateTimeString() }}</strong></li>
                </ul>
            </section>

            <aside class="page-side">
                <section class="panel">
                    <h2>Order Addresses</h2>
                    <div class="activity-list">
                        @forelse($addresses as $address)
                            <p>{{ $address }}</p>
                        @empty
                            <p>No delivery addresses yet.</p>
                        @endforelse
                    </div>
                </section>
            </aside>
        </div>

        <section class="panel table-panel">
            <div class="panel-header compact"><h2>Customer Orders</h2></div>
            <table>
                <thead><tr><th>Order</th><th>Status</th><th>Payment</th><th>Total</th><th>Items</th><th>Notes</th><th>Date</th></tr></thead>
                <tbody>
                    @forelse($customer->orders as $order)
                        <tr>
                            <td><a href="{{ route('admin.orders', ['search' => $order->order_number]) }}">{{ $order->order_number }}</a></td>
                            <td><span class="badge {{ in_array($order->status, ['delivered', 'confirmed'], true) ? 'done' : ($order->status === 'cancelled' ? 'cancel' : 'prep') }}">{{ str($order->status)->replace('_', ' ')->headline() }}</span></td>
                            <td>{{ ucfirst($order->payment_status) }}</td>
                            <td>${{ number_format((float) $order->total, 2) }}</td>
                            <td>
                                @foreach($order->orderItems as $item)
                                    <div>{{ $item->meal_name }} x {{ $item->quantity }}</div>
                                @endforeach
                            </td>
                            <td>{{ $order->notes ?: '-' }}</td>
                            <td>{{ $order->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="empty-state">No orders yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </section>
@endsection
