@extends('admin.layouts.master')

@section('title', 'Votes History')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="fw-bold"> Votes History</h5>
            <form action="" method="get">
                <div class="input-group">
                    <input type="search" id="searchInput" name="search" value="{{ request('search') }}" class="form-control"
                        placeholder="Search">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                </div>
            </form>
        </div>
        <div class="card-body table-responsive">
            @if ($votes->count() > 0)
                <table class="table table-striped responsive-table search-table">
                    <thead class="thead-primary">
                        <tr>
                            <th>Participant</th>
                            <th>Contest</th>
                            <th>Quantity</th>
                            <th>Coins</th>
                            <th>Type</th>
                            <th>Voter</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($votes as $item)
                            <tr id="vote-{{ $item->id }}">
                                <td data-label="@lang('Participant')">
                                    @if ($item->submission)
                                        <a href="{{ route('admin.submission.show', $item->submission_id) }}"
                                            class="text-primary">{{ $item->submission->title ?? 'n/a' }}</a>
                                    @else
                                        <span class="text-muted">Deleted</span>
                                    @endif
                                </td>
                                <td data-label="@lang('Contest')" class="max-td">
                                    <a href="{{ route('admin.contest.show', $item->contest_id) }}"
                                        class="d-inline-block text-truncate text-primary" style="max-width: 100%;">
                                        {{ Str::limit($item->contest->title ?? 'N/a', 50) }}
                                    </a>
                                </td>
                                <td data-label="@lang('Quantity')">
                                    {{ $item->quantity }}
                                </td>
                                <td data-label="Coins">{{ $item->amount }}</td>
                                <td data-label="@lang('Type')">
                                    <span class="badge py-0 bg-info">{{ $item->type }}</span>
                                </td>
                                <td data-label="@lang('Voter')">
                                    @if ($item->voter_type == 'guest')
                                        Guest
                                    @else
                                        <a href="{{ route('admin.users.view', $item->voter_id) }}"
                                            class="text-primary">{{ $item->user->full_name ?? 'n/a' }}</a>
                                    @endif
                                </td>
                                <td data-label="@lang('Date')">{{ show_datetime($item->created_at) }}</td>
                                <td data-label="@lang('Actions')">
                                    <div class="dropdown">
                                        <button class="btn bg-light border border-primary btn-sm" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item delete-vote" href="javascript:void(0)"
                                                    data-id="{{ $item->id }}"
                                                    data-url="{{ route('admin.contest.votes.delete', $item->id) }}">Delete</a>
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
                    <p class="fw-bold text-center"> No Votes Yet.</p>
                </span>
            @endif
        </div>
        @if ($votes->hasPages())
            <div class="card-footer text-end">{{ paginateLinks($votes) }}</div>
        @endif
    </div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('.delete-vote').on('click', function() {
                const url = $(this).data('url');
                const id = $(this).data('id');

                Swal.fire({
                    title: 'Delete Participants',
                    text: "Do you want to delete this vote?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        JDLoader.open();
                        fetch(url, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/json'
                                }
                            })
                            .then(response => {
                                console.log(response)
                                JDLoader.close();
                                if (response.ok) {
                                    document.getElementById('vote-' + id).remove();
                                    toastr.success('Vote has been deleted.');
                                } else {
                                    throw new Error('Failed to delete');
                                }
                            })
                            .catch(error => {
                                JDLoader.close();
                                toastr.error('Failed to delete Vote.')
                            });
                    }
                });

            });
        });
    </script>
@endpush
