@extends('admin.layouts.master')

@section('title', 'Coins Settings')

@section('content')
    <div class="row">
        <div class="col-sm-12 col-lg-4">

            <div class="card">
                <div class="card-header">
                    <h3 class="fw-bold">Coins Rates</h3>
                </div>
                <div class="card-body">
                    <form action="" method="post" class="ajaxForm ">
                        @csrf
                        @foreach ($settings->where('key', 'coin_rates')->first()->value as $index => $rate)
                            <div class="mb-3 row">
                                <label class="col-md-2 form-label">Coins</label>
                                <div class="col-md-4">
                                    <input type="number" name="settings[coin_rates][value][{{ $index }}][coins]" value="{{ $rate['coins'] }}" class="form-control"
                                        required>
                                </div>
                                <label class="col-md-2 form-label">Price</label>
                                <div class="col-md-4">
                                    <input type="number" step="0.01" name="settings[coin_rates][value][{{ $index }}][price]" value="{{ $rate['price'] }}"
                                        class="form-control" required>
                                </div>
                            </div>
                        @endforeach
                        <button type="submit" class="btn btn-primary w-100">Update Settings</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3>Coin Rewards</h3>
                </div>
                <div class="card-body">
                    <form action="" method="post" class="ajaxForm">
                        @csrf
                        @foreach ($settings->where('key', 'coin_rewards')->first()->value as $key => $value)
                            <div class="mb-3 row">
                                <label class="col-md-3 form-label">{{ ucfirst(str_replace('_', ' ', $key)) }}</label>
                                <div class="col-md-9">
                                    <input type="number" name="settings[coin_rewards][value][{{ $key }}]" value="{{ $value }}" class="form-control" required>
                                </div>
                            </div>
                        @endforeach
                        <button type="submit" class="btn btn-primary w-100">Update Settings</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card ">
                <div class="card-header">
                    <h3>Coin Usage</h3>
                </div>
                <div class="card-body">
                    <form action="" method="post" class="ajaxForm">
                        @csrf
                        @foreach ($settings->where('key', 'coin_usage')->first()->value as $key => $value)
                            <div class="form-group">
                                <label class="form-label">{{ ucfirst(str_replace('_', ' ', $key)) }}</label>
                                <div class="">
                                    @if (is_array($value))
                                        <input type="number" name="settings[coin_usage][value][{{ $key }}][0]" value="{{ $value[0] }}" class="form-control mb-2"
                                            required>
                                        <input type="number" name="settings[coin_usage][value][{{ $key }}][1]" value="{{ $value[1] }}" class="form-control"
                                            required>
                                    @else
                                        <input type="number" name="settings[coin_usage][value][{{ $key }}]" value="{{ $value }}" class="form-control"
                                            required>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                        <button type="submit" class="btn btn-primary w-100">Update Settings</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
