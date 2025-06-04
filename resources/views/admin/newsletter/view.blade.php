@extends('admin.layouts.master')
@section('title') @lang('Edit Newsletter') @stop
@section('content')
    <div class="card">
        <h5 class="card-header fw-bold">@lang('Edit Newsletter') </h5>
        <form action="" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-md-6">
                        <label class="form-label" for="user-emails">@lang('Send Email to')</label>
                        <br>
                        <div class="btn-group gap-2" role="group">
                            <label class="btn btn-primary">
                                <input type="checkbox" id="contestant-emails" name="contestants" value="1" autocomplete="off" @if ($nl->contestants) checked @endif>
                                <span>@lang('Contestants')</span>
                            </label>
                            <label class="btn btn-primary">
                                <input type="checkbox" id="voter-emails" name="voters" value="1" autocomplete="off" @if ($nl->voters) checked @endif>
                                <span>@lang('Voters')</span>
                            </label>
                            <label class="btn btn-primary">
                                <input type="checkbox" id="organizer-emails" name="organizers" value="1" autocomplete="off" @if ($nl->organizers) checked @endif>
                                <span>@lang('Organizers')</span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group col-md-6">
                        <label class="form-label" for="date">@lang('Send Date')</label>
                        <input type="text" class="form-control" name="date" id="dtpicker" value="{{ $nl->date }}" placeholder="Date" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="name">@lang('Other Emails') (comma separated)</label>
                    <textarea class="form-control" name="other_emails" id="" rows="3" placeholder="Other Emails">{{ $nl->other_emails }}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label" for="subject">@lang('Newsletter Subject')</label>
                    <input type="text" class="form-control" name="subject" id="subject" value="{{ $nl->subject }}" placeholder="Subject" required>
                </div>
                <div class="form-group">
                    <label class="form-label">@lang('Newsletter Content')</label>
                    <textarea class="form-control text-editor" name="message" id="tiny1" rows="4" placeholder="Mail Body">{{ $nl->message }}</textarea>
                </div>
                <div class="form-group mb-0 text-end">
                    <button class="btn btn-primary w-100 btn-md" type="submit">@lang('Update')</button>
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
    <script>
        $(document).ready(function() {
            $('.text-editor').summernote({
                tabsize: 2,
                height: 300
            });
        });
    </script>
    <script>
        $("#dtpicker").flatpickr({
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            disableMobile: true,
        });
    </script>
@endpush
