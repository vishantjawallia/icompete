@extends('admin.layouts.master')

@section('title', 'Posts Settings')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="fw-bold">Community Feeds Settings</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.settings.store_settings') }}" method="post" class="ajaxForm">
                @csrf
                <div class="row">
                    <div class="col-md-6 col-lg-4">
                        <label class="form-label">Auto Approve Posts</label>
                        <br>
                        <label class="jdv-switch jdv-switch-success mb-2">
                            <input type="checkbox" onchange="updateSystem(this, 'post_approval')" @if (sys_setting('post_approval') == 1) checked @endif>
                            <span class="slider round"></span>
                        </label>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="form-group">
                            <input type="hidden" name="types[]" value="min_post_length">
                            <label class="form-label">Minimum Post Length (characters/words)</label>
                            <input name="min_post_length" type="number" required class="form-control" placeholder="Enter minimum post length"
                                value="{{ sys_setting('min_post_length') }}" />
                        </div>
                    </div>

                    <!-- Maximum Post Length -->
                    <div class="col-md-6 col-lg-4">
                        <div class="form-group">
                            <input type="hidden" name="types[]" value="max_post_length">
                            <label class="form-label">Maximum Post Length (characters/words)</label>
                            <input name="max_post_length" type="number" required class="form-control" placeholder="Enter maximum post length"
                                value="{{ sys_setting('max_post_length') }}" />
                        </div>
                    </div>

                    <!-- Minimum Comment Length -->
                    <div class="col-md-6 col-lg-4">
                        <div class="form-group">
                            <input type="hidden" name="types[]" value="min_comment_length">
                            <label class="form-label">Minimum Comment Length (characters/words)</label>
                            <input name="min_comment_length" type="number" required class="form-control" placeholder="Enter minimum comment length"
                                value="{{ sys_setting('min_comment_length') }}" />
                        </div>
                    </div>

                    <!-- Maximum Comment Length -->
                    <div class="col-md-6 col-lg-4">
                        <div class="form-group">
                            <input type="hidden" name="types[]" value="max_comment_length">
                            <label class="form-label">Maximum Comment Length (characters/words)</label>
                            <input name="max_comment_length" type="number" required class="form-control" placeholder="Enter maximum comment length"
                                value="{{ sys_setting('max_comment_length') }}" />
                        </div>
                    </div>

                </div>
                <button type="submit" class="btn w-100 btn-success">Save</button>
            </form>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 col-lg-4" hidden>
            <div class="card">
                <div class="card-header">
                    <h5 class="fw-bold">Auto Approve Comments</h5>
                </div>
                <div class="card-body">
                    <label class="jdv-switch jdv-switch-success mb-2">
                        <input type="checkbox" onchange="updateSystem(this, 'comment_approval')" @if (sys_setting('comment_approval') == 1) checked @endif>
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>
        </div>
    </div>
@endsection
