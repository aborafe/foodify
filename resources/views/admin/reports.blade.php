@extends('layouts.admin')

@section('title', 'Reports & Analytics - Foodify')
@section('page_title', 'Reports & Analytics')

@section('content')
    <section class="admin-page">
        @if (session('status'))
            <div class="status-banner">{{ session('status') }}</div>
        @endif

        <div class="page-kpis">
            <article class="metric-card compact"><div class="metric-icon green solid"><svg><use href="#icon-dollar"></use></svg></div><div><span>Total Sales</span><strong>${{ number_format($reportStats['totalSales'], 2) }}</strong><small>Live orders</small></div></article>
            <article class="metric-card compact"><div class="metric-icon green"><svg><use href="#icon-orders"></use></svg></div><div><span>Total Orders</span><strong>{{ number_format($reportStats['totalOrders']) }}</strong><small>All statuses</small></div></article>
            <article class="metric-card compact"><div class="metric-icon green"><svg><use href="#icon-users"></use></svg></div><div><span>Customers</span><strong>{{ number_format($reportStats['customers']) }}</strong><small>Registered users</small></div></article>
            <article class="metric-card compact"><div class="metric-icon amber"><svg><use href="#icon-chart"></use></svg></div><div><span>Avg Order</span><strong>${{ number_format($reportStats['averageOrder'], 2) }}</strong><small>Revenue / order</small></div></article>
        </div>

        <div class="report-grid">
            <section class="panel sales-panel">
                <div class="panel-header">
                    <div>
                        <h2>Revenue Trend</h2>
                        <div class="legend"><span><i class="dot green-dot"></i>Sales</span><span><i class="dot gray-dot"></i>Orders</span></div>
                    </div>
                    <div class="table-actions"><button class="crud-button ghost" data-modal-target="#reportCreateModal">Filters</button><button class="crud-button primary" data-modal-target="#reportCreateModal">New Report</button></div>
                </div>
                <div class="line-chart">
                    <svg viewBox="0 0 760 250" role="img" aria-label="Reports revenue chart">
                        <g class="grid-lines"><line x1="50" y1="25" x2="735" y2="25"/><line x1="50" y1="75" x2="735" y2="75"/><line x1="50" y1="125" x2="735" y2="125"/><line x1="50" y1="175" x2="735" y2="175"/><line x1="50" y1="225" x2="735" y2="225"/></g>
                        <g class="axis-labels y-labels"><text x="8" y="29">100K</text><text x="18" y="79">75K</text><text x="18" y="129">50K</text><text x="18" y="179">25K</text><text x="35" y="229">0</text></g>
                        <polyline class="revenue-line" points="50,168 140,142 230,128 320,98 410,118 500,78 600,64 735,48"/>
                        <polyline class="expense-line" points="50,198 140,182 230,172 320,150 410,158 500,132 600,126 735,112"/>
                        <g class="markers"><circle cx="50" cy="168" r="5"/><circle cx="140" cy="142" r="5"/><circle cx="230" cy="128" r="5"/><circle cx="320" cy="98" r="5"/><circle cx="410" cy="118" r="5"/><circle cx="500" cy="78" r="5"/><circle cx="600" cy="64" r="5"/><circle cx="735" cy="48" r="5"/></g>
                        <g class="axis-labels"><text x="45" y="248">W1</text><text x="135" y="248">W2</text><text x="225" y="248">W3</text><text x="315" y="248">W4</text><text x="405" y="248">W5</text><text x="495" y="248">W6</text><text x="595" y="248">W7</text><text x="715" y="248">W8</text></g>
                    </svg>
                </div>
            </section>

            <section class="panel donut-panel">
                <h2>Order Status Mix</h2>
                <div class="donut-content">
                    <div class="donut-chart"><div><strong>{{ number_format($reportStats['totalOrders']) }}</strong><span>Total Orders</span></div></div>
                    <ul class="status-list">
                        <li><i class="completed"></i><span>Delivered</span><strong>{{ number_format($statusMix['delivered']) }}</strong></li>
                        <li><i class="pending"></i><span>Pending</span><strong>{{ number_format($statusMix['pending']) }}</strong></li>
                        <li><i class="preparing"></i><span>Preparing</span><strong>{{ number_format($statusMix['preparing']) }}</strong></li>
                        <li><i class="cancelled"></i><span>Cancelled</span><strong>{{ number_format($statusMix['cancelled']) }}</strong></li>
                    </ul>
                </div>
            </section>

            <section class="panel table-panel analytics-panel">
                <div class="panel-header compact"><h2>Top Revenue Meals</h2><div class="table-actions"><button class="crud-button ghost">Export</button><button class="crud-button ghost">Print</button></div></div>
                <table>
                    <thead><tr><th>Meal</th><th>Orders</th><th>Revenue</th><th>Share</th></tr></thead>
                    <tbody>
                        @forelse ($topMeals as $meal)
                            <tr>
                                <td><span class="product-thumb">🥗</span>{{ $meal->name }}</td>
                                <td>{{ number_format($meal->order_items_count) }}</td>
                                <td>${{ number_format((float) $meal->order_items_sum_total, 2) }}</td>
                                <td>{{ $reportStats['totalSales'] > 0 ? number_format(((float) $meal->order_items_sum_total / $reportStats['totalSales']) * 100, 1) : 0 }}%</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="empty-state">No order item revenue yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </section>

            <section class="panel table-panel analytics-panel">
                <div class="panel-header compact"><h2>Saved Reports</h2><div class="table-actions"><button class="crud-button primary" data-modal-target="#reportCreateModal">Create Report</button></div></div>
                <table>
                    <thead><tr><th>Name</th><th>Metric</th><th>Format</th><th>Status</th><th>Generated</th><th>Actions</th></tr></thead>
                    <tbody>
                        @forelse ($reports as $report)
                            <tr>
                                <td>{{ $report->name }}</td>
                                <td>{{ str($report->metric)->replace('_', ' ')->title() }}</td>
                                <td>{{ str($report->export_format)->upper() }}</td>
                                <td><span class="status-badge {{ $report->status === 'active' ? 'completed' : ($report->status === 'draft' ? 'pending' : 'cancelled') }}">{{ str($report->status)->title() }}</span></td>
                                <td>{{ $report->generated_at?->diffForHumans() ?? 'Not generated' }}</td>
                                <td>
                                    <span class="row-actions">
                                        <button class="crud-button ghost" data-modal-target="#reportViewModal{{ $report->id }}">View</button>
                                        <button class="crud-button ghost" data-modal-target="#reportEditModal{{ $report->id }}">Edit</button>
                                        <button class="crud-button danger" data-modal-target="#reportDeleteModal{{ $report->id }}">Del</button>
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="empty-state">No saved reports yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="pagination-row">{{ $reports->links('vendor.pagination.foodify') }}</div>
            </section>
        </div>
    </section>

    <div class="modal-backdrop" id="reportCreateModal" aria-hidden="true">
        <section class="crud-modal" role="dialog" aria-modal="true" aria-labelledby="reportCreateTitle">
            <div class="modal-header"><div><h2 id="reportCreateTitle">Create Report</h2><p>Save a reusable analytics report.</p></div><button class="modal-close" data-modal-close type="button">×</button></div>
            <form class="crud-form" method="POST" action="{{ route('admin.reports.store') }}">
                @csrf
                @include('admin.partials.report-form', ['report' => null])
                <div class="modal-footer"><button class="crud-button ghost" data-modal-close type="button">Cancel</button><button class="crud-button primary" type="submit">Save Report</button></div>
            </form>
        </section>
    </div>

    @foreach ($reports as $report)
        <div class="modal-backdrop" id="reportViewModal{{ $report->id }}" aria-hidden="true">
            <section class="crud-modal small" role="dialog" aria-modal="true" aria-labelledby="reportViewTitle{{ $report->id }}">
                <div class="modal-header"><div><h2 id="reportViewTitle{{ $report->id }}">Report Detail</h2><p>{{ $report->name }}</p></div><button class="modal-close" data-modal-close type="button">×</button></div>
                <div class="crud-form">
                    <ul class="detail-list">
                        <li><span>Metric</span><strong>{{ str($report->metric)->replace('_', ' ')->title() }}</strong></li>
                        <li><span>Date Range</span><strong>{{ $report->date_range }}</strong></li>
                        <li><span>Format</span><strong>{{ str($report->export_format)->upper() }}</strong></li>
                        <li><span>Status</span><strong>{{ str($report->status)->title() }}</strong></li>
                        <li><span>Sections</span><strong>{{ collect($report->included_sections)->join(', ') ?: 'None' }}</strong></li>
                    </ul>
                    <div class="modal-footer"><button class="crud-button primary" data-modal-close type="button">Done</button></div>
                </div>
            </section>
        </div>

        <div class="modal-backdrop" id="reportEditModal{{ $report->id }}" aria-hidden="true">
            <section class="crud-modal" role="dialog" aria-modal="true" aria-labelledby="reportEditTitle{{ $report->id }}">
                <div class="modal-header"><div><h2 id="reportEditTitle{{ $report->id }}">Edit Report</h2><p>Update saved report settings.</p></div><button class="modal-close" data-modal-close type="button">×</button></div>
                <form class="crud-form" method="POST" action="{{ route('admin.reports.update', $report) }}">
                    @csrf
                    @method('PUT')
                    @include('admin.partials.report-form', ['report' => $report])
                    <div class="modal-footer"><button class="crud-button ghost" data-modal-close type="button">Cancel</button><button class="crud-button primary" type="submit">Update Report</button></div>
                </form>
            </section>
        </div>

        <div class="modal-backdrop" id="reportDeleteModal{{ $report->id }}" aria-hidden="true">
            <section class="crud-modal small" role="dialog" aria-modal="true" aria-labelledby="reportDeleteTitle{{ $report->id }}">
                <div class="modal-header"><div><h2 id="reportDeleteTitle{{ $report->id }}">Delete Saved Report</h2><p>{{ $report->name }}</p></div><button class="modal-close" data-modal-close type="button">×</button></div>
                <form class="crud-form" method="POST" action="{{ route('admin.reports.destroy', $report) }}">
                    @csrf
                    @method('DELETE')
                    <p class="delete-copy">Delete this saved report configuration?</p>
                    <div class="modal-footer"><button class="crud-button ghost" data-modal-close type="button">Cancel</button><button class="crud-button danger" type="submit">Delete</button></div>
                </form>
            </section>
        </div>
    @endforeach
@endsection
