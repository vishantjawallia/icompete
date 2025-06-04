@extends('admin.layouts.master')
@section('title', 'User Details')

@section('page-title')
    <ol class="breadcrumb m-0">
        <li class="breadcrumb-item"><a href="javascript: void(0);">@lang('Admin')</a></li>
        <li class="breadcrumb-item active">@yield('title')</li>
    </ol>
@endsection

@section('content')

<div class="card profile-overview">
    <div class="card-body d-flex">
        <div class="clearfix">
            <div class="d-inline-block position-relative me-sm-4 me-3 mb-3 mb-lg-0">
                <img src="{{my_asset($user->image)}}" alt="" class="rounded-4 profile-avatar">
                <span class="fa fa-circle border border-3 border-white text-success position-absolute bottom-0 end-0 rounded-circle"></span>
            </div>
        </div>
        <div class="clearfix d-xl-flex flex-grow-1">
            <div class="clearfix pe-md-5">
                <h3 class="fw-semibold mb-1">{{$user->full_name}} </h3>
                <ul class="d-flex flex-wrap fs-6 align-items-center">
                    <li class="me-3 d-inline-flex align-items-center badge py-1 bg-info"><i class="las la-user me-1 fs-18"></i>{{$user->role}}</li>
                    <li class="me-3 d-inline-flex align-items-center text-wrap"><i class="las la-user me-1 fs-18"></i>{{$user->username}}</li>
                    <li class="me-3 d-inline-flex align-items-center text-wrap"><i class="las la-envelope me-1 fs-18"></i>{{$user->email}}</li>
                    <li class="me-3 d-inline-flex align-items-center text-wrap"><i class="las la-phone me-1 fs-18"></i>{{$user->phone}}</li>
                </ul>
                <div class="d-md-flex d-none flex-wrap">
                    <div class="border outline-dashed rounded p-2 d-flex align-items-center me-3 mt-3">
                        <div class="avatar avatar-md style-1 bg-primary-light text-primary rounded d-flex align-items-center justify-content-center">
                            <i class="las la-coins 2x"></i>
                        </div>
                        <div class="clearfix ms-2">
                            <h3 class="mb-0 fw-semibold lh-1">{{format_number($user->coins->balance ?? 0)}}</h3>
                            <span class="fs-14">Coin Balance</span>
                        </div>
                    </div>
                    <div class="border outline-dashed rounded p-2 d-flex align-items-center me-3 mt-3">
                        <div class="avatar avatar-md style-1 bg-primary-light text-primary rounded d-flex align-items-center justify-content-center">
                            <i class="las la-dollar-sign 2x"></i>
                        </div>
                        <div class="clearfix ms-2">
                            <h3 class="mb-0 fw-semibold lh-1">{{format_price($user->pendingWithdrawals()->sum('amount'))}}</h3>
                            <span class="fs-14">Pending Withdrawals</span>
                        </div>
                    </div>
                    <div class="border outline-dashed rounded p-2 d-flex align-items-center me-3 mt-3">
                        <div class="avatar avatar-md style-1 bg-primary-light text-primary rounded d-flex align-items-center justify-content-center">
                            <i class="las la-wallet 2x"></i>
                        </div>
                        <div class="clearfix ms-2">
                            <h3 class="mb-0 fw-semibold lh-1 text-danger">{{format_number($user->coinTransaction()->whereType('debit')->sum('coins'))}}</h3>
                            <span class="fs-14">Spent Coins</span>
                        </div>
                    </div>
                    <div class="border outline-dashed rounded p-2 d-flex align-items-center me-3 mt-3">
                        <div class="avatar avatar-md style-1 bg-primary-light text-primary rounded d-flex align-items-center justify-content-center">
                            <i class="las la-wallet 2x"></i>
                        </div>
                        <div class="clearfix ms-2">
                            <h3 class="mb-0 fw-semibold lh-1">{{format_number($user->coinTransaction()->whereType('credit')->sum('coins'))}}</h3>
                            <span class="fs-14">Earned Coins</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="clearfix mt-3 mt-xl-0 ms-auto d-flex flex-column col-xl-3">

                {{-- chart?? --}}
                {{-- <div class="mt-auto d-flex align-items-center">
                    <div class="clearfix me-3">
                        <span class="fw-medium text-black d-block mb-1">Progress</span>
                        <p class="mb-0 d-flex">
                            <span class="text-success me-1">+3.50%</span>
                        </p>
                    </div>
                    <div id="chartProfileProgress"></div>
                </div> --}}
            </div>
        </div>
    </div>
    <div class="card-footer py-0 d-flex flex-wrap justify-content-between align-items-center d-none" >
        <ul class="nav nav-underline nav-underline-primary nav-underline-text-dark nav-underline-gap-x-0" id="tabMyProfileBottom" role="tablist">
            <li class="nav-item ms-1" role="presentation">
                <a href="#" class="nav-link py-3 border-3 text-black active">Overview</a>
            </li>
            <li class="nav-item ms-1" role="presentation">
                <a href="#" class="nav-link py-3 border-3 text-black">Settings</a>
            </li>
        </ul>
    </div>
</div>

{{-- Tab navigation --}}
{{-- <div class="card">
    <div class="card-body">
        @livewire('user-details', ['user' => $user])
    </div>
</div> --}}

    <div class="row">
        <div class="col-12">
            <div class="card p-2">
                <ul class="d-flex flex-wrap gap-1" style="list-style: none">
                    <li class="flex-grow-1 flex-shrink-0">
                        <a class="d-block btn btn-primary bal-btn" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#addSubModal" data-act="add">
                            <i class="fas fa-plus-circle"></i>Add Balance
                        </a>
                    </li>
                    <li class="flex-grow-1 flex-shrink-0">
                        <a class="d-block btn btn-primary bal-btn" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#addSubModal" data-act="sub">
                            <i class="fas fa-minus-circle"></i>
                            Substract Balance
                        </a>
                    </li>
                    <li class="flex-grow-1 flex-shrink-0">
                        <a class="d-block btn btn-primary" href="{{route('admin.reports.notifications')}}?search={{$user->id}}">
                            <i class="fas fa-bell"></i> Notifiactions </a>
                    </li>
                    <li class="flex-grow-1 flex-shrink-0">
                        <a class="d-block btn btn-primary" href="{{route('admin.reports.login.history')}}?search={{$user->id}}">
                            <i class="fas fa-list-alt"></i> Login History </a>
                    </li>
                    <li class="flex-grow-1 flex-shrink-0">
                        @if ($user->status == "active")
                            <a class="d-block btn btn-primary confirmBtn" data-question="Do you want to ban this user?"
                                data-action="{{ route('admin.users.ban', $user->id) }}" href="javascript:void(0)"> <i class="fas fa-ban"></i> Ban User </a>
                        @else
                            <a class="d-block btn btn-primary confirmBtn" data-question="Do you want to unban this user?"
                                data-action="{{ route('admin.users.unban', $user->id) }}" href="javascript:void(0)"> <i class="fas fa-check"></i> Unban User </a>
                        @endif
                    </li>
                    <li class="flex-grow-1 flex-shrink-0">
                        <a class="d-block btn btn-primary" href="#" data-bs-toggle="modal" data-bs-target="#sendEmail"><i class="fas fa-paper-plane"></i> Send Email
                        </a>
                    </li>
                    <li class="flex-grow-1 flex-shrink-0">
                        <a class="d-block btn btn-primary" href="#" data-bs-toggle="modal" data-bs-target="#sendNotify"><i class="fas fa-bell"></i> Send Notification
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="row gy-2 pb-2 gx-2">
                <div class="col-6 col-sm-12 col-md-6 col-lg-12 col-xl-12">
                    <a href="#">
                        <div class="card mb-1">
                            <div class="card-body">
                                <div class="row align-items-center mb-0">
                                    <div class="col">
                                        <h6 class="m-b-5">Coin Balance</h6>
                                        <h3 class="m-b-0">{{format_number($user->coins->balance ?? 0)}}</h3>
                                    </div>
                                    <div class="col-auto">
                                        <i class="text-primary fas fa-coins"></i> <!-- Updated icon for coins -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-6 col-sm-12 col-md-6 col-lg-12 col-xl-12">
                    <a href="{{route('admin.withdrawal.history')}}?search={{$user->id}}&type=pending">
                        <div class="card mb-1">
                            <div class="card-body">
                                <div class="row align-items-center mb-0">
                                    <div class="col">
                                        <h6 class="m-b-5">Pending Withdrawals</h6>
                                        <h3 class="m-b-0">{{format_price($user->pendingWithdrawals()->sum('amount'))}}</h3>
                                    </div>
                                    <div class="col-auto">
                                        <i class="text-warning fas fa-arrow-down"></i> <!-- Updated icon for withdrawals -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-6 col-sm-12 col-md-6 col-lg-12 col-xl-12">
                    <a href="{{route('admin.coin.transactions')}}?search={{$user->id}}&type=debit">
                        <div class="card mb-1">
                            <div class="card-body">
                                <div class="row align-items-center mb-0">
                                    <div class="col">
                                        <h6 class="m-b-5">Spent</h6>
                                        <h3 class="m-b-0">{{($user->coinTransaction()->whereType('debit')->sum('coins'))}}</h3>
                                    </div>
                                    <div class="col-auto">
                                        <i class="text-danger fas fa-wallet"></i> <!-- Updated icon for spending -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-6 col-sm-12 col-md-6 col-lg-12 col-xl-12">
                    <a href="{{route('admin.coin.transactions')}}?search={{$user->id}}&type=credit">
                        <div class="card mb-1">
                            <div class="card-body">
                                <div class="row align-items-center mb-0">
                                    <div class="col">
                                        <h6 class="m-b-5">Earned</h6>
                                        <h3 class="m-b-0">{{($user->coinTransaction()->whereType('credit')->sum('coins'))}}</h3>
                                    </div>
                                    <div class="col-auto">
                                        <i class="text-success fas fa-hand-holding-usd"></i> <!-- Updated icon for earning -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                {{-- Organizers --}}
                @if($user->role == 'organizer')
                <div class="col-6 col-sm-12 col-md-6 col-lg-12 col-xl-12">
                    <a href="{{route('admin.contest.index')}}?search={{$user->id}}&status=active">
                        <div class="card mb-1">
                            <div class="card-body">
                                <div class="row align-items-center mb-0">
                                    <div class="col">
                                        <h6 class="m-b-5">Active Contest</h6>
                                        <h3 class="m-b-0">{{$user->contests()->whereStatus('active')->count()}}</h3>
                                    </div>
                                    <div class="col-auto">
                                        <i class="text-info fas fa-trophy"></i> <!-- Updated icon for active contest -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-sm-12 col-md-6 col-lg-12 col-xl-12">
                    <a href="{{route('admin.contest.index')}}?search={{$user->id}}&status=pending">
                        <div class="card mb-1">
                            <div class="card-body">
                                <div class="row align-items-center mb-0">
                                    <div class="col">
                                        <h6 class="m-b-5">Pending Contest</h6>
                                        <h3 class="m-b-0">{{$user->contests()->whereStatus('pending')->count()}}</h3>
                                    </div>
                                    <div class="col-auto">
                                        <i class="text-warning fas fa-hourglass-half"></i> <!-- Updated icon for pending contest -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                {{-- Contestants --}}
                @elseif($user->role == 'contestant')
                <div class="col-6 col-sm-12 col-md-6 col-lg-12 col-xl-12">
                    <a href="{{route('admin.submission.entry')}}?search={{$user->id}}">
                        <div class="card mb-1">
                            <div class="card-body">
                                <div class="row align-items-center mb-0">
                                    <div class="col">
                                        <h6 class="m-b-5">All Entries</h6>
                                        <h3 class="m-b-0">{{$user->entry()->whereType('entry')->count()}}</h3>
                                    </div>
                                    <div class="col-auto">
                                        <i class="text-info fas fa-pen"></i> <!-- Updated icon for entries -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-sm-12 col-md-6 col-lg-12 col-xl-12">
                    <a href="{{route('admin.submission.index')}}?search={{$user->id}}">
                        <div class="card mb-1">
                            <div class="card-body">
                                <div class="row align-items-center mb-0">
                                    <div class="col">
                                        <h6 class="m-b-5">Active Submissions</h6>
                                        <h3 class="m-b-0">{{$user->entry()->whereType('submission')->count()}}</h3>
                                    </div>
                                    <div class="col-auto">
                                        <i class="text-success fas fa-check-circle"></i> <!-- Updated icon for active submissions -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                @endif

                <div class="col-6 col-sm-12 col-md-6 col-lg-12 col-xl-12">
                    <a href="{{route('admin.community.posts')}}?search={{$user->id}}&type=active">
                        <div class="card mb-1">
                            <div class="card-body">
                                <div class="row align-items-center mb-0">
                                    <div class="col">
                                        <h6 class="m-b-5">Posts</h6>
                                        <h3 class="m-b-0">{{$user->posts()->whereStatus('active')->count()}}</h3>
                                    </div>
                                    <div class="col-auto">
                                        <i class="text-info fas fa-comments"></i> <!-- Updated icon for posts -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{!! $user->full_name !!} Information </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="form-group  col-xl-3 col-md-6 col-12">
                                <label class="form-label" for="ev">Email Verification </label>
                                <label class="jdv-switch jdv-switch-success m-0" for="ev">
                                    <input type="checkbox" class="toggle-switch" name="email_verify" @if($user->email_verify) checked @endif id="ev" value="1">
                                    <span class="slider round"></span>
                                </label>
                            </div>
                            <div class="form-group  col-xl-3 col-md-6 col-12">
                                <p class="form-label" for="user_role">Role</p>
                                <span class="badge bg-info">{{$user->role}}</span>
                            </div>
                            <div class="form-group  col-xl-3 col-md-6 col-12">
                                <p class="form-label" for="user_status">Status</p>
                                {!!getUserStatus($user->status)!!}
                            </div>

                        </div>

                        <div class="row mt-2">
                            <div class="col-md-6">
                                <div class="form-group ">
                                    <label class="form-label" for="firstname" class="required">First Name </label>
                                    <input class="form-control" type="text" name="first_name"  value="{{ $user->first_name }}" id="firstname">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group ">
                                    <label class="form-label" for="lastname" class="required">Last Name </label>
                                    <input class="form-control" type="text" name="last_name"  value="{{ $user->last_name }}" id="lastname">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="email" class="required">Email </label>
                                    <input class="form-control" type="email" name="email" value="{{ $user->email }}" required id="email">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="username">Username </label>
                                    <input class="form-control" type="text" name="username" value="{{ $user->username }}" required id="username">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" class="required">Mobile Number </label>
                                    <input type="tel" name="phone" value="{{ $user->phone }}" id="mobile" class="form-control checkUser" >
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="Gender">Gender</label>
                                    <select name="gender" id="Gender" class="form-select form-control">
                                        <option value="male" @if ($user->gender == 'male') selected @endif>Male</option>
                                        <option value="female" @if ($user->gender == 'female') selected @endif>Female</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group ">
                                    <label class="form-label" for="bio">Bio</label>
                                    <textarea name="bio" id="bio" class="form-control" rows="2">{{ $user->bio }}</textarea>
                                </div>
                            </div>
                            <hr class="my-1">
                            <h5 class="fw-bold">Change Password</h5>
                            <div class="form-group">
                                <label class="form-label" for="password">Password </label>
                                <input type="password" name="password" id="password" class="form-control" placeholder="Leave empty if you don'r want to change">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save </button>

                    </form>
                </div>
            </div>

        </div>
    </div>
    {{-- Send Email Modal --}}
    <div id="sendEmail" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">Send Email to {!! $user->username !!}</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                </div>
                <div class="modal-body">
                    <form method="POST" class="ajaxForm resetForm" action="{{ route('admin.users.sendmail', $user->id) }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="id" value="{{ $user->id }}">
                        <div class="form-group">
                            <label class="form-label">Subject </label>
                            <input type="text" name="subject" required class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Message </label>
                            <textarea name="message" id="tiny1" cols="30" class="form-control" rows="10"></textarea>
                        </div>
                        <div class="form-group mb-0">
                            <button type="submit" class="w-100 btn btn-primary"><span> Send Email </span></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    {{-- App Notification --}}
    <div id="sendNotify" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">Send APP Notification to {!! $user->username !!}</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                </div>
                <div class="modal-body">
                    <form method="POST" class="ajaxForm resetForm" action="{{ route('admin.users.notify', $user->id) }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="id" value="{{ $user->id }}">
                        <div class="form-group">
                            <label class="form-label">Title </label>
                            <input type="text" name="title" required class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Body </label>
                            <textarea name="body" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="form-group mb-0">
                            <button type="submit" class="w-100 btn btn-primary"><span> Send Notification </span></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="addSubModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><span class="type"></span> <span>Balance</span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.users.balance', $user->id) }}" class="ajaxForm resetForm" method="POST">
                    @csrf
                    <input type="hidden" name="act" id="act" value="sub">
                    <div class="modal-body">
                        <p class="mb-3 fw-bold">Coin Balance : {{format_number($user->coins->balance ?? 0)}}</p>
                        <input type="hidden" name="id" value="{{ $user->id }}">
                        <div class="form-group">
                            <label for="amount" class="form-label">Amount</label>
                            <div class="input-group">
                                <input type="number" step="any" name="amount" class="form-control" placeholder=" amount" required="" id="amount">
                                <div class="input-group-text">COINS</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="remark" class="form-label">Message</label>
                            <textarea class="form-control" placeholder="Transaction message" name="message" rows="2" required="" id="remark"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary w-100">Update Balance</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
@push('styles')
    <link href="{{ static_asset('admin/vendors/flatpickr/flatpickr.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ static_asset('admin/vendors/summernote/summernote-lite.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .col-auto>i {
            font-size: 25px;
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ static_asset('admin/vendors/summernote/summernote-lite.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#tiny1').summernote({
                height: 200
            });
            $('.bal-btn').on('click', function() {
                var act = $(this).data('act');
                $('#addSubModal').find('input[name=act]').val(act);
                if (act == 'add') {
                    $('.type').text('Add');
                } else {
                    $('.type').text('Subtract');
                }
            });
        });
    </script>
@endpush
