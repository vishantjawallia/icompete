@extends('admin.layouts.master')

@section('title', 'General Settings')
@php
    $settings = get_setting();
@endphp
@section('content')

<div class="card">
    <div class="card-header h4">Website Information </div>
    <div class="card-body">
        <form action="{{ route('admin.settings.update') }}" method="post" class="row ajaxForm">
            @csrf
            <div class="form-group col-md-6">
                <label for="title" class="form-label">@lang('Website Name')</label>
                <input type="text" name="title" id="title" class="form-control" value="{{ $settings->title }}" placeholder="Enter website name">
            </div>

            <div class="form-group col-md-6">
                <label for="email" class="form-label">@lang('Website Email')</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ $settings->email }}" placeholder="Enter website email">
            </div>

            <div class="form-group col-md-6">
                <label for="support_email" class="form-label">@lang('Support Email')</label>
                <input type="email" name="support_email" id="support_email" class="form-control" value="{{ $settings->support_email }}" placeholder="Enter support email">
            </div>

            <div class="form-group col-md-6">
                <label for="admin_email" class="form-label">@lang('Admin Email')</label>
                <input type="email" name="admin_email" id="admin_email" class="form-control" value="{{ $settings->admin_email }}" placeholder="Enter admin email">
            </div>

            <div class="form-group col-md-6">
                <label for="phone" class="form-label">@lang('Website Phone')</label>
                <input type="tel" name="phone" id="phone" class="form-control" value="{{ $settings->phone }}" placeholder="Enter phone number">
            </div>

            <div class="form-group col-md-6">
                <label for="description" class="form-label">@lang('Website About')</label>
                <textarea name="description" id="description" rows="3" class="form-control" placeholder="Enter website description">{{ $settings->description }}</textarea>
            </div>

            <button class="btn btn-primary w-100" type="submit">Save Settings</button>
        </form>
    </div>
</div>
<div class="card">
    <div class="card-header h4">Logo/Image Settings</div>
    <div class="card-body">
        <form class="row" action="{{route('admin.settings.update')}}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="form-group col-lg-6">
                <label class="form-label">@lang('Site Logo')</label>
                <div class="col-sm-12 row">
                    <input type="file" class="form-control" name="logo" accept="image/*"/>
                    <img class="primage mt-2" src="{{ my_asset($settings->logo)}}" alt="Site Logo" >
                </div>
            </div>
            <div class="form-group col-lg-6">
                <label class="form-label">@lang('Favicon')</label>
                <div class="col-sm-12">
                    <input type="file" class="form-control" name="favicon" accept="image/*"/>
                    <img class="primage mt-2" src="{{ my_asset($settings->favicon)}}" alt="Favicon" >
                </div>
            </div>
            <div class="w-100">
                <button class="btn btn-primary w-100" type="submit">@lang('Update Setting')</button>
            </div>
        </form>
    </div>
</div>
{{-- Social Settings --}}
<div class="card">
    <div class="card-header h4">Social Links</div>
    <div class="card-body">
        <form class="row ajaxForm" action="{{route('admin.settings.update')}}" method="post" enctype="multipart/form-data">
            @csrf
                <div class="form-group col-md-6">
                    <label class="form-label">@lang('Facebook')</label>
                    <input name="facebook" type="text" class="form-control" value="{{ $settings->facebook }}" >
                </div>
                <div class="form-group col-md-6">
                    <label class="form-label">@lang('Twitter')</label>
                    <input name="twitter" type="text" class="form-control" value="{{ $settings->twitter }}" >
                </div>
                <div class="form-group col-md-6">
                    <label class="form-label">@lang('Tiktok')</label>
                    <input name="tiktok" type="text" class="form-control" value="{{ $settings->tiktok }}" >
                </div>
                <div class="form-group col-md-6">
                    <label class="form-label">@lang('Instagram')</label>
                    <input name="instagram" type="text" class="form-control" value="{{ $settings->instagram }}" >
                </div>
                <div class="form-group col-md-6">
                    <label class="form-label">@lang('Youtube')</label>
                    <input name="youtube" type="text" class="form-control" value="{{ $settings->youtube }}" >
                </div>
                {{-- <div class="form-group col-md-6">
                    <label class="form-label">@lang('Apple Appstore')</label>
                    <input name="ios_link" type="text" class="form-control" value="{{ $settings->ios_link }}" >
                </div>
                <div class="form-group col-md-6">
                    <label class="form-label">@lang('Google Playstore')</label>
                    <input name="android_link" type="text" class="form-control" value="{{ $settings->android_link }}" >
                </div> --}}
            <div class="form-group mb-0">
                <button class="btn btn-primary w-100" type="submit">Update Settings</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header h4">Currency Settings</div>
    <div class="card-body">
        <form class="row ajaxForm" action="{{route('admin.settings.update')}}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="form-group col-sm-4 ">
                <label class="form-label">Currency Symbol</label>
                <input type="text" class="form-control" name="currency" value="{{ $settings->currency }}" required placeholder="Currency Symbol"/>
            </div>
            <div class="form-group col-sm-4">
                <label class="form-label">Currency Code</label>
                <input type="text" class="form-control" name="currency_code" value="{{ $settings->currency_code }}" required placeholder="Currency Code"/>
            </div>
            <div class="form-group col-sm-4">
                <label class="form-label">Currency Rate</label>
                <input type="text" class="form-control" name="currency_rate" value="{{ $settings->currency_rate }}" required placeholder="Currency rate"/>
            </div>
            <div class="w-100">
                <button class="btn btn-primary w-100" type="submit">@lang('Update Setting')</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
@endpush
@push('styles')
<style>
    .primage {
        min-height: 80px;
        max-height: 120px !important;
        max-width: 150px !important;
        margin: 0;
    }
</style>
@endpush
