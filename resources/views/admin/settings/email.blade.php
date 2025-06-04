@extends('admin.layouts.master')
@section('title', "Email Settings")

@section('page-title')
<ol class="breadcrumb m-0">
    <li class="breadcrumb-item"><a href="javascript: void(0);">@lang('Admin')</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <h5 class="card-header fw-bold">Email Gateway</h5>
            <div class="card-body">
                <form action="{{route('admin.settings.store_settings')}}" method="post" class="">
                    @csrf
                    <div class="form-group">
                        <input type="hidden" name="types[]" value="email_gateway">
                        <select name="email_gateway" id="" class="form-select">
                            <option value="php" @if(sys_setting('email_gateway') == "php" )selected @endif>PHP Mail</option>
                            <option value="smtp" @if(sys_setting('email_gateway') == "smtp" )selected @endif>SMTP</option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <button class="btn btn-primary w-100 btn-md" type="submit">Update</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.settings.env_key') }}" class="ajaxForm" method="POST">
                    @csrf
                    <div class="form-group row">
                        <div class="col-lg-4">
                            <input type="hidden" name="types[]" value="MAIL_MAILER">
                            <label class="form-label">{{__('MAIL MAILER')}}</label>
                        </div>
                        <div class="col-lg-8">
                            <input type="text" class="form-control" name="MAIL_MAILER" value="{{  env('MAIL_MAILER') }}" placeholder="{{__('MAIL MAILER')}}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <input type="hidden" name="types[]" value="MAIL_HOST">
                        <div class="col-lg-4">
                            <label class="form-label">{{__('MAIL HOST')}}</label>
                        </div>
                        <div class="col-lg-8">
                            <input type="text" class="form-control" name="MAIL_HOST" value="{{  env('MAIL_HOST') }}" placeholder="{{__('MAIL HOST')}}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <input type="hidden" name="types[]" value="MAIL_PORT">
                        <div class="col-lg-4">
                            <label class="form-label">{{__('MAIL PORT')}}</label>
                        </div>
                        <div class="col-lg-8">
                            <input type="text" class="form-control" name="MAIL_PORT" value="{{  env('MAIL_PORT') }}" placeholder="{{__('MAIL PORT')}}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <input type="hidden" name="types[]" value="MAIL_USERNAME">
                        <div class="col-lg-4">
                            <label class="form-label">{{__('MAIL USERNAME')}}</label>
                        </div>
                        <div class="col-lg-8">
                            <input type="text" class="form-control" name="MAIL_USERNAME" value="{{  env('MAIL_USERNAME') }}" placeholder="{{__('MAIL USERNAME')}}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <input type="hidden" name="types[]" value="MAIL_PASSWORD">
                        <div class="col-lg-4    ">
                            <label class="form-label">{{__('MAIL PASSWORD')}}</label>
                        </div>
                        <div class="col-lg-8">
                            <input type="password" class="form-control" name="MAIL_PASSWORD" value="{{  env('MAIL_PASSWORD') }}" placeholder="{{__('MAIL PASSWORD')}}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <input type="hidden" name="types[]" value="MAIL_ENCRYPTION">
                        <div class="col-lg-4">
                            <label class="form-label">{{__('MAIL ENCRYPTION')}}</label>
                        </div>
                        <div class="col-lg-8">
                            <input type="text" class="form-control" name="MAIL_ENCRYPTION" value="{{  env('MAIL_ENCRYPTION') }}" placeholder="{{__('MAIL ENCRYPTION')}}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <input type="hidden" name="types[]" value="MAIL_FROM_ADDRESS">
                        <div class="col-lg-4">
                            <label class="form-label">{{__('MAIL FROM ADDRESS')}}</label>
                        </div>
                        <div class="col-lg-8">
                            <input type="text" class="form-control" name="MAIL_FROM_ADDRESS" value="{{  env('MAIL_FROM_ADDRESS') }}" placeholder="{{__('MAIL FROM ADDRESS')}}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <input type="hidden" name="types[]" value="MAIL_FROM_NAME">
                        <div class="col-lg-4">
                            <label class="form-label">{{__('MAIL FROM NAME')}}</label>
                        </div>
                        <div class="col-lg-8">
                            <input type="text" class="form-control" name="MAIL_FROM_NAME" value="{{  env('MAIL_FROM_NAME') }}" placeholder="{{__('MAIL FROM NAME')}}">
                        </div>
                    </div>
                    <div class="form-group mb-0 ">
                        <button class="btn btn-primary w-100" type="submit">{{__('Save')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <h5 class="card-header fw-bold">Test Email</h5>
            <div class="card-body pb-1">
                <form action="{{route('admin.newsletter.test')}}" class="mb-3 ajaxForm resetForm" method="post">
                    @csrf
                    <div class="form-group mb-2">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" required id="email">
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary text-end">Send Test Email</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="card">
            <h4 class="card-header py-2">{{__('Instruction')}}</h4>
            <div class="card-body">
                <b>{{ __('For Non-SSL') }}</b>
                <ul class="list-group">
                    <li class="list-group-item">{{__('Set Mail Host according to your server Mail Client Manual Settings')}}</li>
                    <li class="list-group-item">{{__("Set Mail port as 587")}}</li>
                    <li class="list-group-item">{{__("Set Mail Encryption as 'ssl' if you face issue with tls")}}</li>
                </ul>
                <br>
                <b>{{ __('For SSL') }}</b>
                <ul class="list-group">
                    <li class="list-group-item">{{__('Set Mail Host according to your server Mail Client Manual Settings')}}</li>
                    <li class="list-group-item">{{__('Set Mail port as 465')}}</li>
                    <li class="list-group-item">{{__('Set Mail Encryption as ssl')}}</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@endsection
