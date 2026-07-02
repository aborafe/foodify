<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Foodify Admin Dashboard')</title>
    <link rel="stylesheet" href="{{ asset('admin-dashboard.css') }}">
    <script>
        window.foodifyDashboardTranslations = {
            en: @json(\Illuminate\Support\Facades\Lang::get('admin.text', [], 'en')),
            ar: @json(\Illuminate\Support\Facades\Lang::get('admin.text', [], 'ar')),
        };
    </script>
    <script src="{{ asset('admin-dashboard.js') }}" defer></script>
</head>
<body>
    @php($employee = auth('employee')->user())
    <svg class="icon-sprite" aria-hidden="true">
        <symbol id="icon-menu" viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"/></symbol>
        <symbol id="icon-dashboard" viewBox="0 0 24 24"><path d="M4 11.5 12 5l8 6.5V20a1 1 0 0 1-1 1h-5v-6h-4v6H5a1 1 0 0 1-1-1v-8.5Z"/></symbol>
        <symbol id="icon-orders" viewBox="0 0 24 24"><path d="M7 4h10l2 3v13H5V7l2-3Z"/><path d="M8 9h8M8 13h8M8 17h5"/></symbol>
        <symbol id="icon-products" viewBox="0 0 24 24"><path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z"/><path d="m4.5 8 7.5 4 7.5-4M12 12v8"/></symbol>
        <symbol id="icon-pos" viewBox="0 0 24 24"><path d="M5 5h14v10H5z"/><path d="M8 19h8M9 9h6M9 12h3"/></symbol>
        <symbol id="icon-inventory" viewBox="0 0 24 24"><path d="M4 7h16v13H4z"/><path d="M4 7l2-3h12l2 3M9 11h6"/></symbol>
        <symbol id="icon-users" viewBox="0 0 24 24"><path d="M16 20v-2a4 4 0 0 0-8 0v2"/><circle cx="12" cy="8" r="4"/><path d="M20 20v-2a3 3 0 0 0-2-2.8M18 5.3a3 3 0 0 1 0 5.4"/></symbol>
        <symbol id="icon-chart" viewBox="0 0 24 24"><path d="M4 20h16"/><path d="M7 16v-5M12 16V7M17 16v-8"/></symbol>
        <symbol id="icon-bell" viewBox="0 0 24 24"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9Z"/><path d="M10 21h4"/></symbol>
        <symbol id="icon-language" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.2 2.4 3.4 5.4 3.4 9s-1.2 6.6-3.4 9M12 3C9.8 5.4 8.6 8.4 8.6 12s1.2 6.6 3.4 9"/></symbol>
        <symbol id="icon-settings" viewBox="0 0 24 24"><path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"/><path d="M19 12a7.7 7.7 0 0 0-.1-1l2-1.6-2-3.4-2.4 1a7.7 7.7 0 0 0-1.8-1L14.3 3h-4.6L9.3 6a7.7 7.7 0 0 0-1.8 1l-2.4-1-2 3.4 2 1.6a7.7 7.7 0 0 0 0 2l-2 1.6 2 3.4 2.4-1a7.7 7.7 0 0 0 1.8 1l.4 3h4.6l.4-3a7.7 7.7 0 0 0 1.8-1l2.4 1 2-3.4-2-1.6c.1-.3.1-.7.1-1Z"/></symbol>
        <symbol id="icon-search" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></symbol>
        <symbol id="icon-calendar" viewBox="0 0 24 24"><path d="M5 5h14v15H5zM8 3v4M16 3v4M5 10h14"/></symbol>
        <symbol id="icon-bag" viewBox="0 0 24 24"><path d="M6 8h12l-1 12H7L6 8Z"/><path d="M9 8a3 3 0 0 1 6 0"/></symbol>
        <symbol id="icon-dollar" viewBox="0 0 24 24"><path d="M12 3v18M17 7.5c-1-1-2.5-1.5-4.3-1.5-2.3 0-4 1.1-4 3 0 4.5 8.6 2 8.6 6.5 0 1.9-1.8 3-4.4 3-2 0-3.8-.6-5-1.8"/></symbol>
        <symbol id="icon-trend" viewBox="0 0 24 24"><path d="M4 18 10 12l4 4 6-9"/><path d="M15 7h5v5"/></symbol>
        <symbol id="icon-alert" viewBox="0 0 24 24"><path d="M12 4 22 20H2L12 4Z"/><path d="M12 9v5M12 17h.01"/></symbol>
        <symbol id="icon-send" viewBox="0 0 24 24"><path d="M21 3 10 14"/><path d="m21 3-7 18-4-7-7-4 18-7Z"/></symbol>
        <symbol id="icon-stock" viewBox="0 0 24 24"><path d="M4 16h16M6 16V8h4v8M14 16V5h4v11"/></symbol>
        <symbol id="icon-logout" viewBox="0 0 24 24"><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/><path d="M21 4v16"/></symbol>
    </svg>

    <div class="dashboard-shell">
        <aside class="sidebar" id="adminSidebar">
            <div class="brand-panel">
                <button class="sidebar-toggle" id="sidebarToggle" type="button" aria-label="Toggle sidebar" aria-controls="adminSidebar" aria-expanded="true">
                    <svg><use href="#icon-menu"></use></svg>
                </button>
                <div class="brand-mark">
                    <span>foodify</span>
                    <i></i>
                </div>
                <p>Healthy Food, Healthy Life</p>
            </div>

            <nav class="side-nav" aria-label="Admin navigation">
                @if($employee?->isAdmin())
                <a class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><span class="nav-icon"><svg><use href="#icon-dashboard"></use></svg></span><span class="nav-label">Dashboard</span></a>
                @endif
                <a class="nav-item {{ request()->routeIs('admin.orders') ? 'active' : '' }}" href="{{ route('admin.orders') }}"><span class="nav-icon"><svg><use href="#icon-orders"></use></svg></span><span class="nav-label">Orders Management</span></a>
                @if($employee?->isAdmin())
                <a class="nav-item {{ request()->routeIs('admin.products') ? 'active' : '' }}" href="{{ route('admin.products') }}"><span class="nav-icon"><svg><use href="#icon-products"></use></svg></span><span class="nav-label">Products Management</span></a>
                <a class="nav-item {{ request()->routeIs('admin.categories') ? 'active' : '' }}" href="{{ route('admin.categories') }}"><span class="nav-icon"><svg><use href="#icon-dashboard"></use></svg></span><span class="nav-label">Categories Management</span></a>
                <a class="nav-item {{ request()->routeIs('admin.customers') ? 'active' : '' }}" href="{{ route('admin.customers') }}"><span class="nav-icon"><svg><use href="#icon-users"></use></svg></span><span class="nav-label">Users / Customers</span></a>
                <a class="nav-item {{ request()->routeIs('admin.reports') ? 'active' : '' }}" href="{{ route('admin.reports') }}"><span class="nav-icon"><svg><use href="#icon-chart"></use></svg></span><span class="nav-label">Reports & Analytics</span></a>
                <a class="nav-item {{ request()->routeIs('admin.notifications') ? 'active' : '' }}" href="{{ route('admin.notifications') }}"><span class="nav-icon"><svg><use href="#icon-bell"></use></svg></span><span class="nav-label">Notifications</span></a>
                <a class="nav-item {{ request()->routeIs('admin.employees.*') ? 'active' : '' }}" href="{{ route('admin.employees.index') }}"><span class="nav-icon"><svg><use href="#icon-users"></use></svg></span><span class="nav-label">Employees</span></a>
                @endif
            </nav>

            <div class="green-card">
                <h3>Go Green <span></span></h3>
                <p>Choose healthy.<br>Live healthy.<br>Be healthy.</p>
                <div class="salad-illustration" aria-hidden="true">
                    <span class="leaf leaf-one"></span>
                    <span class="leaf leaf-two"></span>
                    <span class="leaf leaf-three"></span>
                    <span class="bowl"></span>
                    <span class="bottle"></span>
                    <span class="tomato"></span>
                    <span class="carrot"></span>
                </div>
            </div>


        </aside>

        <main class="dashboard-main">
            <header class="topbar">
                <div class="topbar-title">
                    <h1>@yield('page_title', 'Dashboard Overview')</h1>
                </div>
                <div class="topbar-actions">
                    <form class="search-box global-search" action="{{ route('admin.search') }}" method="GET" data-global-search data-search-preview-url="{{ route('admin.search.preview') }}">
                        <span><svg><use href="#icon-search"></use></svg></span>
                        <input name="q" type="search" placeholder="Search anything..." autocomplete="off" data-global-search-input>
                        <div class="global-search-results" data-global-search-results></div>
                    </form>
                    <button class="date-range" type="button"><span><svg><use href="#icon-calendar"></use></svg></span>May 19, 2024 - May 25, 2024</button>
                    <button class="language-toggle" id="languageToggle" type="button" aria-label="Switch language" aria-pressed="false"><svg><use href="#icon-language"></use></svg><span>AR</span></button>
                    <div class="notification-menu" data-notification-menu>
                        <button class="notification-button" type="button" aria-label="Notifications" aria-expanded="false" data-notification-toggle>
                            <svg><use href="#icon-bell"></use></svg>
                            <span>{{ $unreadNotificationCount }}</span>
                        </button>
                        <div class="notification-dropdown" data-notification-dropdown>
                            <div class="notification-dropdown-header">
                                <strong>Notifications</strong>
                                @if($employee?->isAdmin())
                                    <a href="{{ route('admin.notifications') }}">Manage</a>
                                @endif
                            </div>
                            <div class="notification-dropdown-list">
                                @forelse($headerNotifications as $notification)
                                    <a class="notification-dropdown-item {{ $notification->is_read ? '' : 'is-unread' }}" href="{{ $notification->admin_url ?: route('admin.notifications') }}">
                                        <span>{{ str($notification->admin_context)->headline() }}</span>
                                        <strong>{{ $notification->title }}</strong>
                                        <p>{{ str($notification->body)->limit(82) }}</p>
                                        <small>{{ $notification->created_at?->diffForHumans() }}</small>
                                    </a>
                                @empty
                                    <article class="notification-dropdown-empty">
                                        <strong>No notifications</strong>
                                        <p>There are no order or meal notifications yet.</p>
                                    </article>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <div class="admin-chip">
                        <div class="avatar photo">{{ $employee ? strtoupper(substr($employee->full_name, 0, 1)) : 'A' }}</div>
                        <div>
                            <strong>{{ $employee?->full_name ?? 'Admin' }}</strong>
                            <span>{{ $employee?->role === 'cashier' ? 'Cashier' : 'Administrator' }}</span>
                        </div>
                        <form action="{{ route('admin.logout') }}" method="POST">
                            @csrf
                            <button class="logout-button icon-logout" type="submit" aria-label="Logout" title="Logout"><svg><use href="#icon-logout"></use></svg></button>
                        </form>
                    </div>
                </div>
            </header>

            @yield('content')
        </main>
    </div>
</body>
</html>
