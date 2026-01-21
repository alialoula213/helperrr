@extends('themes.dashboard.default.layout')

@section('header_content')
    <!-- Header -->
    <div class="header bg-gradient-primary pb-8 pt-5 pt-md-8">
        <div class="container-fluid">
            <div class="header-body">
                @include('themes.dashboard.default.partials.alerts')
                @if ($unread_messages)
                    <div class="alert alert-info">
                        <i class="fa fa-envelope"></i> You have {{ $unread_messages }} <a href="{{ route('tickets.index') }}">unread messages</a>.
                    </div>
                @endif
                <!-- Card stats -->
                <div class="row mb-3">
                    <div class="col-xl-12 col-lg-6">
                        <div class="card card-stats mb-4 mb-xl-0 text-center">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <h5 class="card-title text-uppercase text-muted mb-0">Balance</h5>
                                        <span class="h2 font-weight-bold mb-0"><span id="miningBalance">{{ currency_format($user_balance, setting('balance_decimals')) }}</span> {{ setting('currency_code') }}</span>
                                    </div>
                                </div>
                                @if (setting('reinvest_status') === 'enabled')
                                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#reinvestModal"><i class="fa fa-sync"></i> Reinvest</button>
                                    @livewire('reinvest')
                                @endif
                                <p class="mt-3 mb-0 text-muted text-sm">
                                    <span class="text-success mr-2"> <span id="dailyProfit">{{ currency_format($user_daily_profit, setting('balance_decimals')) }}</span> {{ setting('currency_code') }}</span>
                                    <span class="text-nowrap">Daily Profit</span>
                                </p>
                                @if (setting('auto_suspend_users_interval') >= 1 && !auth()->user()->allow_withdrawal)
                                    <div class="alert alert-danger text-sm mt-3 mb-0"><i class="fa fa-exclamation-triangle"></i> You have not made any deposits yet and your account will be automatically disabled for inactivity at
                                        <strong>{{ auth()->user()->created_at->addHours(setting('auto_suspend_users_interval'))->format('Y-m-d H:i:s') }}</strong>. Make a deposit now and prevent your account from being deactivated.</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-4 col-lg-6">
                        <div class="card card-stats mb-4 mb-xl-0">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <h5 class="card-title text-uppercase text-muted mb-0">Current Value</h5>
                                        <span class="h2 font-weight-bold mb-0"><span id="fiatBalance">{{ currency_format($user_balance * $exchange_rate, setting('fiat_balance_decimals')) }}</span> {{ setting('rates_api_currency') }}</span>
                                    </div>
                                    <div class="col-auto">
                                        <div class="icon icon-shape bg-warning text-white rounded-circle shadow">
                                            <i class="fas fa-dollar-sign"></i>
                                        </div>
                                    </div>
                                </div>
                                <p class="mt-3 mb-0 text-muted text-sm">
                                    <span class="text-nowrap">May vary based on market value</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-6">
                        <div class="card card-stats mb-4 mb-xl-0">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <h5 class="card-title text-uppercase text-muted mb-0">Total Referrals</h5>
                                        <span class="h2 font-weight-bold mb-0">{{ $total_refs }}</span>
                                    </div>
                                    <div class="col-auto">
                                        <div class="icon icon-shape bg-yellow text-white rounded-circle shadow">
                                            <i class="fas fa-users"></i>
                                        </div>
                                    </div>
                                </div>
                                <p class="mt-3 mb-0 text-muted text-sm">
                                    <span class="text-nowrap"><a href="{{ route('referral-tools') }}">View details</a></span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-6">
                        <div class="card card-stats mb-4 mb-xl-0">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <h5 class="card-title text-uppercase text-muted mb-0">Hashpower</h5>
                                        <span class="h2 font-weight-bold mb-0">{{ user_hashpower() }} {{ setting('hashpower_unit') }}/s</span>
                                    </div>
                                    <div class="col-auto">
                                        <div class="icon icon-shape bg-info text-white rounded-circle shadow">
                                            <i class="fas fa-server"></i>
                                        </div>
                                    </div>
                                </div>
                                <p class="mt-3 mb-0 text-muted text-sm">
                                    <span class="text-nowrap">Total mining power</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('content')
    <div class="row mt-4">
        <div class="col-xl-6 mb-5 mb-xl-0">
            <div class="card shadow">
                <div class="card-header border-0">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0">Profit Details</h3>
                        </div>
                    </div>
                </div>
                <div class="card-body mb-lg-4">
                    <p>Calculation based on your current mining power</p>
                </div>
                <div class="table-responsive">
                    <table class="table align-items-center table-flush text-center">
                        <thead class="thead-light">
                        <tr>
                            <th scope="col">Amount</th>
                            <th scope="col">Period</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach (calculator_periods() as $days)
                            <tr>
                                <td>
                                    <span>{{ currency_format(user_hashpower() * setting('daily_profit') * $days, 8) }}</span> {{ setting('currency_code') }}
                                </td>
                                <td>
                                    @php
                                        $days_class = 'success';
                                        if($days >= 7 ): $days_class = 'info'; endif;
                                        if($days >= 30 ): $days_class = 'primary'; endif;
                                        if($days >= 365 ): $days_class = 'danger'; endif;
                                    @endphp
                                    <span class="badge badge-{{ $days_class }}">
                                        @if($days < 7)
                                            {{ $days }} {{ $days === '1' ? 'day' : 'days' }}
                                        @elseif($days === '7')
                                            1 week
                                        @elseif($days === '14')
                                            2 weeks
                                        @elseif($days === '21')
                                            3 weeks
                                        @elseif($days === '30')
                                            1 month
                                        @elseif($days === '60')
                                            2 months
                                        @elseif($days === '90')
                                            3 months
                                        @elseif($days === '120')
                                            4 months
                                        @elseif($days === '150')
                                            5 months
                                        @elseif($days === '180')
                                            6 months
                                        @elseif($days === '210')
                                            7 months
                                        @elseif($days === '240')
                                            8 months
                                        @elseif($days === '270')
                                            9 months
                                        @elseif($days === '300')
                                            10 months
                                        @elseif($days === '330')
                                            11 months
                                        @elseif($days === '365')
                                            1 year
                                        @else
                                            {{ $days }} days
                                        @endif
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            @livewire('profit-calculator')
        </div>
    </div>
@endsection

@push('footer_scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function(e) {
            let currentRate = {{ $exchange_rate }};
            let balance = document.getElementById('miningBalance');
            const fiatBalance = document.getElementById('fiatBalance');
            const dailyProfit = document.getElementById('dailyProfit');
            let daily_profit = (dailyProfit.innerText);
            let balance_value = {{ $user_balance }};
            let per_second = (daily_profit / {{ setting('mining_counter_seconds', 86400) }}).toFixed(15);
            setInterval(function(){
                balance_value = parseFloat(balance_value)+parseFloat(per_second);
                balance.innerText = parseFloat(balance_value).toFixed({{ setting('balance_decimals') }});
                fiatBalance.innerText = (balance.innerText * currentRate).toFixed({{ setting('fiat_balance_decimals') }});
            }, {{ setting('mining_counter_speed', 1000) }});
        });
    </script>
@endpush