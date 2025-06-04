<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="">
    <meta name="robots" content="index, follow">

    <title> @yield('title') - Icompete</title>
    <link rel="shortcut icon" href="{{ my_asset('favicon.png') }}">

    <!-- App css -->
    <link href="{{ static_asset('errors/css/vendors.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ static_asset('errors/css/style.css') }}" rel="stylesheet" type="text/css">

    @stack('styles')
</head>


<body>
    <div class="authincation fix-wrapper">
		<div class="container ">
			<div class="row justify-content-center h-100 align-items-center">
				<div class="col-md-6">
                    @yield('content')
				</div>
			</div>
		</div>
    </div>

    <!-- App js -->
    <script src="{{ static_asset('errors/js/global.min.js') }}"></script>
    <script src="{{ static_asset('errors/js/vendors.js') }}"></script>

    @stack('scripts')

</body>

</html>
