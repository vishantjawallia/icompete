@extends('admin.layouts.master')
@section('title', "Newsletter")

@section('page-title')
<ol class="breadcrumb m-0">
    <li class="breadcrumb-item"><a href="javascript: void(0);">@lang('Admin')</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
</ol>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5 class="fw-bold">Newsletters</h5>
        <a class="btn btn-sm btn-primary" href="{{route('admin.newsletter.add')}}"> <i class="far fa-plus"></i> Send Emails</a>
    </div>

    <div class="card-body table-responsive">
        <table class="responsive-table table table-bordered table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Subject</th>
                    <th>Send To</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($nls as $item)
                <tr>
                    <td data-label="ID">{{$loop->iteration}}</td>
                    <td data-label="Subject">{{$item->subject}}</td>
                    <td data-label="Send To">
                        @if($item->organizers == 1) <span class="badge bg-info badge-sm">Organizers</span>  @endif
                        @if($item->contestants == 1) <span class="badge bg-info badge-sm">Contestants</span>  @endif
                        @if($item->voters == 1) <span class="badge bg-info badge-sm">Voters</span>  @endif
                        @if($item->other_emails != null) <span class="badge bg-info badge-sm">others</span>  @endif
                    </td>
                    <td data-label="Date">{{$item->date}}</td>
                    <td data-label="Status">
                        @if($item->status == 1)
                            <span class="badge bg-success">sent</span>
                        @else
                            <span class="badge bg-warning">scheduled</span>
                        @endif
                    </td>
                    <td data-label="Actions">
                        <a class="btn btn-light btn-sm" type="button" href="{{route('admin.newsletter.edit', $item->id)}}" >
                            <i class="far fa-edit"></i>
                        </a>
                        <a class="btn btn-danger btn-sm delete-btn" type="button" href="{{route('admin.newsletter.delete', $item->id)}}" >
                            <i class="far fa-trash"></i>
                        </a>
                        @if ($item->status != 1)
                        <a class="btn btn-success btn-sm" type="button" href="{{route('admin.newsletter.send', $item->id)}}" >
                            <i class="far fa-envelope"></i> Send Now
                        </a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

    </div>
</div>
@endsection
