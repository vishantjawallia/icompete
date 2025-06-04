@extends('errors.layouts')
@section('title','Method Not Allowed')
@section('content')
<div class="error-page">
    <div class="error-inner text-center">
        <div class="dz-error" data-text="405">405</div>
        <h2 class="error-head mb-0"><i class="fa fa-ban text-danger me-2"></i>Method Not Allowed!</h2>
        <p>The HTTP method used is not allowed for this resource.</p>
        <a href="{{url('/')}}" class="btn btn-secondary">BACK TO HOMEPAGE</a>
    </div>
</div>
@endsection
