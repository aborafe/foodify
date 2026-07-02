@extends('layouts.admin')

@section('title', 'Search Results - Foodify')
@section('page_title', 'Global Search')

@section('content')
    <section class="admin-page">
        <section class="panel">
            <form class="crud-tools" action="{{ route('admin.search') }}" method="GET">
                <label class="search-box">
                    <span><svg><use href="#icon-search"></use></svg></span>
                    <input name="q" type="search" value="{{ $query }}" placeholder="Search anything...">
                </label>
                <button class="crud-button primary" type="submit">Search</button>
            </form>
        </section>

        @forelse($groups as $group)
            <section class="panel table-panel">
                <div class="panel-header compact"><h2>{{ $group['label'] }}</h2><span>{{ $group['count'] }} matches</span></div>
                <table>
                    <thead><tr><th>Result</th><th>Action</th></tr></thead>
                    <tbody>
                        @foreach($group['items'] as $item)
                            <tr><td>{{ $item['title'] }}</td><td><a class="crud-button ghost" href="{{ $item['url'] }}">Open</a></td></tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        @empty
            <section class="panel"><p>No search results found.</p></section>
        @endforelse
    </section>
@endsection
