@extends('admin.layouts.master')

@section('title', 'Coins Transactions')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="fw-bold">Coin Transactions</h5>
            <form action="" method="GET">
                <div class="input-group justify-content-end">
                    <input type="search" name="search" class="form-control" placeholder="@lang('Search transactions')"
                        value="{{ request()->search ?? '' }}" id="searchInput">
                    <button class="btn btn-primary input-group-text" type="submit"><i class="far fa-search"></i></button>
                </div>
            </form>
        </div>

        <div class="row gy-3 gx-3 p-3">
            <div class="col-xl-3 col-lg-3 col-md-4 col-6">
                <div class="custom-select-box-two">
                    <label>Trnx Type</label>
                    <select class="form-select" onchange="window.location.href=this.value">
                        <option value="{{ queryBuild('type', '') }}" {{ request('type') == '' ? 'selected' : '' }}>
                            @lang('All Type')</option>
                        <option value="{{ queryBuild('type', 'credit') }}"
                            {{ request('type') == 'credit' ? 'selected' : '' }}>
                            @lang('Credit Transactions')</option>
                        <option value="{{ queryBuild('type', 'debit') }}"
                            {{ request('type') == 'debit' ? 'selected' : '' }}>
                            @lang('Debit Transactions')</option>
                    </select>
                </div><!-- custom-select-box-two end -->
            </div>
            <div class="col-xl-3 col-lg-3 col-md-4 col-6">
                <div class="custom-select-box-two">
                    <label>Trnx Service</label>
                    <select class="form-select" onchange="window.location.href=this.value">
                        <option value="{{ queryBuild('service', '') }}" {{ request('service') == '' ? 'selected' : '' }}>
                            @lang('All Services')</option>
                        @foreach ($services as $service)
                            <option value="{{ queryBuild('service', $service['service']) }}"
                                {{ request('service') == $service['service'] ? 'selected' : '' }}>
                                {{ ucfirst($service['service']) }}</option>
                        @endforeach
                    </select>
                </div><!-- custom-select-box-two end -->
            </div>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-striped responsive-table table-hover search-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Amount</th>
                        <th>Coins</th>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Message</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transactions as $transaction)
                        <tr>
                            <td data-label="@lang('#')">{{ $transaction->code }}</td>
                            <td data-label="@lang('User')">
                                <span class="fw-bold">{{ $transaction->user->full_name ?? 'n/a' }}</span>
                                <br> @<a href="{{ route('admin.users.view', $transaction->user_id) }}"
                                    class="text-primary">{{ $transaction->user->username ?? 'n/a' }}</a>
                            </td>
                            <td data-label="@lang('Amount')">{{ format_price($transaction->amount, 2) }}</td>
                            <td data-label="@lang('Coin')">{{ $transaction->coins }}</td>
                            <td data-label="@lang('Service')">{{ ucfirst($transaction->service) }}</td>
                            <td data-label="@lang('Date')">{{ $transaction->created_at->format('Y-m-d H:i:s') }}</td>
                            <td data-label="@lang('Message')"><span class="text-wrap">
                                    {{ $transaction->description }}</span></td>
                            <td data-label="@lang('Actions')">
                                <a href="#" class="btn btn-sm btn-info" data-bs-toggle="modal"
                                    data-bs-target="#trxDetail{{ $transaction->id }}">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($transactions->hasPages())
            <div class="card-footer text-end">{{ paginateLinks($transactions) }}</div>
        @endif
    </div>

    @foreach ($transactions as $key => $item)
        <div class="modal fade" id="trxDetail{{ $item->id }}" tabindex="-1" role="dialog"
            aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header py-2">
                        <h4 class="modal-title" id="myModalLabel"> Transaction Details</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <p> Transaction ID : {{ $item->code }} </p>
                            <p class="col-6"> <b>New Bal:</b> {{ format_price($item->newbal) }} </p>
                            <p class="col-6"> <b>Old Bal :</b> {{ format_price($item->oldbal) }} </p>
                            <p class="col-6">Type :
                                @if ($item->type == 'credit')
                                    <span class="badge bg-success">credit</span>
                                @elseif ($item->type == 'debit')
                                    <span class="badge bg-danger">debit</span>
                                @endif
                            </p>
                            <p class="col-6">Service : <span class="badge bg-info p-1">{{ $item->service }} </span> </p>
                            <p class="col-sm-12"> Details : {{ $item->description }} </p>
                            <p class="col-sm-12"> API Response :
                            <div style="max-height: 250px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;"
                                class="pt-2">
                                <pre>{{ json_encode($item->response, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection
