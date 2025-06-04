@extends('admin.layouts.master')
@section('title', __('Withdrawal OTP'))

@section('page-title')
    <ol class="breadcrumb m-0">
        <li class="breadcrumb-item"><a href="javascript: void(0);">@lang('Admin')</a></li>
        <li class="breadcrumb-item active">@yield('title')</li>
    </ol>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6 col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="fw-bold"> Monnify OTP</h5>
                </div>
                <div class="card-body">
                   <p class="alert alert-info">Check Your Email For OTP to complete this withdrawal</p>
                    <form action="{{ route('admin.withdrawal.otp.process', $withdraw->id) }}" class="ajaxForm" method="post" >
                        @csrf
                        <input type="hidden" name="reference" value="{{$withdraw->reference}}">
                        <div class="form-group">
                            <label class="form-label">Withdrawal OTP</label>
                            <input name="authorizationCode" type="number" integer required autofocus class="form-control" placeholder="Authorization Code"value="" />
                        </div>
                        <button type="submit" class="btn w-100 btn-success">Confirm Withdrawal</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        .card-header {
            padding-bottom: 5px;
        }
    </style>
@endpush
@push('scripts')

@endpush
