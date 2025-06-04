@extends('admin.layouts.master')
@section('title', 'Edit Template')

@section('content')
<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body table-responsive">
                <table class="table-hover table table-sm" >
                    <thead>
                        <tr>
                            <th>Short Code</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody class="list">
                        @forelse($template->shortcodes as $shortcode => $key)
                        <tr>
                            <td>@php echo "{". $key ."}"  @endphp</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $key) )}}</td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="100%" class="text-muted text-center">@lang('No shortcode available')</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <h5 class="fw-bold">Default Shortcodes</h5>
                <table class="table-hover table table-sm" >
                    <thead>
                        <tr>
                            <th>Short Code</th>
                            <th>Description</th>
                        </tr>
                    </thead>

                    <tbody class="list">
                        @foreach(get_setting('shortcodes') as $shortcode => $key)
                            <tr>
                                <td>@php echo "{". $shortcode ."}"  @endphp</td>
                                <td>{{ $key }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="fw-bold">{{$template->name}}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.email.templates.update', $template->id) }}" class="ajaxForm" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="fw-bold my-auto">@lang('Status')</label>
                        <br>
                        <label class="jdv-switch jdv-switch-success">
                            <input type="checkbox" name="email_status" value="1" @if($template->email_status == 1) checked @endif >
                            <span class="slider round"></span>
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="fw-bold">Email Subject <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg" placeholder="@lang('Email subject')" name="email_subject" value="{{ $template->email_subject }}"/>
                    </div>
                    <div class="form-group">
                        <label class="fw-bold">Email Content <span class="text-danger">*</span></label>
                        <textarea name="email_content" rows="4" class="form-control form-control-lg" id="tiny1" placeholder="@lang('Your message using shortcodes')">{{ $template->email_content }}</textarea>
                    </div>
                    <div class="form-group text-end">
                        <button type="submit" class="btn btn-primary me-2 w-100">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('page-title')
<ol class="breadcrumb m-0">
    <li class="breadcrumb-item"><a href="javascript: void(0);">@lang('Admin')</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
</ol>
@endsection

@push('styles')
    <link href="{{ static_asset('admin/vendors/summernote/summernote-lite.min.css') }}" rel="stylesheet" type="text/css" />
@endpush

@push('scripts')
    <script src="{{ static_asset('admin/vendors/summernote/summernote-lite.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#tiny1').summernote({
                height: 300
            });
        });
    </script>
@endpush
