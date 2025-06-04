@extends('admin.layouts.master')

@section('title', 'Notification History')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="fw-bold"> Notifications History</h5>
            <form action="" method="GET">
                <div class="input-group justify-content-end">
                    <input type="search" name="search" class="form-control" placeholder="@lang('Search')"
                        value="{{ request()->search ?? '' }}" id="searchInput">
                    <button class="btn btn-primary input-group-text" type="submit"><i class="far fa-search"></i></button>
                </div>
            </form>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-striped responsive-table search-table">
                <thead class="thead-primary">
                    <tr>
                        <th>@lang('User')</th>
                        <th>@lang('Sent')</th>
                        <th>@lang('Type')</th>
                        <th>@lang('Title')</th>
                        <th>@lang('Action')</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($logs as $item)
                        <tr id="notif-{{ $item->id }}">
                            <td>
                                <span class="fw-bold">{{ @$item->user->full_name ?? 'n/a' }}</span>
                                <br>
                                <span class="small">
                                    <a class="text-primary" style="text-decoration: underline;"
                                        href="{{ route('admin.users.view', $item->user_id) }}"><span>@</span>{{ @$item->user->username ?? 'n/a' }}</a>
                                </span>
                            </td>
                            <td data-label="@lang('Date')">
                                {{ show_datetime($item->created_at) }}
                                <br>
                                {{ $item->created_at->diffForHumans() }}
                            </td>
                            <td data-label="@lang('Type')">
                                <span class="badge py-0 bg-info">{{ $item->type }}</span>
                            </td>
                            <td data-label="@lang('Title')">
                                {{ $item->title }}
                            </td>
                            <td data-label="@lang('Actions')">
                                <button class="btn btn-sm btn-outline-primary notifyDetail" data-title="{{ $item->title }}"
                                    data-message="{{ $item->message }}">
                                    <i class="las la-desktop"></i> @lang('Detail')
                                </button>
                                <a class="btn btn-sm btn-outline-danger delete-notif" data-id="{{ $item->id }}"
                                    data-url="{{ route('admin.reports.notifications.delete', $item->id) }}"><i
                                        class="fa fa-trash"></i></a>
                            </td>
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
        @if ($logs->hasPages())
            <div class="card-footer text-end">{{ paginateLinks($logs) }}</div>
        @endif
    </div>

    <!-- Modal -->
    <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="notificationModalLabel">Notification Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6 id="notificationTitle"></h6>

                    <p id="notificationMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $(document).on('click', '.notifyDetail', function() {
            var title = $(this).data('title');
            var message = $(this).data('message');

            $('#notificationModal .modal-title').text(title);
            $('#notificationModal .modal-body').text(message);
            $('#notificationModal').modal('show');
        });

        $(document).ready(function() {
            $('.delete-notif').on('click', function() {
                const url = $(this).data('url');
                const id = $(this).data('id');

                Swal.fire({
                    title: 'Delete Notification',
                    text: "This notification will be deleted permanently",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        JDLoader.open();
                        fetch(url, {
                                method: 'GET',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/json'
                                }
                            })
                            .then(response => {
                                JDLoader.close();
                                if (response.ok) {
                                    document.getElementById('notif-' + id).remove();
                                    toastr.success('Notification has been deleted.');
                                } else {
                                    throw new Error('Failed to delete');
                                }
                            })
                            .catch(error => {
                                JDLoader.close();
                                toastr.error('Failed to delete Notification.')
                            });
                    }
                });

            });
        });
    </script>
@endpush
