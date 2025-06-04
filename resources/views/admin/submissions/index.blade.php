@extends('admin.layouts.master')

@section('title', $title)

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="fw-bold">{{ $title }}</h5>
            <form action="" method="GET">
                <div class="input-group justify-content-end">
                    <input type="search" name="search" class="form-control" placeholder="@lang('Search Submissions')"
                        value="{{ request()->search ?? '' }}" id="searchInput">
                    <button class="btn btn-primary input-group-text" type="submit"><i class="far fa-search"></i></button>
                </div>
            </form>
        </div>
        <div class="card-body table-responsive">
            @if ($submissions->count() > 0)
                <table class="table table-striped responsive-table search-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Contest</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($submissions as $item)
                            <tr>
                                <td data-label="@lang('User')">
                                    @if ($item->user)
                                        <span class="fw-bold">{{ $item->user->full_name ?? 'n/a' }}</span>
                                        <br> @<a href="{{ route('admin.users.view', $item->user_id) }}"
                                            class="text-primary">{{ $item->user->username ?? 'n/a' }}</a>
                                    @else
                                        <span class="fw-bold">Deleted</span>
                                    @endif
                                </td>
                                <td data-label="@lang('Contest')" class="max-td">
                                    <a href="{{ route('admin.contest.show', $item->contest_id) }}"
                                        class="d-inline-block text-truncate text-primary" style="max-width: 100%;">
                                        {{ Str::limit($item->contest->title ?? 'N/a', 50) }}
                                    </a>
                                </td>
                                <td data-label="@lang('Title')">
                                    {{ $item->title }}
                                </td>
                                <td data-label="@lang('Status')">
                                    {!! submissionStatus($item->status) !!}
                                </td>
                                <td data-label="@lang('Date')">{{ show_datetime($item->created_at) }}</td>
                                <td data-label="@lang('Actions')">
                                    <div class="dropdown gx-1">
                                        <a class="btn btn-sm btn-outline-primary"
                                            href="{{ route('admin.submission.show', $item->id) }}">View</a>
                                        <button class="btn bg-light border border-primary btn-sm" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item"
                                                    href="{{ route('admin.submission.show', $item->id) }}">View</a></li>
                                            @if ($item->status == 'pending' || $item->status == 'submitted')
                                                <li><a class="dropdown-item approve-btn" href="javascript:void(0)"
                                                        data-url="{{ route('admin.submission.approve', $item->id) }}">Approve</a>
                                                </li>
                                                <li><a class="dropdown-item reject-btn" href="javascript:void(0)"
                                                        data-url="{{ route('admin.submission.reject', $item->id) }}">Reject</a>
                                                </li>
                                            @endif
                                            <li><a class="dropdown-item delete-sub" href="javascript:void(0)"
                                                    data-url="{{ route('admin.submission.delete', $item->id) }}">Delete</a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <span class="">
                    <p class="fw-bold text-center"> No submissions Found</p>
                </span>
            @endif
        </div>
        @if ($submissions->hasPages())
            <div class="card-footer text-end">{{ paginateLinks($submissions) }}</div>
        @endif
    </div>

@endsection
@push('styles')
    <style>
        .table-img {
            height: 60px;
            width: auto;
        }
    </style>
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
