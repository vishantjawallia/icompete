@extends('admin.layouts.auth')

@section('title', 'Reset Password')
@section('content')
<div class="auth-form">
    <div class="text-center mb-3">
        <a href="{{route('admin.login')}}">
            <img src="{{my_asset(get_setting('logo'))}}" alt="" style="width: 90px;">
        </a>
    </div>
    <h4 class="text-center mb-4">Forgot Password</h4>
    <form action="{{route('admin.password.reset')}}" class="ajaxForm autoR" method="POST">
        @csrf
        <div class="mb-3">
            <label class="mb-1 form-label">Email Address</label>
            <input type="email" name="email" value="{{old('email')}}" required class="form-control" placeholder="Email Address">
        </div>

        <div class="text-center">
            <button type="submit" class="btn btn-primary btn-block">Submit</button>
        </div>
    </form>

</div>
@endsection
