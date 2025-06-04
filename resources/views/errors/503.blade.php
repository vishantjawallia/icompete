@extends('errors.layouts')
@section('title','Maintenance Mode')
@section('content')
<div class="error-page">
    <div class="error-inner text-center">
        <div class="dz-error" data-text="503">503</div>
        <h2 class="error-head mb-0"><i class="fa fa-cogs text-warning me-2"></i>Service Unavailable!</h2>
        <p>We are currently performing maintenance. Please try again later.</p>
        {{-- <a href="{{url('/')}}" class="btn btn-secondary">BACK TO HOMEPAGE</a> --}}
    </div>
</div>
@endsection
