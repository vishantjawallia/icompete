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
</head>


<body style="background-image:url('{{ static_asset('admin/images/bg.png') }}'); background-position:center;">
    <!-- Preloader -->
    @include('inc.loaders')
    <div class="authincation fix-wrapper">
        <div class="container h-100">
            <div class="row justify-content-center h-100 align-items-center">
                <div class="col-md-6">
                    <div class="authincation-content">
                        <div class="row no-gutters">
                            <div class="col-xl-12">
                                @yield('content')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- App js -->
    <script src="{{ static_asset('admin/js/global.min.js') }}"></script>
    <script src="{{ static_asset('admin/js/vendors.js') }}"></script>
    <script src="{{ static_asset('admin/js/custom.min.js') }}"></script>
    <script src="{{ static_asset('admin/js/app.js') }}"></script>
    <script src="{{ static_asset('admin/js/deznav-init.js') }}"></script>

    @stack('scripts')

    @include('inc.scripts')
</body>

</html>
