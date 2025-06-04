@extends('admin.layouts.master')

@section('title', 'Contests Submissions')

@section('content')
    <div class="card mb-1">
        <div class="card-header">
            <h5 class="fw-bold">{{ $submission->title }} Details</h5>
            <div class="text-end">
                <span>Type: <span class="badge bg-info py-1">{{ $submission->type }}</span></span>
                @if ($submission->type == 'submission')
                {!! submissionStatus($submission->status) !!}
                @else
                {!! submissionStatus($submission->vote_status) !!}
                @endif
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-6 col-md-3">
            <div class="card">
                <a class="card-body d-flex pe-2 justify-content-between" href='{{ route('admin.contest.show', $submission->contest_id) }}'>
                    <div class="mb-2">
                        <h6 class="text-muted small">Contest</h6>
                        <h5 class="text-dark fw-bold mb-0 h5">
                            {{ $submission->contest->title ?? 'nil' }}
                        </h5>
                    </div>
                    <span class="dash-icon bg-primary my-auto">
                        <i class="far fa-user-group"></i>
                    </span>
                </a>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card">
                <a class="card-body d-flex pe-2 justify-content-between" href="{{route('admin.submission.votes', $submission->id)}}">
                    <div class="mb-2">
                        <h6 class="text-muted small">Contest Votes</h6>
                        <h5 class="mb-0 fw-bold">{{ $submission->vote_count }}</h5>
                    </div>
                    <span class="dash-icon bg-success my-auto">
                        <i class="fas fa-vote-yea"></i>
                    </span>
                </a>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card">
                <a class="card-body d-flex pe-2 justify-content-between">
                    <div class="mb-2">
                        <h6 class="text-muted small">Ranks</h6>
                        <h5 class="mb-0 fw-bold">{{ $submission->ranking() ?? 0}}</h5>
                    </div>
                    <span class="dash-icon bg-warning my-auto">
                        <i class="fas fa-trophy"></i>
                    </span>
                </a>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card">
                <a class="card-body d-flex pe-2 justify-content-between">
                    <div class="mb-2">
                        <h6 class="text-muted small">Total Coins Received</h6>
                        <h5 class="mb-0 fw-bold">{{ $submission->votes()->sum('amount') }}</h5>
                    </div>
                    <span class="dash-icon bg-info my-auto">
                        <i class="fas fa-coins"></i>
                    </span>
                </a>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h5 class="fw-bold">Edit Submission</h5>
            @if ($submission->type == 'submission')
                <div class="dropdown">
                    <button class="btn btn-primary border border-primary btn-sm" data-bs-toggle="dropdown">
                       <i class="fa fa-ellipsis-v me-2"></i> actions
                    </button>
                    <ul class="dropdown-menu">
                        @if ($submission->status == 'pending' || $submission->status == 'submitted')
                            <li><a class="dropdown-item approve-btn" href="javascript:void(0)" data-url="{{ route('admin.submission.approve', $submission->id) }}">Approve</a>
                            </li>
                            <li><a class="dropdown-item reject-btn" href="javascript:void(0)" data-url="{{ route('admin.submission.reject', $submission->id) }}">Reject</a>
                            </li>
                        @endif
                    </ul>
                </div>
            @endif
        </div>
        <div class="card-body">
            <form action="{{ route('admin.submission.update', $submission->id) }}" class="row" method="post" enctype="multipart/form-data">
                @csrf
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" value="{{ $submission->title }}" required>
                    </div>

                    @if ($submission->type == 'entry')
                        <div class="form-group">
                            <label for="vote_status" class="form-label">Vote Status</label>
                            <select class="form-select" id="vote_status" name="vote_status" required>
                                <option value="enabled" {{ $submission->vote_status == 'enabled' ? 'selected' : '' }}>Enabled</option>
                                <option value="disabled" {{ $submission->vote_status == 'disabled' ? 'selected' : '' }}>Disabled</option>
                            </select>
                        </div>
                    @else
                        <div class="form-group">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="approved" {{ $submission->status == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="pending" {{ $submission->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="rejected" {{ $submission->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                    @endif
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3">{{ $submission->description }}</textarea>
                    </div>
                    <div class="form-group bg-white">
                        <label class="form-label fw-bold mb-3">Submission Response</label>

                        @if ($submission->response)
                            <div class="submission-response">
                                @foreach ($submission->response as $item)
                                    <div class="response-item bg-light p-3 rounded mb-3 border">
                                        <h6 class="text-primary mb-2">{{ $item->name }}</h6>

                                        @if ($item->type === 'image')
                                            <div class="media-container mt-2">
                                                <img src="{{ my_asset($item->value) }}" alt="{{ $item->name }}" class="img-thumbnail"
                                                    style="max-width: 250px; height: auto;">
                                            </div>
                                        @elseif($item->type === 'video')
                                            <div class="media-container mt-2">
                                                <video controls class="rounded shadow-sm" style="max-width: 100%; height: auto;">
                                                    <source src="{{ asset($item->value) }}" type="video/mp4">
                                                    Your browser does not support the video tag.
                                                </video>
                                            </div>
                                        @elseif($item->type === 'url')
                                            <div class="url-container mt-2">
                                                <a href="{{ $item->value }}" target="_blank" class="text-decoration-none text-primary">
                                                    <i class="fas fa-external-link-alt me-1"></i>
                                                    {{ $item->value }}
                                                </a>
                                            </div>
                                        @else
                                            <div class="text-container mt-2 ">
                                               <p class="fw-bold">{{ $item->value }}</p>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-info" role="alert">
                                <i class="fas fa-info-circle me-2"></i>
                                No submission response available.
                            </div>
                        @endif
                    </div>

                </div>

                <button type="submit" class="btn btn-primary w-100">Update Submission</button>
            </form>
        </div>
    </div>

@endsection
@push('styles')
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            // Approve Contest
            $('.approve-btn').on('click', function() {
                const url = $(this).data('url');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You want to approve this submission?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, approve it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            });

            // Reject Contest
            $('.reject-btn').on('click', function() {
                const url = $(this).data('url');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You want to reject this submission?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, reject it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            });

            // Delete Contest
            $('.delete-sub').on('click', function() {
                const url = $(this).data('url');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            });
        });
    </script>
@endpush
