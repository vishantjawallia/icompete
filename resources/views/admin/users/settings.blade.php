@extends('admin.layouts.master')
@section('title', __('User Settings'))

@section('page-title')
    <ol class="breadcrumb m-0">
        <li class="breadcrumb-item"><a href="javascript: void(0);">@lang('Admin')</a></li>
        <li class="breadcrumb-item active">@yield('title')</li>
    </ol>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6 col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="fw-bold">Affiliate Settings</h5>
                </div>
                <div class="card-body">
                    <label class="jdv-switch jdv-switch-success mb-2">
                        <input type="checkbox" onchange="updateSystem(this, 'is_affiliate')" @if (sys_setting('is_affiliate') == 1) checked @endif>
                        <span class="slider round"></span>
                    </label>

                    <form action="{{ route('admin.settings.store_settings') }}" method="post" class="ajaxForm">
                        @csrf
                        <div class="form-group">
                            <input type="hidden" name="types[]" value="referral_bonus">
                            <label class="form-label">Referral Bonus</label>
                            <div class="input-group">
                                <input name="referral_bonus" type="number" integer required class="form-control" placeholder="referral percentage"
                                    value="{{ sys_setting('referral_bonus') }}" />
                                <div class="input-group-prepend">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn w-100 btn-success">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <h5 class="card-header fw-bold">Rejected Username</h5>
        <div class="card-body">
            <form action="{{ route('admin.settings.update') }}" method="post" class="ajaxForm">
                @csrf
                <div class="form-group">
                    <label for="description" class="form-label">@lang('Bad Usernames') (Comma separated)</label>
                    <textarea name="rejected_usernames" id="description" rows="6" class="form-control" placeholder="Bad Usernames">{{ get_setting('rejected_usernames') }}</textarea>
                </div>
                <button class="btn btn-primary w-100" type="submit">Save Settings</button>
            </form>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .card-header {
            padding-bottom: 5px;
        }
    </style>
@endpush
@push('scripts')
    <script>
        function updateSystem(el, name) {
            if ($(el).is(':checked')) {
                var value = 1;
            } else {
                var value = 0;
            }
            $.post('{{ route('admin.settings.sys_settings') }}', {
                _token: '{{ csrf_token() }}',
                name: name,
                value: value
            }, function(data) {
                if (data == '1') {
                    toastr.success('Settings updated successfully', 'Success')
                } else {
                    toastr.error('Something went wrong', 'Error')
                }
            });
        }
    </script>
@endpush
