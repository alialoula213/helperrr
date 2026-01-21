@extends('themes.dashboard.default.layout')

@section('header_content')
    <!-- Header -->
    <div class="header bg-gradient-primary pb-8"></div>
@endsection
@section('content')
    <div class="row mt-5">
        <div class="col-xl-12 mb-5 mb-xl-0">
            @include('themes.dashboard.default.partials.alerts')
            <div class="card shadow">
                <div class="card-header border-0">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0">Deposit History</h3>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped text-center">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">Invoice</th>
                                    <th scope="col">HashPower</th>
                                    <th scope="col">Amount</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Date</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($deposits as $deposit)
                                    <tr>
                                        <td>{{ $deposit->invoice }}</td>
                                        <td>{{ $deposit->power }} {{ setting('hashpower_unit') }}/s</td>
                                        <td>{{ crypto_currency($deposit->amount) }}</td>
                                        <td>
                                            @if($deposit->status === 'pending')
                                                <span class="badge badge-primary">Pending</span>
                                            @elseif($deposit->status === 'processing')
                                                <span class="badge badge-info">Processing</span>
                                            @elseif($deposit->status === 'paid')
                                                <span class="badge badge-success">Paid</span>
                                            @else
                                                <span class="badge badge-danger">Canceled</span>
                                            @endif
                                        </td>
                                        <td>{{ $deposit->created_at }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                @if($deposit->status === 'pending')
                                                    <a href="{{ route('payment', $deposit->invoice) }}" class="btn btn-success"><i class="fa fa-money-bill"></i> Pay Now</a>
                                                @else
                                                    <a href="{{ setting('blockchain_url').$deposit->tx_id }}" class="btn btn-info @if(!$deposit->tx_id)disabled @endif" target="_blank"><i class="fa fa-eye"></i> View on Blockchain</a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td class="text-center" colspan="6">No deposits found!</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($deposits->hasPages())
                <div class="card-footer">
                    {{ $deposits->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection