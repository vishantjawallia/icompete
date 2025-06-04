@extends('admin.layouts.master')
@section('title') @lang('Newsletter') @stop
@section('content')
    <div class="card">
        {{-- <h5 class="card-header fw-bold"> </h5> --}}
        <form action="{{ route('admin.newsletter.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-md-6">
                        <label class="form-label" for="user-emails">@lang('Send Email to')</label>
                        <br>
                        <div class="btn-group gap-2" role="group">
                            <label class="btn btn-primary">
                                <input type="checkbox" id="contestant-emails" name="contestants" value="1" autocomplete="off">
                                <span>@lang('Contestants')</span>
                            </label>
                            <label class="btn btn-primary">
                                <input type="checkbox" id="voter-emails" name="voters" value="1" autocomplete="off">
                                <span>@lang('Voters')</span>
                            </label>
                            <label class="btn btn-primary">
                                <input type="checkbox" id="organizer-emails" name="organizers" value="1" autocomplete="off">
                                <span>@lang('Organizers')</span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group col-md-6">
                        <label class="form-label" for="date">@lang('Send Date')</label>
                        <input type="text" class="form-control" name="date" id="dtpicker" placeholder="Date" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="name">@lang('Other Emails') (comma separated)</label>
                    <textarea class="form-control" name="other_emails" id="" rows="3" placeholder="Other Emails"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label" for="subject">@lang('Newsletter Subject')</label>
                    <input type="text" class="form-control" name="subject" id="subject" placeholder="Subject" required>
                </div>
                <div class="form-group">
                    <label class="form-label">@lang('Newsletter Content')</label>
                    <textarea class="form-control text-editor" name="message" rows="4" placeholder="Mail Body"> </textarea>
                </div>
                <div class="form-group mb-0 text-end">
                    <button class="btn btn-primary w-100 btn-md" type="submit">@lang('Schedule')</button>
                </div>
            </div>
        </form>
    </div>
@endsection
@push('styles')
    <link href="{{ static_asset('admin/vendors/flatpickr/flatpickr.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ static_asset('admin/vendors/summernote/summernote-lite.min.css') }}" rel="stylesheet" type="text/css" />
@endpush
@push('scripts')
    <script src="{{ static_asset('admin/vendors/summernote/summernote-lite.min.js') }}"></script>
    <!--Wysiwig js-->
    <script>
        $(document).ready(function() {
            $('.text-editor').summernote({
                tabsize: 2,
                height: 300,
            });
        });

        flatpickr("#dtpicker", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            disableMobile: true,
            minDate: "today",
        });
    </script>
@endpush
