@extends('admin.layouts.master')
@section('title', $title)

@section('page-title')
<ol class="breadcrumb m-0">
    <li class="breadcrumb-item"><a href="javascript: void(0);">@lang('Admin')</a></li>
    <li class="breadcrumb-item active">@yield('title')</li>
</ol>
@endsection

@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5>Withdrawal History</h5>
        <div class="pull-right search mr-2">
            <form action="" method="get" id="history-search">
                <div class="d-flex">
                    <input type="text" name="search" value="{{ request()->search }}" id="searchInput" minlength="3" placeholder="Search withdrawals" class="form-control" />
                    <button type="submit" class="btn btn-sm btn-info">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @if($title != "Pending Withdrawals")
    <div class="row gy-3 gx-3 p-3 pb-0">
        <div class="col-xl-4 col-lg-4 col-md-6">
          <div class="custom-select-box-two">
            <label class="form-label">Select Status</label>
            <select class="form-select" onchange="window.location.href=this.value">
              <option value="{{queryBuild('type','')}}" {{request('type') == '' ? 'selected':''}}>@lang('All Withdrawals')</option>
              <option value="{{queryBuild('type','pending')}}" {{request('type') == 'pending' ? 'selected':''}}>@lang('Pending Withdrawals')</option>
              <option value="{{queryBuild('type','approved')}}" {{request('type') == 'approved' ? 'selected':''}}>@lang('Approved Withdrawals')</option>
              <option value="{{queryBuild('type','processing')}}" {{request('type') == 'processing' ? 'selected':''}}>@lang('Processing Withdrawals')</option>
              <option value="{{queryBuild('type','completed')}}" {{request('type') == 'completed' ? 'selected':''}}>@lang('Completed Withdrawals')</option>
              <option value="{{queryBuild('type','canceled')}}" {{request('type') == 'canceled' ? 'selected':''}}>@lang('Canceled Withdrawals')</option>
            </select>
          </div><!-- custom-select-box-two end -->
        </div>
    </div>
    @endif
    <div class="card-body table-responsive">
        <table class="responsive-table table table-hover table-striped search-table">
            <thead class="thead-primary">
                <tr>
                    <th>#</th>
                    <th scope="col">@lang('User')</th>
                    <th scope="col">@lang('Amount')</th>
                    <th scope="col">@lang('Fee')</th>
                    <th scope="col">@lang('Coins')</th>
                    <th class="col">Method</th>
                    <th scope="col">@lang('Date')</th>
                    <th scope="col">@lang('Status')</th>
                    {{-- <th scope="col">@lang('New Bal')</th> --}}
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($withdraws as $item)
                    <tr>
                        <td data-label="Code">
                            {{ $item->code }}
                        </td>
                        <td data-label="@lang('User')">
                            <a href="{{ route('admin.users.view', $item->user_id) }}" style="color: #007BFF; text-decoration: underline;">
                                {{ textTrim($item->user->username, 30) ?? 'N/A' }}
                            </a>
                        </td>
                        <td data-label="@lang('Amount')">
                            <p class="my-0">{{ format_price($item->amount) }} </p>
                        </td>
                        <td data-label="@lang('Fees')">
                            <p class="my-0">{{ format_price($item->fee) }} </p>
                        </td>
                        <td data-label="@lang('Coins')">
                            <p class="my-0">{{ ($item->coins) }} </p>
                        </td>
                        <td data-label="Method">
                            <span class="badge bg-primary">{{ $item->method }}</span>
                        </td>
                        <td data-label="@lang('Date')">
                            {{ show_datetime($item->created_at) }}
                        </td>
                        <td data-label="@lang('Status')">
                            {!! withdrawStatus($item->status)!!}
                        </td>
                        {{-- <td data-label="@lang('New Balance')">
                            <p class="my-0">{{ format_price($item->newbal) }} </p>
                        </td> --}}
                        <td data-label="@lang('Actions')">
                            <div class="dropstart">
                                <button class="btn btn-outline-primary " type="button" id="" data-bs-toggle="dropdown">
                                    <i class="far fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#wdr{{$item->id}}">
                                        View Details
                                    </a>
                                    @if ($item->status == 'pending' || $item->status == 'processing')
                                        <a class="dropdown-item approve-btn" href="javascript:void(0)" data-url="{{ route('admin.withdrawal.approve', $item->id) }}">
                                            Approve
                                        </a>
                                        <a class="dropdown-item reject-btn" href="javascript:void(0)" data-url="{{ route('admin.withdrawal.reject', $item->id) }}">
                                            Reject
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                    <div class="modal fade" id="wdr{{$item->id}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title" id="myModalLabel"> Payouts Details</h4>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div>
                                        @if ($item->method == "paypal")
                                        <p class="form-label">Email: {{$item->payment_details->email ?? ''}}</p>
                                        @elseif ($item->method == "bank_transfer")
                                        <p style="font-size:15px;">
                                            Bank: <b>{{ $item->payment_details->bank_name?? '' }} </b> <br>
                                            Account: <b>{{ $item->payment_details->account_number?? '' }} </b> <br>
                                            Name: <b>{{ $item->payment_details->account_name ?? ''}} </b>
                                        </p>
                                        @else
                                        <p class="form-label">Payout destination not set</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="100%" class="text-center">@lang('No Withdrawal Requests Found')</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($withdraws->hasPages())
            <div class="py-1 paginate-footer">
                {{ paginateLinks($withdraws) }}
            </div>
        @endif
    </div>
</div>

@endsection
@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Select all dropdown items with the `autowithdrawBtn` class
        const autoWithdrawButtons = document.querySelectorAll('.autowithdrawBtn');

        autoWithdrawButtons.forEach(button => {
            button.addEventListener('click', function () {
                // Extract the data attributes
                const details = JSON.parse(this.getAttribute('data-details'));
                const withdrawalId = this.getAttribute('data-id');
                const amount = this.getAttribute('data-amount') || "N/A";
                const amountNgn = this.getAttribute('data-amountNgn');
                const amountNaira = this.getAttribute('data-amountNaira');

                // Populate the modal fields
                document.getElementById('dwmBank').innerText = details.bank_name || "Unknown Bank";
                document.getElementById('dwmAccount').innerText = details.account_number || "N/A";
                document.getElementById('dwmName').innerText = details.account_name || "N/A";
                document.getElementById('dwmAmount').innerText = amount || "N/A";
                document.getElementById('dwmAmountNgn').innerText = amountNgn || "N/A";

                document.querySelector('input[name="amountNaira"]').value = amountNaira;
                // Set the hidden withdrawal ID
                const hiddenInput = document.querySelector('input[name="withdrawal_id"]');
                if (hiddenInput) hiddenInput.value = withdrawalId;
            });
        });
    });
</script>

@endpush
