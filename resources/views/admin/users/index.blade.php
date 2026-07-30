@extends('layouts.app')

@section('page-title', 'User Management')
@section('page-subtitle', 'Create and manage student, warden, caretaker, and admin accounts.')

@section('topbar-actions')
    <a href="{{ route('admin.users.create') }}" class="btn btn-brand"><i class="bi bi-person-plus me-1"></i> Add User</a>
@endsection

@section('content')
<div class="card-modern">
    <div class="table-responsive">
        <table class="table table-modern mb-0 align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td class="fw-semibold">{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td><span class="badge bg-light text-dark text-capitalize">{{ $user->getRoleNames()->first() }}</span></td>
                        <td>
                            <span class="badge rounded-pill {{ $user->is_active ? 'bg-success-subtle text-success-emphasis' : 'bg-danger-subtle text-danger-emphasis' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <form action="{{ route('admin.users.toggle', $user) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-sm btn-outline-secondary">{{ $user->is_active ? 'Deactivate' : 'Activate' }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $users->links() }}</div>
</div>
@endsection
