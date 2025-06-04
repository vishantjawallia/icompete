@extends('admin.layouts.master')
@section('title', $title)

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="fw-bold">{{$title}}</h5>
        <form action="" method="GET">
            <div class="input-group justify-content-end">
                <input type="search" name="search" class="form-control" placeholder="@lang('Search')" value="{{request()->search ?? ''}}" id="searchInput">
                <button class="btn btn-primary input-group-text" type="submit"><i class="far fa-search"></i></button>
            </div>
        </form>
    </div>
@if (request()->routeIs('admin.reports.login.ipHistory'))
    <a href="https://ipgeolocation.io/what-is-my-ip/{{ $ip }}" target="_blank"
        class="btn btn-primary">@lang('Lookup IP') {{ $ip }}</a>
@endif

    <div class="card-body table-responsive">
        <table class="table table-hover table-bordered search-table">
            <thead>
                <tr>
                    <th>@lang('User')</th>
                    <th>@lang('Date')</th>
                    <th>@lang('IP Address')</th>
                    <th>@lang('Location')</th>
                    {{-- <th>@lang('Browser | OS')</th> --}}
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>
                            <span class="fw-bold">{{ @$log->user->full_name ?? 'n/a'}}</span>
                            <br>
                            <span class="small">
                                <a class="text-primary" href="{{ route('admin.users.view', $log->user_id) }}"><span>@</span>{{ @$log->user->username ?? 'n/a' }}</a>
                            </span>
                        </td>

                        <td>
                            {{ show_datetime($log->created_at) }} <br> {{ ($log->created_at->diffForHumans()) }}
                        </td>

                        <td>
                            <span class="fw-bold">
                                <a class="text-primary"  href="{{ route('admin.reports.login.ipHistory', [$log->ip_address]) }}">{{ $log->ip_address }}</a>
                            </span>
                        </td>

                        <td>{{ __($log->city) }} <br> {{ __($log->country) }}</td>
                        {{-- <td>
                            {{ __($log->browser) }} <br> {{ __($log->os) }}
                        </td> --}}
                        <td><a class="btn btn-sm btn-danger delete-btn" href="{{ route('admin.reports.login.history.delete',$log->id) }}"><i class="fa fa-trash"></i></a></td>
                    </tr>
                @empty
                    <tr>
                        <td class="text-muted text-center" colspan="100%">No History found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <span class="m-2">
            {{$logs->links()}}
        </span>
    </div>
</div>
@endsection

@section('breadcrumb')
<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">@yield('title')</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Admin</a></li>
                    <li class="breadcrumb-item active">@yield('title')</li>
                </ol>
            </div>

        </div>
    </div>
</div>
<!-- end page title -->
@endsection
