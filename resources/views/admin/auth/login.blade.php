@extends('admin.layouts.auth')

@section('title', 'Login Password')
@section('content')
<div class="auth-form">
    <div class="text-center mb-3">
        <a href="{{route('admin.login')}}">
            <img src="{{my_asset(get_setting('logo'))}}" alt="" style="width: 90px;">
        </a>
    </div>
    <h4 class="text-center mb-4">Sign in to your account</h4>
    <form action="{{route('admin.login')}}" method="POST" class="ajaxForm autoR">
        @csrf
        <div class="mb-3">
            <label class="mb-1 form-label">Email Address</label>
            <input type="email" name="email" value="{{old('email')}}" required class="form-control" placeholder="Email Address">
        </div>

        <div class="mb-3 position-relative">
            <label class="form-label" for="dz-password">Password</label>
            <input type="password" id="dz-password" class="form-control" name="password" required autocomplete="off" placeholder="Enter Password">
            <span class="show-pass eye">
                <i class="fa fa-eye-slash"></i>
                <i class="fa fa-eye"></i>
            </span>
        </div>
        <div class="form-row d-flex flex-wrap justify-content-between mb-2">
            <div class="form-group mb-sm-4 mb-1">
                <div class="form-check custom-checkbox ms-1">
                    <input type="checkbox" class="form-check-input" id="basic_checkbox_1" name="remember">
                    <label class="form-check-label" for="basic_checkbox_1">Remember my preference</label>
                </div>
            </div>
            <div class="form-group ms-2">
                <a class="text-hover" href="{{route('admin.password.reset')}}">Forgot Password?</a>
            </div>
        </div>
        <div class="text-center">
            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
        </div>
    </form>
</div>

@endsection
