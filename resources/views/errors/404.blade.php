@extends('errors.layouts')
@section('title','Not Found')
@section('content')
<div class="error-page">
    <div class="error-inner text-center">
        <div class="dz-error" data-text="404">404</div>
        <h2 class="error-head mb-0"><i class="fa fa-exclamation-triangle text-warning me-2"></i>The page you were looking for is not found!</h2>
        <P>You may have mistyped the address or the page may have moved.</P>
        <a href="{{url('/')}}" class="btn btn-secondary">BACK TO HOMEPAGE</a>
    </div>
</div>
@endsection
