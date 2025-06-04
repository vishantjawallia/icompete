@extends('admin.layouts.master')

@section('title', 'Notifications')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="fw-bold">Notifications</h5>
            <a class="btn btn-sm btn-primary read-all" href="#" >Read All</a>
        </div>
        <div class="card-body">
            <ul class="list-group px-1" id="notifications-list">
                @forelse ($notifs as $notification)
                    <li class="list-group-item d-flex justify-content-between align-items-center @if ($notification->read_at == null) bg-light @endif" id="notification-{{ $notification->id }}">
                        <div type="button"  onclick="openNotif('{{ $notification->id }}')">
                            <h5 class="mb-1">{{ $notification->title ?? ucfirst($notification->type) }}</h5>
                            <p class="mb-1">{{ $notification->message }} {{ $notification->message }} {{ $notification->message }}</p>
                            <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                        </div>

                        <div class="dropdown">
                            <button type="button" class="btn btn-outline-primary btn-icon-xxs tp-btn fs-18 border border-primary" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                @if ($notification->read_at == null)
                                <li><a class="dropdown-item mark-as-read" href="#" data-id="{{ $notification->id }}"><i class="bi bi-check me-2"></i>Mark as Read</a></li>
                                @endif
                                <li><a class="dropdown-item text-danger delete-notif" data-id="{{ $notification->id }}" href="#"><i class="bi bi-trash me-2"></i>Delete</a></li>
                            </ul>
                        </div>
                    </li>
                @empty
                    <a href="javascript:void(0);" class="dropdown-item p-0 notify-item card m-0 my-1 shadow-none">
                        <div class="card-body px-1">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 text-truncate ms-2 text-center">
                                    <i class="fa-duotone fa-bell-slash" style="font-size: 100px;padding: 20px;"></i>
                                    <h5 class="noti-item-title fw-semibold fs-14">No new notifications</h5>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforelse
            </ul>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        // Mark notification as read
        $(document).on('click', '.mark-as-read', function (e) {
            e.preventDefault(); // Prevent default action
            let notificationId = $(this).data('id');
            let url = `{{ route('admin.notification.read', ':id') }}`.replace(':id', notificationId);

            $.ajax({
                url: url,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response.status === 'success') {
                        let notifItem = $(`#notification-${notificationId}`);
                        notifItem.removeClass('bg-light'); // Remove unread background
                        notifItem.find('.mark-as-read').remove(); // Remove "Mark as Read" button
                    }
                },
                error: function (xhr) {
                    console.error('Error:', xhr.responseText);
                }
            });
        });
        // delete notification
        $(document).on('click', '.delete-notif', function (e) {
            e.preventDefault(); // Prevent default action

            let notificationId = $(this).data('id');
            let url = `{{ route('admin.notification.delete', ':id') }}`.replace(':id', notificationId);
            Swal.fire({
                title: 'Are you sure?',
                text: "This notification will be deleted permanently.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Proceed with deletion if confirmed
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            if (response.status === 'success') {
                                $(`#notification-${notificationId}`).remove();
                                toastr.success('Notification deleted successfully!');
                            }
                        },
                        error: function (xhr) {
                            console.error('Error:', xhr.responseText);
                        }
                    });
                }
            });
        });
        $(document).on('click', '.read-all', function (e) {
            e.preventDefault(); // Prevent default action
            let url = `{{ route('admin.notifications.readAll') }}`;
            // Proceed with deletion if confirmed
            $.ajax({
                url: url,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response.status === 'success') {
                        $('.list-group-item.bg-light').each(function () {
                            $(this).removeClass('bg-light'); // Remove unread background
                            $(this).find('.mark-as-read').remove();
                        });
                        toastr.success('All notifications marked as read.');
                    }
                },
                error: function (xhr) {
                    console.error('Error:', xhr.responseText);
                }
            });
        });


        // // Listen for real-time notifications using Laravel Echo
        // window.Echo.channel("admin.notifications")
        //     .listen(".notification.new", function (data) {
        //         let notification = data.notification;
        //         let newItem = `
        //             <li class="list-group-item d-flex justify-content-between align-items-center bg-light" id="notification-${notification.id}">
        //                 <div>
        //                     <h5 class="mb-1">${notification.title ?? notification.type.charAt(0).toUpperCase() + notification.type.slice(1)}</h5>
        //                     <p class="mb-1">${notification.message}</p>
        //                     <small class="text-muted">Just now</small>
        //                 </div>
        //                 <a href="#" class="btn btn-sm btn-primary mark-as-read" data-id="${notification.id}">Mark as Read</a>
        //             </li>`;

        //         $('#notifications-list').prepend(newItem);
        //     });
    });

    function openNotif(notificationId) {
        // Send AJAX request to mark the notification as read
        let url = `{{ route('admin.notification.open', ':id') }}`.replace(':id', notificationId);
        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                if (response.status === 'success') {
                    let notifItem = $(`#notification-${notificationId}`);
                    notifItem.removeClass('bg-light');
                    notifItem.find('.mark-as-read').remove();

                    if (response.url) {
                        window.location.href = response.url;
                    }
                } else {
                }
            },
            error: function(xhr) {
            }
        });
    }
</script>
@endpush

@push('styles')
@endpush
