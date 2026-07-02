@extends('layouts.admin')

@section('title', 'Notifications - Foodify')
@section('page_title', 'Notifications')

@php($types = ['order', 'health_tip', 'offer', 'system'])
@php($employee = auth('employee')->user())

@section('content')
    <section class="admin-page">
        <div class="page-kpis">
            <article class="metric-card compact"><div class="metric-icon green"><svg><use href="#icon-bell"></use></svg></div><div><span>Total Sent</span><strong>{{ $notificationStats['total'] }}</strong><small>All records</small></div></article>
            <article class="metric-card compact"><div class="metric-icon green"><svg><use href="#icon-orders"></use></svg></div><div><span>Order Alerts</span><strong>{{ $notificationStats['order'] }}</strong><small>Automated</small></div></article>
            <article class="metric-card compact"><div class="metric-icon amber"><svg><use href="#icon-send"></use></svg></div><div><span>Offers</span><strong>{{ $notificationStats['offer'] }}</strong><small>Campaigns</small></div></article>
            <article class="metric-card compact"><div class="metric-icon green"><svg><use href="#icon-chart"></use></svg></div><div><span>Read Rate</span><strong>{{ $notificationStats['readRate'] }}%</strong><small>Read notifications</small></div></article>
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
                    <h2>Notification History</h2>
                    @if($employee?->isAdmin())
                        <div class="table-actions"><button class="crud-button ghost" type="button">Order</button><button class="crud-button ghost" type="button">Offer</button><button class="crud-button primary" data-modal-target="#notificationCreateModal" type="button">Create Notification</button></div>
                    @endif
                </div>
                <div class="crud-tools">
                    <label class="search-box"><span><svg><use href="#icon-search"></use></svg></span><input type="search" placeholder="Search notifications..."></label>
                    <div><button class="crud-button ghost" type="button">Schedule</button><button class="crud-button ghost" type="button">Templates</button><button class="crud-button warn" type="button">Unread Only</button></div>
                </div>
                <table>
                    <thead>
                        <tr><th>Title</th><th>Type</th><th>Audience</th><th>Status</th><th>Time</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        @foreach($notifications as $notification)
                            <tr>
                                <td>{{ $notification->title }}</td>
                                <td>{{ str($notification->type)->replace('_', ' ')->headline() }}</td>
                                <td>{{ $notification->user?->full_name }}</td>
                                <td><span class="badge {{ $notification->is_read ? 'done' : 'wait' }}">{{ $notification->is_read ? 'Read' : 'Unread' }}</span></td>
                                <td>{{ $notification->created_at->diffForHumans() }}</td>
                                <td><span class="row-actions"><button class="crud-button ghost" data-modal-target="#notificationViewModal{{ $notification->id }}" type="button">View</button>@if($employee?->isAdmin())<button class="crud-button ghost" data-modal-target="#notificationEditModal{{ $notification->id }}" type="button">Edit</button><button class="crud-button danger" data-modal-target="#notificationDeleteModal{{ $notification->id }}" type="button">Del</button>@endif</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="pagination-row">{{ $notifications->links('vendor.pagination.foodify') }}</div>
            </section>

            <aside class="page-side">
                @if($employee?->isAdmin())
                <section class="panel compose-card">
                    <h2>Quick Message</h2>
                    <label>Type</label>
                    <div class="chip-list"><span>Order</span><span>Offer</span><span>System</span></div>
                    <label>Preview</label>
                    <div class="message-preview">Your healthy meal update is ready. Open Foodify to view details.</div>
                    <button type="button" data-modal-target="#notificationCreateModal">Send Notification</button>
                </section>
                @endif
                <section class="panel">
                    <h2>Type Split</h2>
                    <div class="mini-bars">
                        @php($notificationTotal = max($notificationStats['total'], 1))
                        <div><span>Order</span><strong>{{ $notificationStats['order'] }}</strong><i style="width:{{ round(($notificationStats['order'] / $notificationTotal) * 100) }}%"></i></div>
                        <div><span>Offer</span><strong>{{ $notificationStats['offer'] }}</strong><i style="width:{{ round(($notificationStats['offer'] / $notificationTotal) * 100) }}%"></i></div>
                        <div><span>Read Rate</span><strong>{{ $notificationStats['readRate'] }}%</strong><i style="width:{{ $notificationStats['readRate'] }}%"></i></div>
                    </div>
                </section>
            </aside>
        </div>
    </section>

    @if($employee?->isAdmin())
    <div class="modal-backdrop" id="notificationCreateModal" aria-hidden="true">
        <section class="crud-modal" role="dialog" aria-modal="true" aria-labelledby="notificationCreateTitle">
            <div class="modal-header"><div><h2 id="notificationCreateTitle">Create Notification</h2><p>Send to one customer or all active customers.</p></div><button class="modal-close" data-modal-close type="button">×</button></div>
            <form class="crud-form" action="{{ route('admin.notifications.store') }}" method="POST">
                @csrf
                @include('admin.partials.notification-form', ['notification' => null, 'customers' => $customers, 'types' => $types, 'creating' => true])
                <div class="modal-footer"><button class="crud-button ghost" data-modal-close type="button">Cancel</button><button class="crud-button primary" type="submit">Save Notification</button></div>
            </form>
        </section>
    </div>

    @endif

    @foreach($notifications as $notification)
        <div class="modal-backdrop" id="notificationViewModal{{ $notification->id }}" aria-hidden="true">
            <section class="crud-modal small" role="dialog" aria-modal="true" aria-labelledby="notificationViewTitle{{ $notification->id }}">
                <div class="modal-header"><div><h2 id="notificationViewTitle{{ $notification->id }}">Notification Details</h2><p>{{ $notification->user?->full_name }}</p></div><button class="modal-close" data-modal-close type="button">×</button></div>
                <div class="crud-form"><ul class="detail-list"><li><span>Title</span><strong>{{ $notification->title }}</strong></li><li><span>Type</span><strong>{{ str($notification->type)->replace('_', ' ')->headline() }}</strong></li><li><span>Audience</span><strong>{{ $notification->user?->full_name }}</strong></li><li><span>Status</span><strong>{{ $notification->is_read ? 'Read' : 'Unread' }}</strong></li></ul><p class="delete-copy">{{ $notification->body }}</p><div class="modal-footer"><button class="crud-button primary" data-modal-close type="button">Done</button></div></div>
            </section>
        </div>

        @if($employee?->isAdmin())
        <div class="modal-backdrop" id="notificationEditModal{{ $notification->id }}" aria-hidden="true">
            <section class="crud-modal" role="dialog" aria-modal="true" aria-labelledby="notificationEditTitle{{ $notification->id }}">
                <div class="modal-header"><div><h2 id="notificationEditTitle{{ $notification->id }}">Edit Notification</h2><p>Adjust title, type, audience, and message.</p></div><button class="modal-close" data-modal-close type="button">×</button></div>
                <form class="crud-form" action="{{ route('admin.notifications.update', $notification) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('admin.partials.notification-form', ['notification' => $notification, 'customers' => $customers, 'types' => $types, 'creating' => false])
                    <div class="modal-footer"><button class="crud-button ghost" data-modal-close type="button">Cancel</button><button class="crud-button primary" type="submit">Update</button></div>
                </form>
            </section>
        </div>
        @endif

        @if($employee?->isAdmin())
        <div class="modal-backdrop" id="notificationDeleteModal{{ $notification->id }}" aria-hidden="true">
            <section class="crud-modal small" role="dialog" aria-modal="true" aria-labelledby="notificationDeleteTitle{{ $notification->id }}">
                <div class="modal-header"><div><h2 id="notificationDeleteTitle{{ $notification->id }}">Delete Notification</h2><p>This removes it from the notification history.</p></div><button class="modal-close" data-modal-close type="button">×</button></div>
                <form class="crud-form" action="{{ route('admin.notifications.destroy', $notification) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <p class="delete-copy">Delete <strong>{{ $notification->title }}</strong>?</p>
                    <div class="modal-footer"><button class="crud-button ghost" data-modal-close type="button">Cancel</button><button class="crud-button danger" type="submit">Delete</button></div>
                </form>
            </section>
        </div>
        @endif
    @endforeach
@endsection
