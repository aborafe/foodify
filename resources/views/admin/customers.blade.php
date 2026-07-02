@extends('layouts.admin')

@section('title', 'Users & Customers - Foodify')
@section('page_title', 'Users & Customers')

@section('content')
    <section class="admin-page">
        <div class="page-kpis">
            <article class="metric-card compact"><div class="metric-icon green"><svg><use href="#icon-users"></use></svg></div><div><span>Total Customers</span><strong>{{ $customerStats['total'] }}</strong><small>Mobile users</small></div></article>
            <article class="metric-card compact"><div class="metric-icon green"><svg><use href="#icon-bell"></use></svg></div><div><span>Phone Verified</span><strong>{{ $customerStats['verified'] }}</strong><small>Verified phones</small></div></article>
            <article class="metric-card compact"><div class="metric-icon amber"><svg><use href="#icon-bag"></use></svg></div><div><span>Repeat Customers</span><strong>{{ $customerStats['repeat'] }}</strong><small>Ordered again</small></div></article>
            <article class="metric-card compact"><div class="metric-icon red"><svg><use href="#icon-alert"></use></svg></div><div><span>Inactive</span><strong>{{ $customerStats['inactive'] }}</strong><small>Blocked login</small></div></article>
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
                    <h2>Customer Directory</h2>
                    <div class="table-actions"><button class="crud-button ghost" type="button">Verified</button><button class="crud-button ghost" type="button">Top Buyers</button><button class="crud-button primary" data-modal-target="#customerCreateModal" type="button">Add Customer</button></div>
                </div>
                <div class="crud-tools">
                    <label class="search-box"><span><svg><use href="#icon-search"></use></svg></span><input type="search" placeholder="Search customers, phone..."></label>
                    <div><button class="crud-button ghost" type="button">Export CSV</button><button class="crud-button ghost" type="button">Send Offer</button><button class="crud-button warn" type="button">Inactive Segment</button></div>
                </div>
                <table>
                    <thead>
                        <tr><th>Customer</th><th>Phone</th><th>Email</th><th>Orders</th><th>Total Spent</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $customer)
                            <tr>
                                <td><span class="mini-avatar dark">{{ strtoupper(substr($customer->full_name, 0, 2)) }}</span>{{ $customer->full_name }}</td>
                                <td>{{ $customer->phone }}</td>
                                <td>{{ $customer->email ?? '-' }}</td>
                                <td>{{ $customer->orders_count }}</td>
                                <td>${{ number_format((float) ($customer->orders_sum_total ?? 0), 2) }}</td>
                                <td><span class="badge {{ $customer->is_active ? ($customer->phone_verified_at ? 'done' : 'wait') : 'cancel' }}">{{ $customer->is_active ? ($customer->phone_verified_at ? 'Verified' : 'Active') : 'Inactive' }}</span></td>
                                <td><span class="row-actions"><button class="crud-button ghost" data-modal-target="#customerViewModal{{ $customer->id }}" type="button">View</button><button class="crud-button ghost" data-modal-target="#customerEditModal{{ $customer->id }}" type="button">Edit</button><button class="crud-button danger" data-modal-target="#customerDeleteModal{{ $customer->id }}" type="button">Del</button></span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="pagination-row">{{ $customers->links('vendor.pagination.foodify') }}</div>
            </section>

            <aside class="page-side">
                <section class="panel">
                    <h2>Customer Segments</h2>
                    <div class="mini-bars">
                        <div><span>Verified</span><strong>{{ $customerStats['verified'] }}</strong><i style="width:89%"></i></div>
                        <div><span>Repeat</span><strong>{{ $customerStats['repeat'] }}</strong><i style="width:35%"></i></div>
                        <div><span>Inactive</span><strong>{{ $customerStats['inactive'] }}</strong><i style="width:18%"></i></div>
                    </div>
                </section>
                <section class="panel">
                    <h2>Recent Customers</h2>
                    <div class="activity-list">
                        @foreach($recentCustomers as $customer)
                            <p><b>{{ $customer->full_name }}</b> {{ $customer->phone_verified_at ? 'verified phone' : 'registered' }} {{ $customer->created_at->diffForHumans() }}</p>
                        @endforeach
                    </div>
                </section>
            </aside>
        </div>
    </section>

    <div class="modal-backdrop" id="customerCreateModal" aria-hidden="true">
        <section class="crud-modal" role="dialog" aria-modal="true" aria-labelledby="customerCreateTitle">
            <div class="modal-header"><div><h2 id="customerCreateTitle">Add Customer</h2><p>Create a mobile customer account.</p></div><button class="modal-close" data-modal-close type="button">×</button></div>
            <form class="crud-form" action="{{ route('admin.customers.store') }}" method="POST">
                @csrf
                @include('admin.partials.customer-form', ['customer' => null])
                <div class="modal-footer"><button class="crud-button ghost" data-modal-close type="button">Cancel</button><button class="crud-button primary" type="submit">Save Customer</button></div>
            </form>
        </section>
    </div>

    @foreach($customers as $customer)
        <div class="modal-backdrop" id="customerViewModal{{ $customer->id }}" aria-hidden="true">
            <section class="crud-modal small" role="dialog" aria-modal="true" aria-labelledby="customerViewTitle{{ $customer->id }}">
                <div class="modal-header"><div><h2 id="customerViewTitle{{ $customer->id }}">Customer Profile</h2><p>{{ $customer->full_name }}</p></div><button class="modal-close" data-modal-close type="button">×</button></div>
                <div class="crud-form"><ul class="detail-list"><li><span>Name</span><strong>{{ $customer->full_name }}</strong></li><li><span>Phone</span><strong>{{ $customer->phone }}</strong></li><li><span>Email</span><strong>{{ $customer->email ?? '-' }}</strong></li><li><span>Orders</span><strong>{{ $customer->orders_count }}</strong></li><li><span>Total Spent</span><strong>${{ number_format((float) ($customer->orders_sum_total ?? 0), 2) }}</strong></li></ul><div class="modal-footer"><button class="crud-button primary" data-modal-close type="button">Done</button></div></div>
            </section>
        </div>

        <div class="modal-backdrop" id="customerEditModal{{ $customer->id }}" aria-hidden="true">
            <section class="crud-modal" role="dialog" aria-modal="true" aria-labelledby="customerEditTitle{{ $customer->id }}">
                <div class="modal-header"><div><h2 id="customerEditTitle{{ $customer->id }}">Edit Customer</h2><p>Modify customer profile and access state.</p></div><button class="modal-close" data-modal-close type="button">×</button></div>
                <form class="crud-form" action="{{ route('admin.customers.update', $customer) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('admin.partials.customer-form', ['customer' => $customer])
                    <div class="modal-footer"><button class="crud-button ghost" data-modal-close type="button">Cancel</button><button class="crud-button primary" type="submit">Update Customer</button></div>
                </form>
            </section>
        </div>

        <div class="modal-backdrop" id="customerDeleteModal{{ $customer->id }}" aria-hidden="true">
            <section class="crud-modal small" role="dialog" aria-modal="true" aria-labelledby="customerDeleteTitle{{ $customer->id }}">
                <div class="modal-header"><div><h2 id="customerDeleteTitle{{ $customer->id }}">Delete Customer</h2><p>This removes customer data and cascades related rows.</p></div><button class="modal-close" data-modal-close type="button">×</button></div>
                <form class="crud-form" action="{{ route('admin.customers.destroy', $customer) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <p class="delete-copy">Remove <strong>{{ $customer->full_name }}</strong> from customers?</p>
                    <div class="modal-footer"><button class="crud-button ghost" data-modal-close type="button">Cancel</button><button class="crud-button danger" type="submit">Delete</button></div>
                </form>
            </section>
        </div>
    @endforeach
@endsection
