@extends('admin.layouts.master')
@section('title', $title)

@section('page-title')
<ol class="breadcrumb m-0">
    <li class="breadcrumb-item"><a href="javascript: void(0);">@lang('Users')</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
</ol>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-sm-flex justify-content-between">
        <ul class="nav nav-tabs menu-tabs nav-pills">
            <li class="nav-item">
                <a class="nav-link @if($type == 'all' || "") active @endif" href="{{route('admin.users.index')}}">@lang('All Users') </a>
            </li>
            <li class="nav-item">
                <a class="nav-link @if($type == 'active') active @endif" href="{{route('admin.users.active')}}">@lang('Active')</a>
            </li>
            <li class="nav-item">
                <a class="nav-link @if($type == 'banned') active @endif" href="{{route('admin.users.banned')}}">@lang('Banned') </a>
            </li>
            <li class="nav-item">
                <a class="nav-link @if($type == 'everified') active @endif" href="{{route('admin.users.email.verified')}}">@lang('Email Verified')</a>
            </li>
            <li class="nav-item">
                <a class="nav-link @if($type == 'eunverified') active @endif" href="{{route('admin.users.email.unverified')}}">@lang('Email Unverified')</a>
            </li>
        </ul>

        <form action="" method="GET">
            <div class="input-group justify-content-end">
                <input type="search" name="search" class="form-control" placeholder="@lang('Search user')" value="{{request()->search ?? ''}}" id="search">
                <button class="btn btn-primary input-group-text" type="submit"><i class="far fa-search"></i></button>
            </div>
        </form>
    </div>
    <div class="card-body table-responsive">

        <table class="responsive-table table table-hover table-striped " >
            <thead class="thead-primary">
                <tr>
                    <th scope="col">@lang('Name')</th>
                    <th scope="col">@lang('Email')</th>
                    <th scope="col">@lang('Role')</th>
                    <th scope="col">@lang('Email')</th>
                    <th scope="col">@lang('Joined')</th>
                    <th scope="col">@lang('Status')</th>
                    <th scope="col">@lang('Action')</th>
                </tr>
            </thead>
            <tbody>
            @forelse($users as $item)
                <tr>
                    <td data-label="@lang('Name')">
                        <span class="fw-bold">{{ $item->full_name }}</span>
                        <br>
                        <span class="small">
                            <a href="{{ route('admin.users.view', $item->id) }}"><span>@</span>{{ $item->username }}</a>
                        </span>
                    </td>
                    <td data-label="@lang('Email')">
                        {{$item->email}}
                    </td>
                    <td data-label="@lang('Role')">
                       <span class="badge bg-info">{{$item->role}}</span>
                    </td>
                    <td data-label="@lang('Email')">
                       @if ($item->email_verify) <i class="far fa-check-circle text-success"></i> @else <i class="far fa-circle-xmark text-danger"></i> @endif
                    </td>
                    <td data-label="@lang('Joined')">
                        {{show_datetime($item->created_at)}}
                    </td>

                    <td data-label="@lang('Status')">
                        {!!getUserStatus($item->status)!!}
                    </td>
                    <td data-label="@lang('Action')">
                        <a href="{{route('admin.users.view', $item->id)}}" class="btn btn-outline-primary btn-sm" data-bs-toggle="tooltip" title="@lang('View User')">
                            <i class="far fa-eye"></i>
                        </a>
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="100%" class="text-center">@lang('No Data Found')</td>
                </tr>
            @endforelse
            </tbody>
        </table>

        @if($users->hasPages())
        <div class="py-1 paginate-footer">
            {{ paginateLinks($users) }}
        </div>
        @endif
    </div>
</div>
@endsection
