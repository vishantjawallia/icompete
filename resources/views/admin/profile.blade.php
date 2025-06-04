@extends('admin.layouts.master')

@section('title', 'Update Profile')

@section('content')
<div class="card">
    <div class="card-body">
        <form action="" method="POST" class="row ajaxForm">
            @csrf
            <div class="form-group col-md-6">
                <label class="form-label" for="name">@lang('Full Name')</label>
                <input type="text" placeholder="@lang('Full Name')" id="name" name="name" class="form-control" value="{{$user->name ?? ''}}" required>
            </div>
            <div class="form-group col-md-6">
                <label class="form-label" for="email">@lang('Email Address')</label>
                <input type="text" placeholder="@lang('Email Address')" name="email" id="email" class="form-control" value="{{$user->email ?? ''}}" required>
            </div>
            <div class="form-group col-md-6">
                <label class="form-label" >@lang('Phone')</label>
                <input type="text" placeholder="@lang('Phone')" name="phone" class="form-control" value="{{$user->phone ?? ''}}">
            </div>
            <div class="form-group col-md-6">
                <label class="form-label">Password</label>
                <input class="form-control" type="password" name="password" placeholder="Leave empty if you dont want to update" />
            </div>
            <div class="col-12 mb-0">
                <button type="submit" class="btn btn-block btn-primary w-100">@lang('Update Details')</button>
            </div>
        </form>
    </div>
</div>
@endsection

