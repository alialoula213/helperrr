<!-- Navigation -->
<ul class="navbar-nav nav-sidebar">
    <li class="nav-item">
        <a class="nav-link" href="{{ route('account') }}">
            <i class="fa fa-home"></i> Dashboard
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('history') }}">
            <i class="fa fa-history"></i> Account History
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('my-deposits') }}">
            <i class="fa fa-chart-area"></i> Deposits
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('my-withdrawals') }}">
            <i class="fa fa-chart-line"></i> Withdrawals
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('referral-tools') }}">
            <i class="fa fa-users"></i> Referrals
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="javascript:void(0);" data-toggle="modal" data-target="#faqsModal">
            <i class="fa fa-question-circle"></i> F.A.Q.s
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('news') }}">
            <i class="fa fa-newspaper"></i> News
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('tickets.index') }}">
            <i class="fa fa-life-ring"></i> Support Center
        </a>
    </li>
</ul>
<!-- Divider -->
<hr class="my-3">
<h4 class="text-center">1 {{ setting('currency_code') }} = {{ fiat_currency($exchange_rate, setting('fiat_balance_decimals')) }}</h4>
<a class="btn btn-success btn-block" href="#depositModal" data-toggle="modal" data-target="#depositModal">
    <i class="fa fa-piggy-bank"></i> Deposit
</a>
<a class="btn btn-warning btn-block" href="#withdrawalModal" data-toggle="modal" data-target="#withdrawalModal">
    <i class="fa fa-hand-holding-usd"></i> Withdrawal
</a>