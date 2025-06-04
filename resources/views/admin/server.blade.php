@extends('admin.layouts.master')
@section('title', __('Server Info'))

@section('page-title')
<ol class="breadcrumb m-0 float-end">
    <li class="breadcrumb-item"><a href="javascript: void(0);">@lang('Admin')</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
</ol>
@endsection

@section('content')
<div class="card">
    <h5 class="card-header fw-bold">@lang("Server Info")</h5>
    <div class="card-body">
        <ul class="list-group">
            <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                <span>@lang('PHP Version')</span>
                <span>{{ phpversion() }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                <span>@lang('Laravel Version')</span>
                <span>{{ app()->version() }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                <span>@lang('APP Debug')</span>
                <span>{{ ucwords((env("APP_DEBUG","false") == true) ? "True" : "False") }} </span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                <span>@lang('APP Environment')</span>
                <span>{{ ucwords(env("APP_ENV","Local")) }} </span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                <span>@lang('Timezone')</span>
                <span>{{ ucwords(env("TIMEZONE","Africa/Lagos")) }} </span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                <span>@lang('Server Software')</span>
                <span>{{ @$server['SERVER_SOFTWARE'] }}</span>
            </li >
            <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                <span>@lang('Server IP Address')</span>
                <span>{{ @$server['SERVER_ADDR'] }}</span>
            </li >
            <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                <span>@lang('Server Protocol')</span>
                <span>{{ @$server['SERVER_PROTOCOL'] }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                <span>@lang('HTTP Host')</span>
                <span>{{ @$server['HTTP_HOST'] }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                <span>@lang('Server Port')</span>
                <span>{{ @$server['SERVER_PORT'] }}</span>
            </li >
        </ul>
    </div>
</div>
<div class="card">
    <h5 class="card-header fw-bold">@lang('Server Requirements')</h5>
    <div class="card-body">
        <p>Make sure everything is checked so that you do not run into problems.</p>
        @foreach($results['extensions'] as $type => $extension)
            <div class="list-group {{ $loop->index == 0 ? 'mb-3 mt-2' : 'mt-2 mb-3 pt-2' }}">
                <div class="list-group-item">
                    <div class="row align-items-center">
                        <div class="col">
                            <span class="fw-bold">{{ mb_strtoupper($type) }}</span>
                            @if($type == 'php')
                                {{ config('system.php_version') }}+
                            @endif
                        </div>

                        <div class="col-auto d-flex align-items-center">
                            @if($type == 'php')
                                @if(version_compare(PHP_VERSION, config('system.php_version')) >= 0)
                                    <i class="far fa-check text-success"></i>
                                @else
                                    <i class="far fa-close text-danger"></i>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>

                @foreach($extension as $name => $enabled)
                    <div class="list-group-item ">
                        <div class="row align-items-center">
                            <div class="col">
                                <span class="fw-bold"> {{ $name }} </span>
                            </div>
                            <div class="col-auto d-flex align-items-center">
                                @if($enabled)
                                    <i class="far fa-check text-success"></i>
                                @else
                                    <i class="far fa-close text-danger"></i>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</div>
@endsection
