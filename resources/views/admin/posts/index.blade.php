@extends('admin.layouts.master')

@section('title', 'Commnunity Posts')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="fw-bold">All Posts</h5>
        <form action="" method="GET">
            <div class="input-group justify-content-end">
                <input type="search" name="search" class="form-control" placeholder="@lang('Search posts')" value="{{request()->search ?? ''}}" id="searchInput">
                <button class="btn btn-primary input-group-text" type="submit"><i class="far fa-search"></i></button>
            </div>
        </form>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-striped responsive-table search-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Content</th>
                    <th>Stats</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($posts as $post)
                    <tr>
                        <td data-label="@lang('User')">
                            <span class="fw-bold">{{ $post->user->full_name ?? "n/a" }}</span>
                            <br> @<a href="{{route('admin.users.view', $post->user_id)}}" class="text-primary">{{ $post->user->username ?? 'n/a' }}</a>
                        </td>
                        <td data-label="@lang('Content')" class="max-td">
                            <span class="d-inline-block text-truncate" style="max-width: 100%;">
                                {{ Str::limit($post->content, 50) }}
                            </span>
                        </td>
                        <td data-label="@lang('Stats')">
                            <i class="fas fa-thumbs-up text-primary"></i> {{$post->likes->count()}} <br>
                            <i class="fas fa-comment text-secondary"></i> {{$post->comments->count()}}
                        </td>
                        <td data-label="@lang('Date')">{{ show_datetime($post->created_at) }}</td>
                        <td data-label="@lang('Actions')">
                            <a href="{{route('admin.community.view', $post->id)}}" class="btn btn-sm btn-info">View</a>
                            <a href="{{route('admin.community.delete', $post->id)}}" class="btn btn-sm btn-danger delete-btn">Delete</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($posts->hasPages())
    <div class="card-footer text-end">{{ $posts->links() }}</div>
    @endif
</div>

@endsection

@section('page-title')
<ol class="breadcrumb m-0 float-end">
    <li class="breadcrumb-item"><a href="javascript: void(0);">Admin</a></li>
    <li class="breadcrumb-item active">Dashboard</li>
</ol>
@endsection
