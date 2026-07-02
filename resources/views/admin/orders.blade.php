@extends('layouts.admin')

@section('title', 'Orders Management - Foodify')
@section('page_title', 'Orders Management')

@php
    $statuses = ['pending', 'confirmed', 'preparing', 'on_the_way', 'delivered', 'cancelled'];
    $paymentStatuses = ['pending', 'paid', 'failed'];
@endphp

@section('content')
    <section class="admin-page">
        <div class="page-kpis">
            <article class="metric-card compact"><div class="metric-icon green"><svg><use href="#icon-orders"></use></svg></div><div><span>Pending Orders</span><strong>{{ $orderStats['pending'] }}</strong><small>Need review</small></div></article>
            <article class="metric-card compact"><div class="metric-icon amber"><svg><use href="#icon-alert"></use></svg></div><div><span>Preparing</span><strong>{{ $orderStats['preparing'] }}</strong><small>Kitchen queue</small></div></article>
            <article class="metric-card compact"><div class="metric-icon green"><svg><use href="#icon-send"></use></svg></div><div><span>On The Way</span><strong>{{ $orderStats['onTheWay'] }}</strong><small>Active delivery</small></div></article>
            <article class="metric-card compact"><div class="metric-icon red"><svg><use href="#icon-trend"></use></svg></div><div><span>Cancelled</span><strong>{{ $orderStats['cancelled'] }}</strong><small>All time</small></div></article>
        </div>

        @if(session('status'))
            <div class="flash-message">{{ session('status') }}</div>
        @endif

        @if($errors->any())
            <div class="error-message">{{ $errors->first() }}</div>
        @endif

        <div class="page-layout">
            <section class="panel table-panel page-main-panel">
                <div class="panel-header compact">
                    <h2>All Orders</h2>
                    <div class="table-actions">
                        <button class="crud-button primary" form="orderFilters" type="submit">Filter</button>
                        <button class="crud-button ghost" form="orderFilters" formaction="{{ route('admin.orders.export') }}" type="submit">Export PDF</button>
                        <a class="crud-button ghost" href="{{ route('admin.orders') }}">Reset</a>
                        <button class="crud-button warn" type="button">Refund Queue</button>
                        <button class="crud-button primary" data-modal-target="#orderCreateModal" type="button">New Order</button>
                    </div>
                </div>
                <form id="orderFilters" class="crud-tools" method="GET" action="{{ route('admin.orders') }}">
                    <div class="crud-filter-fields">
                        <label class="search-box order-search-box">
                            <span><svg><use href="#icon-search"></use></svg></span>
                            <input name="search" type="search" value="{{ request('search') }}" placeholder="Search order, customer...">
                        </label>
                        <select name="status" aria-label="Order status"><option value="">All Statuses</option>@foreach($statuses as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ str($status)->replace('_', ' ')->headline() }}</option>@endforeach</select>
                        <select name="payment_status" aria-label="Payment status"><option value="">All Payments</option>@foreach($paymentStatuses as $status)<option value="{{ $status }}" @selected(request('payment_status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select>
                        <input name="date_from" type="date" value="{{ request('date_from') }}" aria-label="Date from">
                        <input name="date_to" type="date" value="{{ request('date_to') }}" aria-label="Date to">
                    </div>
                </form>
                <div class="table-scroll">
                    <table class="orders-table">
                        <thead>
                            <tr><th>Order</th><th>Customer</th><th>Items</th><th>Total</th><th>Payment</th><th>Status</th><th>Time</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <td><a href="#" data-modal-target="#orderViewModal{{ $order->id }}">{{ $order->order_number }}</a></td>
                                    <td><a href="{{ $order->user ? route('admin.customers.show', $order->user) : '#' }}">{{ $order->user?->full_name }}</a><br><small>{{ $order->user?->phone }}</small></td>
                                    <td>{{ $order->order_items_count }} meals</td>
                                    <td>${{ number_format((float) $order->total, 2) }}</td>
                                    <td><span class="badge {{ $order->payment_status === 'paid' ? 'done' : ($order->payment_status === 'failed' ? 'cancel' : 'wait') }}">{{ ucfirst($order->payment_status) }}</span></td>
                                    <td><span class="badge {{ in_array($order->status, ['delivered', 'confirmed'], true) ? 'done' : ($order->status === 'cancelled' ? 'cancel' : 'prep') }}">{{ str($order->status)->replace('_', ' ')->headline() }}</span></td>
                                    <td>{{ $order->created_at->diffForHumans() }}</td>
                                    <td>
                                        <span class="row-actions">
                                            <button class="crud-button ghost" data-modal-target="#orderViewModal{{ $order->id }}" type="button">View</button>
                                            <button class="crud-button ghost" data-modal-target="#orderEditModal{{ $order->id }}" type="button">Edit</button>
                                            <a class="crud-button ghost" href="{{ route('admin.orders.invoice', $order) }}" target="_blank">Print</a>
                                            <button class="crud-button danger" data-modal-target="#orderDeleteModal{{ $order->id }}" type="button">Del</button>
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="pagination-row">{{ $orders->links('vendor.pagination.foodify') }}</div>
            </section>

            <aside class="page-side">
                <section class="panel">
                    <h2>Order Status</h2>
                    <div class="mini-bars">
                        @php($statusTotal = max(array_sum($orderStats), 1))
                        <div><span>Pending</span><strong>{{ $orderStats['pending'] }}</strong><i style="width:{{ round(($orderStats['pending'] / $statusTotal) * 100) }}%"></i></div>
                        <div><span>Preparing</span><strong>{{ $orderStats['preparing'] }}</strong><i style="width:{{ round(($orderStats['preparing'] / $statusTotal) * 100) }}%"></i></div>
                        <div><span>On The Way</span><strong>{{ $orderStats['onTheWay'] }}</strong><i style="width:{{ round(($orderStats['onTheWay'] / $statusTotal) * 100) }}%"></i></div>
                    </div>
                </section>

            </aside>
        </div>
    </section>

    <div class="modal-backdrop" id="orderCreateModal" aria-hidden="true">
        <section class="crud-modal wide" role="dialog" aria-modal="true" aria-labelledby="orderCreateTitle">
            <div class="modal-header"><div><h2 id="orderCreateTitle">Create Order</h2><p>Search customer by name, phone, or partial value, then select meals.</p></div><button class="modal-close" data-modal-close type="button">×</button></div>
            <form class="crud-form" action="{{ route('admin.orders.store') }}" method="POST" data-order-form>
                @csrf
                @include('admin.partials.order-form', ['order' => null, 'customers' => $customers, 'meals' => $meals, 'statuses' => $statuses, 'paymentStatuses' => $paymentStatuses])
                <div class="modal-footer"><button class="crud-button ghost" data-modal-close type="button">Cancel</button><button class="crud-button primary" type="submit">Save Order</button></div>
            </form>
        </section>
    </div>

    @foreach($orders as $order)
        <div class="modal-backdrop" id="orderViewModal{{ $order->id }}" aria-hidden="true">
            <section class="crud-modal" role="dialog" aria-modal="true" aria-labelledby="orderViewTitle{{ $order->id }}">
                <div class="modal-header"><div><h2 id="orderViewTitle{{ $order->id }}">Order {{ $order->order_number }}</h2><p>{{ $order->user?->full_name }} - {{ $order->user?->phone }}</p></div><button class="modal-close" data-modal-close type="button">×</button></div>
                <div class="crud-form">
                    <ul class="detail-list">
                        <li><span>Customer</span><strong>{{ $order->user?->full_name }}</strong></li>
                        <li><span>Delivery Address</span><strong>{{ $order->delivery_address ?? '-' }}</strong></li>
                        <li><span>Status</span><strong>{{ str($order->status)->replace('_', ' ')->headline() }}</strong></li>
                        <li><span>Payment</span><strong>{{ ucfirst($order->payment_status) }}</strong></li>
                        <li><span>Subtotal</span><strong>${{ number_format((float) $order->subtotal, 2) }}</strong></li>
                        <li><span>Delivery Fee</span><strong>${{ number_format((float) $order->delivery_fee, 2) }}</strong></li>
                        <li><span>Adjustment</span><strong>${{ number_format((float) $order->manual_adjustment, 2) }}</strong></li>
                        <li><span>Total</span><strong>${{ number_format((float) $order->total, 2) }}</strong></li>
                        <li><span>Notes</span><strong>{{ $order->notes ?: '-' }}</strong></li>
                    </ul>
                    <table>
                        <thead><tr><th>Meal</th><th>Qty</th><th>Unit</th><th>Total</th></tr></thead>
                        <tbody>
                            @foreach($order->orderItems as $item)
                                <tr><td>{{ $item->meal_name }}</td><td>{{ $item->quantity }}</td><td>${{ number_format((float) $item->unit_price, 2) }}</td><td>${{ number_format((float) $item->total, 2) }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="modal-footer"><button class="crud-button primary" data-modal-close type="button">Done</button></div>
                </div>
            </section>
        </div>

        <div class="modal-backdrop" id="orderEditModal{{ $order->id }}" aria-hidden="true">
            <section class="crud-modal wide" role="dialog" aria-modal="true" aria-labelledby="orderEditTitle{{ $order->id }}">
                <div class="modal-header"><div><h2 id="orderEditTitle{{ $order->id }}">Edit Order</h2><p>Update customer, meals, quantities, status, and payment.</p></div><button class="modal-close" data-modal-close type="button">×</button></div>
                <form class="crud-form" action="{{ route('admin.orders.update', $order) }}" method="POST" data-order-form>
                    @csrf
                    @method('PUT')
                    @include('admin.partials.order-form', ['order' => $order, 'customers' => $customers, 'meals' => $meals, 'statuses' => $statuses, 'paymentStatuses' => $paymentStatuses])
                    <div class="modal-footer"><button class="crud-button ghost" data-modal-close type="button">Cancel</button><button class="crud-button primary" type="submit">Update Order</button></div>
                </form>
            </section>
        </div>

        <div class="modal-backdrop" id="orderDeleteModal{{ $order->id }}" aria-hidden="true">
            <section class="crud-modal small" role="dialog" aria-modal="true" aria-labelledby="orderDeleteTitle{{ $order->id }}">
                <div class="modal-header"><div><h2 id="orderDeleteTitle{{ $order->id }}">Delete Order</h2><p>This will delete order items too.</p></div><button class="modal-close" data-modal-close type="button">×</button></div>
                <form class="crud-form" action="{{ route('admin.orders.destroy', $order) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <p class="delete-copy">Delete order <strong>{{ $order->order_number }}</strong>?</p>
                    <div class="modal-footer"><button class="crud-button ghost" data-modal-close type="button">Cancel</button><button class="crud-button danger" type="submit">Delete</button></div>
                </form>
            </section>
        </div>
    @endforeach
@endsection
