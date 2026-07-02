<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login | Foodify</title>
    <link rel="stylesheet" href="{{ asset('admin-dashboard.css') }}">
    <script>
        window.foodifyDashboardTranslations = {
            en: @json(\Illuminate\Support\Facades\Lang::get('admin.text', [], 'en')),
            ar: @json(\Illuminate\Support\Facades\Lang::get('admin.text', [], 'ar')),
        };
    </script>
    <script src="{{ asset('admin-dashboard.js') }}" defer></script>
</head>
<body class="auth-body">
    <svg class="icon-sprite" aria-hidden="true">
        <symbol id="icon-language" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.2 2.4 3.4 5.4 3.4 9s-1.2 6.6-3.4 9M12 3C9.8 5.4 8.6 8.4 8.6 12s1.2 6.6 3.4 9"/></symbol>
        <symbol id="icon-dashboard" viewBox="0 0 24 24"><path d="M4 11.5 12 5l8 6.5V20a1 1 0 0 1-1 1h-5v-6h-4v6H5a1 1 0 0 1-1-1v-8.5Z"/></symbol>
        <symbol id="icon-lock" viewBox="0 0 24 24"><path d="M6 10h12v10H6z"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></symbol>
        <symbol id="icon-mail" viewBox="0 0 24 24"><path d="M4 6h16v12H4z"/><path d="m4 7 8 6 8-6"/></symbol>
    </svg>

    <main class="auth-page">
        <section class="auth-visual" aria-label="Foodify admin">
            <div class="auth-brand">
                <div class="brand-mark">
                    <span>foodify</span>
                    <i></i>
                </div>
                <p>Healthy Food, Healthy Life</p>
            </div>

            <div class="auth-hero-card">
                <span class="metric-icon green solid"><svg><use href="#icon-dashboard"></use></svg></span>
                <div>
                    <strong>Admin Dashboard</strong>
                    <p>Manage orders, meals, customers, notifications, and business reports from one clean workspace.</p>
                </div>
            </div>

            <div class="auth-illustration" aria-hidden="true">
                <span class="leaf leaf-one"></span>
                <span class="leaf leaf-two"></span>
                <span class="leaf leaf-three"></span>
                <span class="bowl"></span>
                <span class="bottle"></span>
                <span class="tomato"></span>
                <span class="carrot"></span>
            </div>
        </section>

        <section class="auth-panel">
            <div class="auth-toolbar">
                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                <button class="language-toggle" id="languageToggle" type="button" aria-label="Switch language" aria-pressed="false"><svg><use href="#icon-language"></use></svg><span>AR</span></button>
            </div>

            <form class="login-card" action="{{ route('admin.login.store') }}" method="post">
                @csrf
                <div class="login-heading">
                    <span class="metric-icon green"><svg><use href="#icon-lock"></use></svg></span>
                    <div>
                        <h1>Admin Login</h1>
                        <p>Sign in to continue to Foodify management.</p>
                    </div>
                </div>

                <label class="auth-field">
                    <span>Email Address</span>
                    <div>
                        <svg><use href="#icon-mail"></use></svg>
                        <input name="email" type="email" value="{{ old('email', 'admin@foodify.test') }}" autocomplete="email">
                    </div>
                    @error('email')<small>{{ $message }}</small>@enderror
                </label>

                <label class="auth-field">
                    <span>Password</span>
                    <div>
                        <svg><use href="#icon-lock"></use></svg>
                        <input name="password" type="password" value="password123" autocomplete="current-password">
                    </div>
                    @error('password')<small>{{ $message }}</small>@enderror
                </label>

                <div class="auth-options">
                    <label><input name="remember" type="checkbox" value="1" checked> Remember me</label>
                    <a href="#">Forgot password?</a>
                </div>

                <button class="auth-submit" type="submit">Login to Dashboard</button>

                <p class="auth-note">Admin: admin@foodify.test / password123<br>Cashier: cashier@foodify.test / password123</p>
            </form>
        </section>
    </main>
</body>
</html>
