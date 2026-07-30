@extends('layouts.app')

@section('page-title', 'Create User')
@section('page-subtitle', 'Staff accounts are created by admin only.')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card-modern p-4 p-md-5">
            <form method="POST" action="{{ route('admin.users.store') }}" id="userForm">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Role</label>
                        <select name="role" id="roleSelect" class="form-select" required>
                            @foreach(['student','warden','caretaker','admin'] as $role)
                                <option value="{{ $role }}" @selected(old('role') === $role)>{{ ucfirst($role) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 role-field role-student">
                        <label class="form-label">Student ID</label>
                        <input type="text" name="student_id_number" class="form-control" value="{{ old('student_id_number') }}">
                    </div>
                    <div class="col-md-6 role-field role-student">
                        <label class="form-label">Programme</label>
                        <input type="text" name="programme" class="form-control" value="{{ old('programme') }}">
                    </div>
                    <div class="col-md-6 role-field role-warden d-none">
                        <label class="form-label">Block Assigned</label>
                        <select name="block_assigned" class="form-select">
                            @foreach(config('hostel.blocks') as $block)
                                <option value="{{ $block }}">{{ $block }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-brand mt-4">Create User</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const roleSelect = document.getElementById('roleSelect');
function toggleRoleFields() {
    document.querySelectorAll('.role-field').forEach(el => el.classList.add('d-none'));
    document.querySelectorAll('.role-' + roleSelect.value).forEach(el => el.classList.remove('d-none'));
}
roleSelect.addEventListener('change', toggleRoleFields);
toggleRoleFields();
</script>
@endpush
