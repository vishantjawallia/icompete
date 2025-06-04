<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="">
    <meta name="robots" content="index, follow">

    <title>@lang(get_setting('title')) | @yield('title')</title>
    <link rel="shortcut icon" href="{{ my_asset(get_setting('favicon')) }}">

    <!-- App css -->
    <link href="{{ static_asset('admin/css/vendors.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ static_asset('admin/css/style.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ static_asset('admin/css/custom.css') }}" rel="stylesheet" type="text/css">

    @stack('styles')
    @livewireStyles()
</head>

<body data-typography="poppins" data-layout="vertical" data-nav-headerbg="color_2" data-headerbg="color_1"
    data-sidebarbg="color_2">
    <!-- Preloader -->
    @include('inc.loaders')

    <div id="main-wrapper">

        {{-- Header --}}
        @include('admin.layouts.topbar')
        {{-- Sidebar --}}
        @include('admin.layouts.sidebar')

        {{-- page content --}}
        <div class="content-body">
            <!-- row -->
            <div class="container-fluid">
                @yield('content')
            </div>
        </div>
        {{-- Footer --}}
        <div class="footer out-footer">
            <div class="copyright">
                <p class="mb-0">© {{ get_setting('title') }} <span class="current-year">2025</span></p>
            </div>
        </div>

    </div>

    @include('inc.modals')
    <!-- App js -->
    <script src="{{ static_asset('admin/js/global.min.js') }}"></script>
    <script src="{{ static_asset('admin/js/vendors.js') }}"></script>
    <script src="{{ static_asset('admin/js/custom.min.js') }}"></script>
    <script src="{{ static_asset('admin/js/app.js') }}"></script>
    <script src="{{ static_asset('admin/js/deznav-init.js') }}"></script>
    {{-- @livewireScripts() --}}
    <script src="{{ asset('public/vendor/livewire/livewire.js') }}" data-csrf="{{ csrf_token() }}"
        data-update-uri="{{ url('livewire/update') }}" data-navigate-once="true"></script>

    @stack('scripts')
    <script>
        $(document).ready(function() {
            function fetchNotifications() {
                $.ajax({
                    url: "{{ route('admin.notifications.ajax') }}",
                    method: "GET",
                    success: function(data) {
                        updateNotificationUI(data);
                    },
                    error: function(error) {
                        console.error("Error fetching notifications:", error);
                    }
                });
            }

            function updateNotificationUI(data) {
                let countElement = $(".notif-counts");
                let notifContainer = $("#topnotif-list");

                // Update notification count
                if (data.unread_count > 0) {
                    countElement.text(data.unread_count).show();
                } else {
                    countElement.hide();
                }

                // Clear existing notifications
                notifContainer.empty();

                notifContainer.empty();
                if (data.notifications.length > 0) {
                    data.notifications.forEach(function(notification) {
                        let notificationItem = `
                            <li>
                                <div class="timeline-panel">
                                    <div class="media-body">
                                        <h6 class="mb-1">
                                            <a href="javascript:void(0);" onclick="markAsRead(${notification.id})">
                                                ${notification.title}
                                            </a>
                                        </h6>
                                        <small class="d-block text-muted">${notification.updated_at_human}</small>
                                    </div>
                                </div>
                            </li>
                        `;
                        notifContainer.append(notificationItem);
                    });
                } else {
                    notifContainer.html(`
                        <li class="text-center text-muted py-3">
                            <i class="fa-duotone fa-bell-slash" style="font-size: 50px;"></i>
                            <p>No New Notifications</p>
                        </li>
                    `);
                }
            }

            // Trigger notifications fetch when dropdown is clicked
            $("#notificationDropdown").on("click", function() {
                fetchNotifications();
            });

            // Polling every 60 seconds to fetch new notifications
            setInterval(fetchNotifications, 60000);
            fetchNotifications();
            // Mark as read function
            function markAsRead(notificationId) {
                let requestUrl = `{{ route('admin.notification.open', ':id') }}`.replace(':id', notificationId);

                $.ajax({
                    url: requestUrl,
                    type: "GET",
                    success: function(response) {
                        fetchNotifications(); // Refresh notifications
                        if (response.url) {
                            window.location.href = response.url; // Redirect if needed
                        }
                    },
                    error: function(xhr) {
                        console.error("Error marking notification as read:", xhr);
                    }
                });
            }
            // ✅ Real-time Notifications using Laravel Reverb
            // window.Echo.channel(`admin.notifications`)
            //     .listen(".notification.new", (data) => {
            //         console.log("New notification received:", data);
            //         fetchNotifications(); // Refresh UI when a new notification arrives
            //     });
        });
    </script>

    @include('inc.scripts')
</body>

</html>
