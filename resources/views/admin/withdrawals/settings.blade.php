@extends('admin.layouts.master')
@section('title', __('Withdrawal Settings'))

@section('page-title')
    <ol class="breadcrumb m-0">
        <li class="breadcrumb-item"><a href="javascript: void(0);">@lang('Admin')</a></li>
        <li class="breadcrumb-item active">@yield('title')</li>
    </ol>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="fw-bold"> Settings</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.settings.store_settings') }}" method="post" class="ajaxForm">
                @csrf
                <div class="row">
                    <div class="col-md-6 col-lg-4">
                        <label class="form-label">Withdrawal Status</label>
                        <br>
                        <label class="jdv-switch jdv-switch-success mb-2">
                            <input type="checkbox" onchange="updateSystem(this, 'withdrawal_status')" @if (sys_setting('withdrawal_status') == 1) checked @endif>
                            <span class="slider round"></span>
                        </label>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="form-group">
                            <input type="hidden" name="types[]" value="min_withdraw">
                            <label class="form-label">Minimum Withdrawal</label>
                            <div class="input-group">
                                <input name="min_withdraw" type="number" integer required class="form-control" placeholder="Min Withdrawal"
                                    value="{{ sys_setting('min_withdraw') }}" />
                                <div class="input-group-prepend">
                                    <span class="input-group-text py-2">{{ get_setting('currency') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="form-group">
                            <input type="hidden" name="types[]" value="max_withdraw">
                            <label class="form-label">Maximum Withdrawal</label>
                            <div class="input-group">
                                <input name="max_withdraw" type="number" integer required class="form-control" placeholder="Max Withdrawal"
                                    value="{{ sys_setting('max_withdraw') }}" />
                                <div class="input-group-prepend">
                                    <span class="input-group-text py-2">{{ get_setting('currency') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn w-100 btn-success">Save</button>
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
