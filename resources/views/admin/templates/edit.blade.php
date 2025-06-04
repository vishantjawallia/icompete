@extends('admin.layouts.master')
@section('title', 'Edit Template')

@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body table-responsive">
                    <table class="table-hover table table-sm">
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
                    <table class="table-hover table table-bordered">
                        <thead>
                            <tr>
                                <th>Short Code</th>
                                <th>Description</th>
                            </tr>
                        </thead>

                        <tbody class="list">
                            @foreach (get_setting('shortcodes') as $shortcode => $key)
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
                    <h5 class="fw-bold">{{ $template->name }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.notify.templates.update', $template->id) }}" class="ajaxForm" method="POST">
                        @csrf
                        <div class="form-group">
                            <label class="fw-bold my-auto">@lang('Status')</label>
                            <br>
                            <label class="jdv-switch jdv-switch-success">
                                <input type="checkbox" name="push_status" value="1" @if ($template->push_status == 1) checked @endif>
                                <span class="slider round"></span>
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="fw-bold">Notification Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" required placeholder="@lang('Email Title')" maxlength="50" name="title"
                                value="{{ $template->title }}" />
                        </div>
                        <div class="form-group">
                            <label class="fw-bold">@lang('Notification Message') <span class="text-danger">*</span></label>
                            <textarea name="message" rows="4" class="form-control form-control-lg" maxlength="300" placeholder="@lang('Your message using shortcodes')">{{ $template->message }}</textarea>
                        </div>
                        <div class="form-group text-end">
                            <button type="submit" class="btn btn-primary me-2 w-100">@lang('Update')</button>
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
@push('scripts')
<script src="{{ static_asset('admin/js/bootstrap-maxlength.js') }}"></script>
    <script>
        $('.form-control').maxlength({
            alwaysShow: true,
            threshold: 10,
            warningClass: "label label-info",
            limitReachedClass: "label label-danger",
            placement: 'top',
            separator: ' of ',
            postText: ' chars.'
        });
    </script>
@endpush
