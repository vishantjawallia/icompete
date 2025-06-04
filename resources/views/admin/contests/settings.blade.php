@extends('admin.layouts.master')

@section('title', 'Contest Settings')

@section('content')
    <div class="row">
        <div class="col-md-6 col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="fw-bold">Contest Creation</h5>
                </div>
                <div class="card-body">
                    <label class="jdv-switch jdv-switch-success mb-2">
                        <input type="checkbox" onchange="updateSystem(this, 'contest_status')" @if (sys_setting('contest_status') == 1) checked @endif>
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="fw-bold">Auto Approve Contest</h5>
                </div>
                <div class="card-body">
                    <label class="jdv-switch jdv-switch-success mb-2">
                        <input type="checkbox" onchange="updateSystem(this, 'contest_approval')" @if (sys_setting('contest_approval') == 1) checked @endif>
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="fw-bold">Contest Deletion</h5>
                </div>
                <div class="card-body">
                    <label class="jdv-switch jdv-switch-success mb-2">
                        <input type="checkbox" onchange="updateSystem(this, 'contest_delete')" @if (sys_setting('contest_delete') == 1) checked @endif>
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h5 class="fw-bold">Entry Fee</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.settings.store_settings') }}" method="post" class="ajaxForm">
                @csrf
                <div class="row">
                    {{-- Free Entry --}}
                    <div class="col-md-6 col-lg-4">
                        <div class="form-group">
                            <input type="hidden" name="types[]" value="free_entry">
                            <label class="form-label">Free Entry</label>
                            <div class="input-group">
                                <input name="free_entry" type="number" integer required class="form-control" placeholder="Free Entry"
                                    value="{{ sys_setting('free_entry') }}" />
                                <div class="input-group-prepend">
                                    <span class="input-group-text py-2">coins</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Paid Entry --}}
                    <div class="col-md-6 col-lg-4">
                        <div class="form-group">
                            <input type="hidden" name="types[]" value="paid_entry">
                            <label class="form-label">Paid Entry</label>
                            <div class="input-group">
                                <input name="paid_entry" type="number" integer required class="form-control" placeholder="Paid Entry"
                                    value="{{ sys_setting('paid_entry') }}" />
                                <div class="input-group-prepend">
                                    <span class="input-group-text py-2">coins</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Exclusive Entry --}}
                    <div class="col-md-6 col-lg-4">
                        <div class="form-group">
                            <input type="hidden" name="types[]" value="exclusive_entry">
                            <label class="form-label">Exclusive Entry</label>
                            <div class="input-group">
                                <input name="exclusive_entry" type="number" integer required class="form-control" placeholder="Exclusive Entry"
                                    value="{{ sys_setting('exclusive_entry') }}" />
                                <div class="input-group-prepend">
                                    <span class="input-group-text py-2">coins</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn w-100 btn-success">Save</button>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h5 class="fw-bold">Voting Price</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.settings.store_settings') }}" method="post" class="ajaxForm">
                @csrf
                <div class="row">
                    {{-- Free Voting --}}
                    <div class="col-md-6 col-lg-4">
                        <div class="form-group">
                            <input type="hidden" name="types[]" value="free_voting">
                            <label class="form-label">Free Voting Price</label>
                            <div class="input-group">
                                <input name="free_voting" type="number" integer required class="form-control" placeholder="Free Voting Price"
                                    value="{{ sys_setting('free_voting') }}" />
                                <div class="input-group-prepend">
                                    <span class="input-group-text py-2">coins</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Paid Voting --}}
                    <div class="col-md-6 col-lg-4">
                        <div class="form-group">
                            <input type="hidden" name="types[]" value="paid_voting">
                            <label class="form-label">Paid Voting Price</label>
                            <div class="input-group">
                                <input name="paid_voting" type="number" integer required class="form-control" placeholder="Paid Voting Price"
                                    value="{{ sys_setting('paid_voting') }}" />
                                <div class="input-group-prepend">
                                    <span class="input-group-text py-2">coins</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Exclusive Voting --}}
                    <div class="col-md-6 col-lg-4">
                        <div class="form-group">
                            <input type="hidden" name="types[]" value="exclusive_voting">
                            <label class="form-label">Exclusive Voting Price</label>
                            <div class="input-group">
                                <input name="exclusive_voting" type="number" integer required class="form-control" placeholder="Exclusive Voting Price"
                                    value="{{ sys_setting('exclusive_voting') }}" />
                                <div class="input-group-prepend">
                                    <span class="input-group-text py-2">coins</span>
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
