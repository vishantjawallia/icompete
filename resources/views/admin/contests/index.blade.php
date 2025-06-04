@extends('admin.layouts.master')

@section('title', 'Manage Contests')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="fw-bold">All Contests</h5>
            <form action="" method="GET">
                <div class="input-group justify-content-end">
                    <input type="search" name="search" class="form-control" placeholder="@lang('Search Contest')"
                        value="{{ request()->search ?? '' }}" id="search">
                    <button class="btn btn-primary input-group-text" type="search"><i class="far fa-search"></i></button>
                </div>
            </form>
        </div>
        <div class="row gy-3 gx-3 p-3">
            <div class="col-xl-3 col-lg-3 col-md-4 col-6">
                <div class="custom-select-box-two">
                    <label>Contest Type</label>
                    <select class="form-select" onchange="window.location.href=this.value">
                        <option value="{{ queryBuild('type', '') }}" {{ request('type') == '' ? 'selected' : '' }}>
                            @lang('All Contests')</option>
                        <option value="{{ queryBuild('type', 'free') }}" {{ request('type') == 'free' ? 'selected' : '' }}>
                            @lang('Free Contests')</option>
                        <option value="{{ queryBuild('type', 'paid') }}" {{ request('type') == 'paid' ? 'selected' : '' }}>
                            @lang('Paid Contests')</option>
                    </select>
                </div><!-- custom-select-box-two end -->
            </div>
            <div class="col-xl-3 col-lg-3 col-md-4 col-6">
                <div class="custom-select-box-two">
                    <label>Contest Status</label>
                    <select class="form-select" onchange="window.location.href=this.value">
                        <option value="{{ queryBuild('status', '') }}" {{ request('status') == '' ? 'selected' : '' }}>
                            @lang('All Status')</option>
                        @foreach ($statuses as $status)
                            <option value="{{ queryBuild('status', $status['status']) }}"
                                {{ request('status') == $status['status'] ? 'selected' : '' }}>
                                {{ ucfirst($status['status']) }}</option>
                        @endforeach
                    </select>
                </div><!-- custom-select-box-two end -->
            </div>
        </div>
        <div class="card-body table-responsive">
            @if ($contests->count() > 0)
                <table class="table table-striped responsive-table">
                    <thead>
                        <tr>
                            <th>Organizer</th>
                            <th>Name</th>
                            <th>Image</th>
                            <th>Featured</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($contests as $contest)
                            <tr>
                                <td data-label="@lang('User')">
                                    <span class="fw-bold">{{ $contest->organizer->full_name ?? 'n/a' }}</span>
                                    <br> @<a href="{{ route('admin.users.view', $contest->organizer_id) }}"
                                        class="text-primary">{{ $contest->organizer->username ?? 'n/a' }}</a>
                                </td>
                                <td data-label="@lang('Name')" class="max-td">
                                    <a href="{{ route('admin.contest.show', $contest->id) }}"
                                        class="d-inline-block text-truncate text-primary" style="max-width: 100%;">
                                        {{ Str::limit($contest->title, 50) }}
                                    </a>
                                </td>
                                <td data-label="@lang('Image')">
                                    <img src="{{ my_asset($contest->image) }}" class="table-img" alt="">
                                </td>
                                <td data-label="@lang('Featured')">
                                    @if ($contest->featured == 1)
                                        <i class="fa fa-check-circle text-success"></i>
                                    @else
                                        <i class="fa fa-cancel text-red"></i>
                                    @endif
                                </td>
                                <td data-label="@lang('Type')">
                                    @if ($contest->type == 'paid')
                                        <span class="badge bg-primary">paid</span>
                                    @else
                                        <span class="badge bg-secondary">free</span>
                                    @endif
                                </td>
                                <td data-label="@lang('Status')">
                                    {!! contestStatus($contest->status) !!}
                                </td>
                                <td data-label="@lang('Date')">{{ show_datetime($contest->created_at) }}</td>
                                <td data-label="@lang('Actions')">
                                    <div class="dropdown">
                                        <button class="btn bg-light border border-primary btn-sm" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item"
                                                    href="{{ route('admin.contest.show', $contest->id) }}">View</a></li>
                                            <li><a class="dropdown-item"
                                                    href="{{ route('admin.contest.participants', $contest->id) }}">Participants</a>
                                            </li>
                                            @if ($contest->status == 'pending')
                                                <li><a class="dropdown-item approve-btn" href="javascript:void(0)"
                                                        data-url="{{ route('admin.contest.approve', $contest->id) }}">Approve</a>
                                                </li>
                                                <li><a class="dropdown-item reject-btn" href="javascript:void(0)"
                                                        data-url="{{ route('admin.contest.reject', $contest->id) }}">Reject</a>
                                                </li>
                                            @endif
                                            <li><a class="dropdown-item"
                                                    href="{{ route('admin.contest.votes', $contest->id) }}">Votes</a></li>
                                            <li><a class="dropdown-item delete-contest" href="javascript:void(0)"
                                                    data-url="{{ route('admin.contest.delete', $contest->id) }}">Delete</a>
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
                    <p class="fw-bold text-center"> No contest Found</p>
                </span>
            @endif
        </div>
        @if ($contests->hasPages())
            <div class="card-footer text-end">{{ paginateLinks($contests) }}</div>
        @endif
    </div>
@endsection
@push('scripts')
@endpush
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
            // Delete Contest
            $('.delete-contest').on('click', function() {
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
