@extends('layouts.admin')

@section('title', 'Categories Management - Foodify')
@section('page_title', 'Categories Management')

@section('content')
    <section class="admin-page">
        <div class="page-kpis">
            <article class="metric-card compact"><div class="metric-icon green"><svg><use href="#icon-dashboard"></use></svg></div><div><span>Total Categories</span><strong>{{ $categoryStats['total'] }}</strong><small>Menu groups</small></div></article>
            <article class="metric-card compact"><div class="metric-icon green solid"><svg><use href="#icon-products"></use></svg></div><div><span>Active Categories</span><strong>{{ $categoryStats['active'] }}</strong><small>Visible in app</small></div></article>
            <article class="metric-card compact"><div class="metric-icon amber"><svg><use href="#icon-alert"></use></svg></div><div><span>Inactive Categories</span><strong>{{ $categoryStats['inactive'] }}</strong><small>Hidden from app</small></div></article>
            <article class="metric-card compact"><div class="metric-icon green"><svg><use href="#icon-bag"></use></svg></div><div><span>With Meals</span><strong>{{ $categoryStats['withMeals'] }}</strong><small>Linked catalog</small></div></article>
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
                    <h2>Categories Directory</h2>
                    <div class="table-actions">
                        <button class="crud-button primary" form="categoryFilters" type="submit">Filter</button>
                        <a class="crud-button ghost" href="{{ route('admin.categories') }}">Reset</a>
                        <button class="crud-button primary" data-modal-target="#categoryCreateModal" type="button">Add Category</button>
                    </div>
                </div>

                <form id="categoryFilters" class="crud-tools" method="GET" action="{{ route('admin.categories') }}">
                    <div class="crud-filter-fields">
                        <label class="search-box">
                            <span><svg><use href="#icon-search"></use></svg></span>
                            <input name="search" type="search" value="{{ request('search') }}" placeholder="Search categories...">
                        </label>
                        <select name="status" aria-label="Category status">
                            <option value="">All Statuses</option>
                            <option value="active" @selected(request('status') === 'active')>Active</option>
                            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                        </select>
                    </div>
                </form>

                <table>
                    <thead>
                        <tr><th>Category</th>
                        {{-- <th>Image</th> --}}
                        <th>Meals</th><th>Status</th><th>Created</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>
                                    <span class="table-entity">
                                        @if($category->image)
                                            <img class="table-thumb" src="{{ $category->image }}" alt="{{ $category->name }}">
                                        @else
                                            <span class="product-thumb">🥗</span>
                                        @endif
                                        <strong>{{ $category->name }}</strong>
                                    </span>
                                </td>
                                {{-- <td>
                                    @if($category->image)
                                        <img class="table-thumb large" src="{{ $category->image }}" alt="{{ $category->name }}">
                                    @else
                                        -
                                    @endif
                                </td> --}}
                                <td>{{ $category->meals_count }}</td>
                                <td><span class="badge {{ $category->is_active ? 'done' : 'cancel' }}">{{ $category->is_active ? 'Active' : 'Inactive' }}</span></td>
                                <td>{{ $category->created_at?->diffForHumans() }}</td>
                                <td><span class="row-actions"><button class="crud-button ghost" data-modal-target="#categoryViewModal{{ $category->id }}" type="button">View</button><button class="crud-button ghost" data-modal-target="#categoryEditModal{{ $category->id }}" type="button">Edit</button><button class="crud-button danger" data-modal-target="#categoryDeleteModal{{ $category->id }}" type="button">Del</button></span></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="empty-state">No categories yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="pagination-row">{{ $categories->links('vendor.pagination.foodify') }}</div>
            </section>

            <aside class="page-side">
                <section class="panel">
                    <h2>Category Signals</h2>
                    <div class="mini-bars">
                        @php($total = max($categoryStats['total'], 1))
                        <div><span>Active</span><strong>{{ $categoryStats['active'] }}</strong><i style="width:{{ round(($categoryStats['active'] / $total) * 100) }}%"></i></div>
                        <div><span>Inactive</span><strong>{{ $categoryStats['inactive'] }}</strong><i style="width:{{ round(($categoryStats['inactive'] / $total) * 100) }}%"></i></div>
                        <div><span>With Meals</span><strong>{{ $categoryStats['withMeals'] }}</strong><i style="width:{{ round(($categoryStats['withMeals'] / $total) * 100) }}%"></i></div>
                    </div>
                </section>
            </aside>
        </div>
    </section>

    <div class="modal-backdrop" id="categoryCreateModal" aria-hidden="true">
        <section class="crud-modal" role="dialog" aria-modal="true" aria-labelledby="categoryCreateTitle">
            <div class="modal-header"><div><h2 id="categoryCreateTitle">Add Category</h2><p>Create a menu category for meals.</p></div><button class="modal-close" data-modal-close type="button">×</button></div>
            <form class="crud-form" action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @include('admin.partials.category-form', ['category' => null])
                <div class="modal-footer"><button class="crud-button ghost" data-modal-close type="button">Cancel</button><button class="crud-button primary" type="submit">Save Category</button></div>
            </form>
        </section>
    </div>

    @foreach($categories as $category)
        <div class="modal-backdrop" id="categoryViewModal{{ $category->id }}" aria-hidden="true">
            <section class="crud-modal small" role="dialog" aria-modal="true" aria-labelledby="categoryViewTitle{{ $category->id }}">
                <div class="modal-header"><div><h2 id="categoryViewTitle{{ $category->id }}">Category Details</h2><p>{{ $category->name }}</p></div><button class="modal-close" data-modal-close type="button">×</button></div>
                <div class="crud-form"><ul class="detail-list"><li><span>Name</span><strong>{{ $category->name }}</strong></li><li><span>Image</span><strong>@if($category->image)<img class="detail-image" src="{{ $category->image }}" alt="{{ $category->name }}">@else - @endif</strong></li><li><span>Meals</span><strong>{{ $category->meals_count }}</strong></li><li><span>Status</span><strong>{{ $category->is_active ? 'Active' : 'Inactive' }}</strong></li></ul><div class="modal-footer"><button class="crud-button primary" data-modal-close type="button">Done</button></div></div>
            </section>
        </div>

        <div class="modal-backdrop" id="categoryEditModal{{ $category->id }}" aria-hidden="true">
            <section class="crud-modal" role="dialog" aria-modal="true" aria-labelledby="categoryEditTitle{{ $category->id }}">
                <div class="modal-header"><div><h2 id="categoryEditTitle{{ $category->id }}">Edit Category</h2><p>Update category name, image, and visibility.</p></div><button class="modal-close" data-modal-close type="button">×</button></div>
                <form class="crud-form" action="{{ route('admin.categories.update', $category) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    @include('admin.partials.category-form', ['category' => $category])
                    <div class="modal-footer"><button class="crud-button ghost" data-modal-close type="button">Cancel</button><button class="crud-button primary" type="submit">Update Category</button></div>
                </form>
            </section>
        </div>

        <div class="modal-backdrop" id="categoryDeleteModal{{ $category->id }}" aria-hidden="true">
            <section class="crud-modal small" role="dialog" aria-modal="true" aria-labelledby="categoryDeleteTitle{{ $category->id }}">
                <div class="modal-header"><div><h2 id="categoryDeleteTitle{{ $category->id }}">Delete Category</h2><p>Categories with meals cannot be deleted.</p></div><button class="modal-close" data-modal-close type="button">×</button></div>
                <form class="crud-form" action="{{ route('admin.categories.destroy', $category) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <p class="delete-copy">Delete category <strong>{{ $category->name }}</strong>?</p>
                    <div class="modal-footer"><button class="crud-button ghost" data-modal-close type="button">Cancel</button><button class="crud-button danger" type="submit">Delete</button></div>
                </form>
            </section>
        </div>
    @endforeach
@endsection
