@extends('admin.layouts.master')
@section('title', "Payment Settings")

@section('page-title')
<ol class="breadcrumb m-0">
    <li class="breadcrumb-item"><a href="javascript: void(0);">@lang('Settings')</a></li>
    <li class="breadcrumb-item active">Payment</li>
</ol>
@endsection
@section('content')
<div class="row">
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 fw-bold ">Paypal Payment</h5>
            </div>
            <div class="card-body">
                <div class="form-group row">
                    <div class="col-9">
                        <label class="form-label">Enable Paypal</label>
                    </div>
                    <div class="col-3">
                        <label class="jdv-switch jdv-switch-success mb-0">
                            <input type="checkbox"  onchange="updateSystem(this, 'paypal_payment')" @if(sys_setting('paypal_payment') == 1) checked @endif>
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 fw-bold ">Flutterwave Payment</h5>
            </div>
            <div class="card-body">
                <div class="form-group row">
                    <div class="col-9">
                        <label class="form-label">Enable Flutterwave</label>
                    </div>
                    <div class="col-3">
                        <label class="jdv-switch jdv-switch-success mb-0">
                            <input type="checkbox" onchange="updateSystem(this, 'flutterwave_payment')" @if(sys_setting('flutterwave_payment') == 1) checked @endif>
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card ">
            <div class="card-header">
                <h5 class="fw-bold mb-0">{{__('Paypal Credential')}}</h5>
            </div>
            <form action="{{ route('admin.settings.env_key') }}" method="POST" class=" ajaxForm">
                @csrf
                <div class="card-body">
                    <input type="hidden" name="payment_method" value="paypal">
                    <div class="form-group">
                        <input type="hidden" name="types[]" value="PAYPAL_CLIENT_ID">
                        <label class="form-label">{{__('Paypal Client Id')}}</label>
                        <input type="text" class="form-control" name="PAYPAL_CLIENT_ID" value="{{  env('PAYPAL_CLIENT_ID') }}" placeholder="Paypal Client ID" required>
                    </div>
                    <div class="form-group">
                        <input type="hidden" name="types[]" value="PAYPAL_CLIENT_SECRET">
                        <label class="form-label">{{__('Paypal Client Secret')}}</label>
                        <input type="text" class="form-control" name="PAYPAL_CLIENT_SECRET" value="{{  env('PAYPAL_CLIENT_SECRET') }}" placeholder="Paypal Client Secret" required>
                    </div>
                    <div class="mt-2">
                        <button class="btn btn-primary w-100" type="submit">{{__('Save')}}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 fw-bold ">Flutterwave Credential</h5>
            </div>
            <form action="{{ route('admin.settings.env_key') }}" method="POST" class=" ajaxForm">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <input type="hidden" name="types[]" value="FLUTTERWAVE_PUBLIC">
                        <label class="form-label">Flutterwave Public Key</label>
                        <input type="text" class="form-control" name="FLUTTERWAVE_PUBLIC" value="{{  env('FLUTTERWAVE_PUBLIC') }}" placeholder="Flutterwave Public Key" required>
                    </div>
                    <div class="form-group">
                        <input type="hidden" name="types[]" value="FLUTTERWAVE_SECRET">
                        <label class="form-label">Flutterwave Secret Key</label>
                        <input type="text" class="form-control" name="FLUTTERWAVE_SECRET" value="{{  env('FLUTTERWAVE_SECRET') }}" placeholder="Flutterwave Secret Key" required>
                    </div>
                    <div class="form-group">
                        <input type="hidden" name="types[]" value="FLUTTERWAVE_HASH">
                        <label class="form-label">Flutterwave Encryption Key</label>
                        <input type="text" class="form-control" name="FLUTTERWAVE_HASH" value="{{  env('FLUTTERWAVE_HASH') }}" placeholder="Flutterwave Encryption Key" required>
                    </div>
                    <button class="btn btn-primary mt-2 w-100" type="submit">{{__('Save')}}</button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-12 mt-3">
        <div class="card">
            <div class="card-header">
                <h5 class="fw-bold">Webhook Urls</h5>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Flutterwave</label>
                    <input type="text"class="form-control" placeholder="{{route('flutter.webhook')}}" value="{{route('flutter.webhook')}}">
                </div>
                <div class="form-group">
                    <label class="form-label">Paypal</label>
                    <input type="text"class="form-control" placeholder="{{route('paypal.webhook')}}" value="{{route('paypal.webhook')}}">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h5 class="fw-bold">Currency Settings</h5> 
    </div>
    <div class="card-body">
        <form class="row ajaxForm" action="{{route('admin.settings.update')}}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="form-group col-sm-4 ">
                <label class="form-label">Currency Symbol</label>
                <input type="text" class="form-control" name="currency" value="{{get_setting('currency')}}" required placeholder="Currency Symbol"/>
            </div>
            <div class="form-group col-sm-4">
                <label class="form-label">Currency Code</label>
                <input type="text" class="form-control" name="currency_code" value="{{get_setting('currency_code')}}" required placeholder="Currency Code"/>
            </div>
            <div class="form-group col-sm-4">
                <label class="form-label">Currency Rate</label>
                <input type="text" class="form-control" name="currency_rate" value="{{get_setting('currency_rate')}}" required placeholder="Currency rate"/>
            </div>
            <div class="w-100">
                <button class="btn btn-primary w-100" type="submit">@lang('Update Setting')</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function updateSystem(el, name){
        if($(el).is(':checked')){
            var value = 1;
        }
        else{
            var value = 0;
        }
        $.post('{{ route('admin.settings.sys_settings') }}', {_token:'{{ csrf_token() }}', name:name, value:value}, function(data){
            if(data == '1'){
                toastr.success('Settings updated successfully', 'Success')
            }
            else{
                toastr.error('Something went wrong', 'Error')
            }
        });
    }
</script>
@endpush
