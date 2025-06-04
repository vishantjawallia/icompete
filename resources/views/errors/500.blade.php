@extends('errors.layouts')
@section('title','Internal Server Error')
@section('content')
<div class="error-page">
    <div class="error-inner text-center">
        <div class="dz-error" data-text="500">500</div>
        <h2 class="error-head mb-0"><i class="fa fa-bug text-danger me-2"></i>Internal Server Error!</h2>
        <p>Oops! Something went wrong on our end. Please try again later.</p>
        <a href="{{url('/')}}" class="btn btn-secondary">BACK TO HOMEPAGE</a>
    </div>
</div>
@endsection
