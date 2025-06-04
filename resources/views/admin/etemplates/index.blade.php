@extends('admin.layouts.master')
@section('title', 'Email Templates')

@section('content')
    <div class="card">
        <div class="card-header d-sm-flex">
            <h5 class="fw-bold">Email Templates</h5>
            {{-- seach form --}}
            <form action="#" method="get" onsubmit="return false;">
                <div class="input-group">
                    <input type="search" id="searchInput" name="search" value="{{ request('search') }}" class="form-control"
                        placeholder="Search">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </form>
        </div>
        <div class="card-body table-responsive">
            <table class="table-hover responsive-table table table-bordered search-table">
                <thead>
                    <tr>
                        <th>@lang('Name')</th>
                        <th>@lang('Subject')</th>
                        <th>Status</th>
                        <th>@lang('Action')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($templates as $template)
                        <tr>
                            <td data-label="Name" class="text-wrap">{{ $template->name }}</td>
                            <td data-label="Subject"><span class="text-wrap">{{ $template->email_subject }}</span> </td>
                            <td data-label="Status">{!! get_status($template->email_status) !!}</td>
                            <td data-label="Action">
                                <a class="btn btn-primary" href="{{ route('admin.email.templates.edit', $template->id) }}"
                                    data-bs-toggle="tooltip" title="Edit">
                                    <i class="fa fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="text-muted text-center" colspan="100%">No Email Template was found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection

@section('page-title')
    <ol class="breadcrumb m-0">
        <li class="breadcrumb-item"><a href="javascript: void(0);">@lang('Admin')</a></li>
        <li class="breadcrumb-item active">@yield('title')</li>
    </ol>
@endsection
