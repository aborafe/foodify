@extends('layouts.admin')

@section('title', 'Foodify Admin Dashboard')
@section('page_title', 'Dashboard Overview')

@section('content')
    @php
        $metrics = $dashboardMetrics ?? [
            'totalOrders' => 0,
            'totalRevenue' => 0,
            'totalExpenses' => 0,
            'netProfit' => 0,
            'activeUsers' => 0,
            'lowStockItems' => 0,
        ];
        $formatMoney = fn (float|int $value): string => '$'.number_format((float) $value, 2);
    @endphp

    <section class="content-grid">
        <div class="kpi-grid">
            <article class="metric-card">
                <div class="metric-icon green"><svg><use href="#icon-bag"></use></svg></div>
                <div>
                    <span>Total Orders</span>
                    <strong>{{ number_format($metrics['totalOrders']) }}</strong>
                    <small class="positive">↑ 18.6%</small>
                    <em>vs last week</em>
                </div>
            </article>
            <article class="metric-card">
                <div class="metric-icon green solid"><svg><use href="#icon-dollar"></use></svg></div>
                <div>
                    <span>Total Revenue</span>
                    <strong>{{ $formatMoney($metrics['totalRevenue']) }}</strong>
                    <small class="positive">↑ 23.4%</small>
                    <em>vs last week</em>
                </div>
            </article>
            <article class="metric-card">
                <div class="metric-icon red"><svg><use href="#icon-trend"></use></svg></div>
                <div>
                    <span>Total Expenses</span>
                    <strong>{{ $formatMoney($metrics['totalExpenses']) }}</strong>
                    <small class="positive">↑ 11.3%</small>
                    <em>vs last week</em>
                </div>
            </article>
            <article class="metric-card">
                <div class="metric-icon green"><svg><use href="#icon-chart"></use></svg></div>
                <div>
                    <span>Net Profit</span>
                    <strong>{{ $formatMoney($metrics['netProfit']) }}</strong>
                    <small class="positive">↑ 31.8%</small>
                    <em>vs last week</em>
                </div>
            </article>
            <article class="metric-card">
                <div class="metric-icon green"><svg><use href="#icon-users"></use></svg></div>
                <div>
                    <span>Active Users</span>
                    <strong>{{ number_format($metrics['activeUsers']) }}</strong>
                    <small class="positive">↑ 9.5%</small>
                    <em>vs last week</em>
                </div>
            </article>
            <article class="metric-card">
                <div class="metric-icon amber"><svg><use href="#icon-alert"></use></svg></div>
                <div>
                    <span>Low Stock Items</span>
                    <strong>{{ number_format($metrics['lowStockItems']) }}</strong>
                    <a href="#">View items</a>
                </div>
            </article>
        </div>

        <div class="main-column">
            <section class="panel sales-panel">
                <div class="panel-header">
                    <div>
                        <h2>Sales Overview</h2>
                        <div class="legend">
                            <span><i class="dot green-dot"></i>Revenue</span>
                            <span><i class="dot gray-dot"></i>Expenses</span>
                        </div>
                    </div>
                    <button class="select-button" type="button">This Week⌄</button>
                </div>
                <div class="line-chart">
                    <svg viewBox="0 0 760 250" role="img" aria-label="Sales overview line chart">
                        <defs>
                            <linearGradient id="salesFill" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#16a538" stop-opacity=".18"/>
                                <stop offset="100%" stop-color="#16a538" stop-opacity="0"/>
                            </linearGradient>
                        </defs>
                        <g class="grid-lines">
                            <line x1="50" y1="25" x2="735" y2="25"/><line x1="50" y1="75" x2="735" y2="75"/>
                            <line x1="50" y1="125" x2="735" y2="125"/><line x1="50" y1="175" x2="735" y2="175"/><line x1="50" y1="225" x2="735" y2="225"/>
                        </g>
                        <g class="axis-labels y-labels">
                            <text x="8" y="29">20K</text><text x="18" y="79">15K</text><text x="18" y="129">10K</text><text x="25" y="179">5K</text><text x="35" y="229">0</text>
                        </g>
                        <path class="area" d="M50 158 L150 105 L250 128 L350 70 L450 118 L560 92 L660 62 L735 98 L735 225 L50 225 Z"/>
                        <polyline class="revenue-line" points="50,158 150,105 250,128 350,70 450,118 560,92 660,62 735,98"/>
                        <polyline class="expense-line" points="50,197 150,166 250,181 350,148 450,178 560,148 660,124 735,146"/>
                        <g class="markers">
                            <circle cx="50" cy="158" r="5"/><circle cx="150" cy="105" r="5"/><circle cx="250" cy="128" r="5"/>
                            <circle cx="350" cy="70" r="5"/><circle cx="450" cy="118" r="5"/><circle cx="560" cy="92" r="5"/>
                            <circle cx="660" cy="62" r="5"/><circle cx="735" cy="98" r="5"/>
                        </g>
                        <g class="axis-labels">
                            <text x="35" y="248">Mon 19</text><text x="135" y="248">Tue 20</text><text x="235" y="248">Wed 21</text>
                            <text x="335" y="248">Thu 22</text><text x="435" y="248">Fri 23</text><text x="545" y="248">Sat 24</text><text x="645" y="248">Sun 25</text>
                        </g>
                    </svg>
                </div>
            </section>

            <section class="panel donut-panel">
                <h2>Orders Status</h2>
                <div class="donut-content">
                    <div class="donut-chart">
                        <div>
                            <strong>{{ number_format($metrics['totalOrders']) }}</strong>
                            <span>Total Orders</span>
                        </div>
                    </div>
                    <ul class="status-list">
                        <li><i class="completed"></i><span>Completed</span><strong>730 (58.5%)</strong></li>
                        <li><i class="pending"></i><span>Pending</span><strong>320 (25.6%)</strong></li>
                        <li><i class="preparing"></i><span>Preparing</span><strong>140 (11.2%)</strong></li>
                        <li><i class="cancelled"></i><span>Cancelled</span><strong>58 (4.7%)</strong></li>
                    </ul>
                </div>
            </section>

            <section class="panel table-panel recent-orders">
                <div class="panel-header compact">
                    <h2>Recent Orders</h2>
                    <a href="{{ route('admin.orders') }}">View All</a>
                </div>
                <table>
                    <thead>
                        <tr><th>Order ID</th><th>Customer</th><th>Items</th><th>Total</th><th>Status</th><th>Time</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($recentOrders as $order)
                            @php
                                $customerName = $order->user?->full_name ?? 'Guest Customer';
                                $initials = collect(explode(' ', $customerName))->filter()->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->join('');
                                $statusClass = match ($order->status) {
                                    'delivered' => 'done',
                                    'preparing', 'confirmed', 'on_the_way' => 'prep',
                                    'cancelled' => 'cancel',
                                    default => 'wait',
                                };
                            @endphp
                            <tr>
                                <td><a href="{{ route('admin.orders', ['search' => $order->order_number]) }}">#{{ $order->order_number }}</a></td>
                                <td>
                                    <span class="mini-avatar dark">{{ $initials ?: 'CU' }}</span>
                                    @if ($order->user)
                                        <a href="{{ route('admin.customers.show', $order->user) }}">{{ $customerName }}</a>
                                    @else
                                        {{ $customerName }}
                                    @endif
                                </td>
                                <td>{{ $order->order_items_count }} items</td>
                                <td>{{ $formatMoney($order->total) }}</td>
                                <td><span class="badge {{ $statusClass }}">{{ str_replace('_', ' ', ucfirst($order->status)) }}</span></td>
                                <td>{{ $order->created_at?->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6">No recent orders yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </section>

            <section class="panel table-panel products-panel">
                <div class="panel-header compact">
                    <h2>Top Selling Products</h2>
                    <a href="#">View All</a>
                </div>
                <table>
                    <thead>
                        <tr><th>Product</th><th>Sold</th><th>Revenue</th></tr>
                    </thead>
                    <tbody>
                        <tr><td><span class="product-thumb">🥗</span>Grilled Chicken Bowl</td><td>342</td><td>$4,890.00</td></tr>
                        <tr><td><span class="product-thumb">🥬</span>Quinoa Salad</td><td>298</td><td>$3,870.00</td></tr>
                        <tr><td><span class="product-thumb">🥑</span>Avocado Toast</td><td>254</td><td>$2,790.00</td></tr>
                        <tr><td><span class="product-thumb">🥤</span>Green Detox Juice</td><td>210</td><td>$1,680.00</td></tr>
                        <tr><td><span class="product-thumb">🧋</span>Protein Smoothie</td><td>186</td><td>$1,395.00</td></tr>
                    </tbody>
                </table>
            </section>

            <section class="panel analytics-panel">
                <div class="panel-header compact">
                    <h2>Analytics Overview</h2>
                    <button class="select-button" type="button">This Month⌄</button>
                </div>
                <div class="analytics-grid">
                    <article><span>Total Sales</span><strong>{{ $formatMoney($metrics['totalRevenue']) }}</strong><small>Live</small><svg viewBox="0 0 120 42"><polyline points="2,36 16,35 28,22 38,30 50,8 62,24 76,21 88,25 100,8 118,14"/></svg></article>
                    <article><span>Total Orders</span><strong>{{ number_format($metrics['totalOrders']) }}</strong><small>Live</small><svg viewBox="0 0 120 42"><polyline points="2,37 18,36 30,22 42,27 54,8 66,25 80,20 94,22 104,6 118,14"/></svg></article>
                    <article><span>Average Order Value</span><strong>{{ $formatMoney($metrics['totalOrders'] > 0 ? $metrics['totalRevenue'] / $metrics['totalOrders'] : 0) }}</strong><small>Live</small><svg viewBox="0 0 120 42"><polyline points="2,25 18,30 30,18 42,26 54,21 66,28 80,10 92,27 104,25 116,12"/></svg></article>
                    <article><span>Total Customers</span><strong>{{ number_format($metrics['activeUsers']) }}</strong><small>Active</small><svg viewBox="0 0 120 42"><polyline points="2,31 16,25 28,30 40,19 52,23 64,12 78,21 90,18 104,23 118,9"/></svg></article>
                    <article><span>Repeat Customers</span><strong>0</strong><small>Live</small><svg viewBox="0 0 120 42"><polyline points="2,34 14,22 26,28 38,16 50,20 62,8 76,24 88,18 100,20 112,10 118,14"/></svg></article>
                </div>
            </section>
        </div>

        <aside class="right-rail">
            <section class="panel notifications-panel">
                <div class="panel-header compact">
                    <h2>Recent Notifications</h2>
                    <a href="{{ route('admin.notifications') }}">View All</a>
                </div>
                <div class="notification-list">
                    @forelse ($recentNotifications as $notification)
                        <article>
                            <span class="notif-icon green"><svg><use href="#icon-orders"></use></svg></span>
                            <strong>{{ $notification->title }}</strong>
                            <small>{{ $notification->created_at?->diffForHumans() }}</small>
                        </article>
                    @empty
                        <article><span class="notif-icon green"><svg><use href="#icon-orders"></use></svg></span><strong>No notifications yet</strong><small>Now</small></article>
                    @endforelse
                </div>
            </section>

            <section class="panel quick-panel">
                <h2>Quick Actions</h2>
                <div class="quick-grid">
                    <button type="button"><span><svg><use href="#icon-bag"></use></svg></span>New Order</button>
                    <button type="button"><span><svg><use href="#icon-products"></use></svg></span>Add Product</button>
                    <button type="button"><span><svg><use href="#icon-users"></use></svg></span>Add User</button>
                    <button type="button"><span><svg><use href="#icon-inventory"></use></svg></span>Add Stock</button>
                    <button type="button"><span><svg><use href="#icon-stock"></use></svg></span>Sales Report</button>
                    <button type="button"><span><svg><use href="#icon-send"></use></svg></span>Send Notification</button>
                </div>
            </section>

            <section class="panel alerts-panel">
                <div class="panel-header compact">
                    <h2>Inventory Alerts</h2>
                    <a href="#">View All</a>
                </div>
                <div class="alert-list">
                    <article><span>🥩</span><div><strong>Chicken Breast</strong><small>1.2 kg left</small></div><em>Low Stock</em></article>
                    <article><span>🌾</span><div><strong>Quinoa</strong><small>0.8 kg left</small></div><em>Low Stock</em></article>
                    <article><span>🥑</span><div><strong>Avocado</strong><small>2.5 kg left</small></div><em>Low Stock</em></article>
                    <article><span>🫒</span><div><strong>Olive Oil</strong><small>1.1 L left</small></div><em>Low Stock</em></article>
                </div>
            </section>
        </aside>
    </section>
@endsection
