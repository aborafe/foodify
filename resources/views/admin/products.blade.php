@extends('layouts.admin')

@section('title', 'Products Management - Foodify')
@section('page_title', 'Products Management')

@section('content')
    <section class="admin-page">
        <div class="page-kpis">
            <article class="metric-card compact"><div class="metric-icon green"><svg><use href="#icon-products"></use></svg></div><div><span>Total Meals</span><strong>{{ $mealStats['total'] }}</strong><small>Active catalog</small></div></article>
            <article class="metric-card compact"><div class="metric-icon green"><svg><use href="#icon-dashboard"></use></svg></div><div><span>Categories</span><strong>{{ $mealStats['categories'] }}</strong><small>Healthy menus</small></div></article>
            <article class="metric-card compact"><div class="metric-icon amber"><svg><use href="#icon-alert"></use></svg></div><div><span>Unavailable</span><strong>{{ $mealStats['unavailable'] }}</strong><small>Need update</small></div></article>
            <article class="metric-card compact"><div class="metric-icon green solid"><svg><use href="#icon-dollar"></use></svg></div><div><span>Catalog Value</span><strong>${{ number_format((float) $mealStats['catalogValue'], 2) }}</strong><small>By meal price</small></div></article>
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
                    <h2>Meals Catalog</h2>
                    <div class="table-actions">
                        <button class="crud-button primary" form="productFilters" type="submit">Filter</button>
                        <a class="crud-button ghost" href="{{ route('admin.products') }}">Reset</a>
                        <button class="crud-button primary" data-modal-target="#productCreateModal" type="button">Add Meal</button>
                    </div>
                </div>
                <form id="productFilters" class="crud-tools" method="GET" action="{{ route('admin.products') }}">
                    <div class="crud-filter-fields">
                        <label class="search-box">
                            <span><svg><use href="#icon-search"></use></svg></span>
                            <input name="search" type="search" value="{{ request('search') }}" placeholder="Search meals, categories...">
                        </label>
                        <select name="category_id" aria-label="Category">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <select name="availability" aria-label="Availability">
                            <option value="">All Availability</option>
                            <option value="available" @selected(request('availability') === 'available')>Available</option>
                            <option value="unavailable" @selected(request('availability') === 'unavailable')>Unavailable</option>
                        </select>
                        <select name="recommendation" aria-label="Recommendation">
                            <option value="">All Recommendations</option>
                            <option value="recommended" @selected(request('recommendation') === 'recommended')>Recommended</option>
                            <option value="standard" @selected(request('recommendation') === 'standard')>Standard</option>
                        </select>
                    </div>
                </form>
                <table>
                    <thead>
                        <tr><th>Meal</th><th>Category</th><th>Price</th><th>Rating</th><th>Nutrition</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        @foreach($meals as $meal)
                            <tr>
                                <td><span class="product-thumb">🥗</span>{{ $meal->name }}</td>
                                <td>{{ $meal->category?->name }}</td>
                                <td>${{ number_format((float) $meal->price, 2) }}</td>
                                <td>{{ number_format((float) $meal->rating, 1) }}</td>
                                <td>{{ collect($meal->nutrition ?? [])->take(2)->map(fn ($value, $key) => $key.': '.$value)->implode(', ') ?: '-' }}</td>
                                <td><span class="badge {{ $meal->is_available ? ($meal->is_recommended ? 'done' : 'wait') : 'cancel' }}">{{ $meal->is_available ? ($meal->is_recommended ? 'Recommended' : 'Available') : 'Unavailable' }}</span></td>
                                <td><span class="row-actions"><button class="crud-button ghost" data-modal-target="#productViewModal{{ $meal->id }}" type="button">View</button><button class="crud-button ghost" data-modal-target="#productEditModal{{ $meal->id }}" type="button">Edit</button><button class="crud-button danger" data-modal-target="#productDeleteModal{{ $meal->id }}" type="button">Del</button></span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="pagination-row">{{ $meals->links('vendor.pagination.foodify') }}</div>
            </section>

            <aside class="page-side">
                <section class="panel">
                    <h2>Top Categories</h2>
                    <div class="chip-list">
                        @foreach($categories->take(8) as $category)
                            <span>{{ $category->name }}</span>
                        @endforeach
                    </div>
                </section>
                <section class="panel">
                    <h2>Product Signals</h2>
                    <div class="activity-list">
                        <p><b>{{ $mealStats['recommended'] }}</b> meals marked recommended</p>
                        <p><b>{{ $mealStats['unavailable'] }}</b> meals unavailable today</p>
                        <p><b>{{ number_format((float) $mealStats['averageRating'], 1) }}</b> average rating</p>
                    </div>
                </section>
            </aside>
        </div>
    </section>

    <div class="modal-backdrop" id="productCreateModal" aria-hidden="true">
        <section class="crud-modal wide" role="dialog" aria-modal="true" aria-labelledby="productCreateTitle">
            <div class="modal-header"><div><h2 id="productCreateTitle">Add Meal</h2><p>Create a product in the meals catalog.</p></div><button class="modal-close" data-modal-close type="button">×</button></div>
            <form class="crud-form" action="{{ route('admin.products.store') }}" method="POST">
                @csrf
                @include('admin.partials.meal-form', ['meal' => null, 'categories' => $categories])
                <div class="modal-footer"><button class="crud-button ghost" data-modal-close type="button">Cancel</button><button class="crud-button primary" type="submit">Save Meal</button></div>
            </form>
        </section>
    </div>

    @foreach($meals as $meal)
        <div class="modal-backdrop" id="productViewModal{{ $meal->id }}" aria-hidden="true">
            <section class="crud-modal small" role="dialog" aria-modal="true" aria-labelledby="productViewTitle{{ $meal->id }}">
                <div class="modal-header"><div><h2 id="productViewTitle{{ $meal->id }}">Meal Details</h2><p>{{ $meal->name }}</p></div><button class="modal-close" data-modal-close type="button">×</button></div>
                <div class="crud-form"><ul class="detail-list"><li><span>Meal</span><strong>{{ $meal->name }}</strong></li><li><span>Category</span><strong>{{ $meal->category?->name }}</strong></li><li><span>Price</span><strong>${{ number_format((float) $meal->price, 2) }}</strong></li><li><span>Rating</span><strong>{{ number_format((float) $meal->rating, 1) }}</strong></li><li><span>Status</span><strong>{{ $meal->is_available ? 'Available' : 'Unavailable' }}</strong></li></ul><div class="modal-footer"><button class="crud-button primary" data-modal-close type="button">Done</button></div></div>
            </section>
        </div>

        <div class="modal-backdrop" id="productEditModal{{ $meal->id }}" aria-hidden="true">
            <section class="crud-modal wide" role="dialog" aria-modal="true" aria-labelledby="productEditTitle{{ $meal->id }}">
                <div class="modal-header"><div><h2 id="productEditTitle{{ $meal->id }}">Edit Meal</h2><p>Update product information.</p></div><button class="modal-close" data-modal-close type="button">×</button></div>
                <form class="crud-form" action="{{ route('admin.products.update', $meal) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('admin.partials.meal-form', ['meal' => $meal, 'categories' => $categories])
                    <div class="modal-footer"><button class="crud-button ghost" data-modal-close type="button">Cancel</button><button class="crud-button primary" type="submit">Update Meal</button></div>
                </form>
            </section>
        </div>

        <div class="modal-backdrop" id="productDeleteModal{{ $meal->id }}" aria-hidden="true">
            <section class="crud-modal small" role="dialog" aria-modal="true" aria-labelledby="productDeleteTitle{{ $meal->id }}">
                <div class="modal-header"><div><h2 id="productDeleteTitle{{ $meal->id }}">Delete Meal</h2><p>This removes the product from catalog.</p></div><button class="modal-close" data-modal-close type="button">×</button></div>
                <form class="crud-form" action="{{ route('admin.products.destroy', $meal) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <p class="delete-copy">Remove <strong>{{ $meal->name }}</strong> from the catalog?</p>
                    <div class="modal-footer"><button class="crud-button ghost" data-modal-close type="button">Cancel</button><button class="crud-button danger" type="submit">Delete</button></div>
                </form>
            </section>
        </div>
    @endforeach
@endsection
