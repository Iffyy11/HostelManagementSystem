@extends('layouts.app')

@section('page-title', 'Report Maintenance Issue')
@section('page-subtitle')
Room: {{ $booking->room->displayName() }}
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card-modern p-4 p-md-5">
            <form method="POST" action="{{ route('student.maintenance.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">Category</label>
                    <select name="category" class="form-select" required>
                        @foreach(['plumbing','electrical','furniture','other'] as $cat)
                            <option value="{{ $cat }}" @selected(old('category') === $cat)>{{ ucfirst($cat) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="description" rows="5" class="form-control" required>{{ old('description') }}</textarea>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Photo (optional)</label>
                    <input type="file" name="photo" class="form-control" accept="image/*">
                </div>
                <button type="submit" class="btn btn-brand">Submit Request</button>
            </form>
        </div>
    </div>
</div>
@endsection
