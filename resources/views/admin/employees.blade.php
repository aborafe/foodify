@extends('layouts.admin')

@section('title', 'Employees - Foodify')
@section('page_title', 'Employees Management')

@section('content')
    <section class="admin-page">
        <div class="page-kpis">
            <article class="metric-card compact"><div class="metric-icon green"><svg><use href="#icon-users"></use></svg></div><div><span>Total Employees</span><strong>{{ $employeeStats['total'] }}</strong><small>Admin staff</small></div></article>
            <article class="metric-card compact"><div class="metric-icon green solid"><svg><use href="#icon-dashboard"></use></svg></div><div><span>Admins</span><strong>{{ $employeeStats['admins'] }}</strong><small>Full access</small></div></article>
            <article class="metric-card compact"><div class="metric-icon amber"><svg><use href="#icon-orders"></use></svg></div><div><span>Cashiers</span><strong>{{ $employeeStats['cashiers'] }}</strong><small>Orders only</small></div></article>
            <article class="metric-card compact"><div class="metric-icon red"><svg><use href="#icon-alert"></use></svg></div><div><span>Inactive</span><strong>{{ $employeeStats['inactive'] }}</strong><small>Blocked login</small></div></article>
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
                    <h2>Employee Accounts</h2>
                    <div class="table-actions"><button class="crud-button primary" data-modal-target="#employeeCreateModal">Add Employee</button></div>
                </div>

                <table>
                    <thead>
                        <tr><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $employee)
                            <tr>
                                <td><span class="mini-avatar dark">{{ strtoupper(substr($employee->full_name, 0, 2)) }}</span>{{ $employee->full_name }}</td>
                                <td>{{ $employee->email }}</td>
                                <td>{{ $employee->phone ?? '-' }}</td>
                                <td><span class="badge {{ $employee->isAdmin() ? 'done' : 'wait' }}">{{ $employee->isAdmin() ? 'Admin' : 'Cashier' }}</span></td>
                                <td><span class="badge {{ $employee->is_active ? 'done' : 'cancel' }}">{{ $employee->is_active ? 'Active' : 'Inactive' }}</span></td>
                                <td>
                                    <span class="row-actions">
                                        <button class="crud-button ghost" data-modal-target="#employeeEditModal{{ $employee->id }}">Edit</button>
                                        <button class="crud-button danger" data-modal-target="#employeeDeleteModal{{ $employee->id }}">Del</button>
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="pagination-row">{{ $employees->links('vendor.pagination.foodify') }}</div>
            </section>

            <aside class="page-side">
                <section class="panel">
                    <h2>Role Permissions</h2>
                    <div class="activity-list">
                        <p><b>Admin</b> can review dashboard, products, customers, reports, notifications, and employees.</p>
                        <p><b>Cashier</b> can access orders only to create and update order workflow.</p>
                    </div>
                </section>
                <section class="panel">
                    <h2>Access Split</h2>
                    <div class="mini-bars">
                        <div><span>Admin</span><strong>{{ $employeeStats['admins'] }}</strong><i style="width:60%"></i></div>
                        <div><span>Cashier</span><strong>{{ $employeeStats['cashiers'] }}</strong><i style="width:40%"></i></div>
                    </div>
                </section>
            </aside>
        </div>
    </section>

    <div class="modal-backdrop" id="employeeCreateModal" aria-hidden="true">
        <section class="crud-modal" role="dialog" aria-modal="true" aria-labelledby="employeeCreateTitle">
            <div class="modal-header"><div><h2 id="employeeCreateTitle">Add Employee</h2><p>Create an admin or cashier dashboard account.</p></div><button class="modal-close" data-modal-close type="button">×</button></div>
            <form class="crud-form" action="{{ route('admin.employees.store') }}" method="POST">
                @csrf
                <div class="form-grid">
                    <label class="form-field"><span>Full Name</span><input name="full_name" value="{{ old('full_name') }}"></label>
                    <label class="form-field"><span>Email</span><input name="email" type="email" value="{{ old('email') }}"></label>
                    <label class="form-field"><span>Phone</span><input name="phone" value="{{ old('phone') }}"></label>
                    <label class="form-field"><span>Role</span><select name="role"><option value="admin">Admin</option><option value="cashier" selected>Cashier</option></select></label>
                    <label class="form-field"><span>Password</span><input name="password" type="password"></label>
                    <label class="form-field"><span>Confirm Password</span><input name="password_confirmation" type="password"></label>
                    <label class="form-field full checkbox-field"><input name="is_active" type="checkbox" value="1" checked> Active employee can login</label>
                </div>
                <div class="modal-footer"><button class="crud-button ghost" data-modal-close type="button">Cancel</button><button class="crud-button primary" type="submit">Save Employee</button></div>
            </form>
        </section>
    </div>

    @foreach($employees as $employee)
        <div class="modal-backdrop" id="employeeEditModal{{ $employee->id }}" aria-hidden="true">
            <section class="crud-modal" role="dialog" aria-modal="true" aria-labelledby="employeeEditTitle{{ $employee->id }}">
                <div class="modal-header"><div><h2 id="employeeEditTitle{{ $employee->id }}">Edit Employee</h2><p>Update role, contact, status, or password.</p></div><button class="modal-close" data-modal-close type="button">×</button></div>
                <form class="crud-form" action="{{ route('admin.employees.update', $employee) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-grid">
                        <label class="form-field"><span>Full Name</span><input name="full_name" value="{{ old('full_name', $employee->full_name) }}"></label>
                        <label class="form-field"><span>Email</span><input name="email" type="email" value="{{ old('email', $employee->email) }}"></label>
                        <label class="form-field"><span>Phone</span><input name="phone" value="{{ old('phone', $employee->phone) }}"></label>
                        <label class="form-field"><span>Role</span><select name="role"><option value="admin" @selected($employee->isAdmin())>Admin</option><option value="cashier" @selected($employee->isCashier())>Cashier</option></select></label>
                        <label class="form-field"><span>New Password</span><input name="password" type="password" placeholder="Leave blank to keep current password"></label>
                        <label class="form-field"><span>Confirm Password</span><input name="password_confirmation" type="password"></label>
                        <label class="form-field full checkbox-field"><input name="is_active" type="checkbox" value="1" @checked($employee->is_active)> Active employee can login</label>
                    </div>
                    <div class="modal-footer"><button class="crud-button ghost" data-modal-close type="button">Cancel</button><button class="crud-button primary" type="submit">Update Employee</button></div>
                </form>
            </section>
        </div>

        <div class="modal-backdrop" id="employeeDeleteModal{{ $employee->id }}" aria-hidden="true">
            <section class="crud-modal small" role="dialog" aria-modal="true" aria-labelledby="employeeDeleteTitle{{ $employee->id }}">
                <div class="modal-header"><div><h2 id="employeeDeleteTitle{{ $employee->id }}">Delete Employee</h2><p>This removes dashboard access.</p></div><button class="modal-close" data-modal-close type="button">×</button></div>
                <form class="crud-form" action="{{ route('admin.employees.destroy', $employee) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <p class="delete-copy">Delete <strong>{{ $employee->full_name }}</strong> from employee accounts?</p>
                    <div class="modal-footer"><button class="crud-button ghost" data-modal-close type="button">Cancel</button><button class="crud-button danger" type="submit">Delete</button></div>
                </form>
            </section>
        </div>
    @endforeach
@endsection
