@extends('errors.layouts')
@section('title','419 Session Expired')
@section('content')
<div class="error-page">
    <div class="error-inner text-center">
        <div class="dz-error" data-text="419">419</div>
        <h2 class="error-head mb-0"><i class="fa fa-clock text-warning me-2"></i>Session Expired!</h2>
        <p>Your session has expired. Please go back and try again.</p>
        <a href="{{url('/')}}" class="btn btn-secondary">BACK TO HOMEPAGE</a>
    </div>
</div>
@endsection
