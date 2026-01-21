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
                            <h3 class="mb-0">Withdrawals History</h3>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped text-center">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">Amount</th>
                                    <th scope="col">Fees</th>
                                    <th scope="col">Paid Amount</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Date</th>
                                    <th scope="col">TX ID</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($withdrawals as $withdrawal)
                                    <tr>
                                        <td>{{ crypto_currency($withdrawal->amount) }}</td>
                                        <td>{{ crypto_currency($withdrawal->fees) }}</td>
                                        <td>{{ crypto_currency($withdrawal->paid_amount) }}</td>
                                        <td>
                                            @if($withdrawal->status === 'pending')
                                                <span class="badge badge-primary">Pending</span>
                                            @elseif($withdrawal->status === 'processing')
                                                <span class="badge badge-info">Processing</span>
                                            @elseif($withdrawal->status === 'paid')
                                                <span class="badge badge-success">Paid</span>
                                            @else
                                                <span class="badge badge-danger" title="{{ $withdrawal->cancel_reason }}">Canceled</span>
                                            @endif
                                        </td>
                                        <td>{{ $withdrawal->created_at }}</td>
                                        <td><a href="{{ setting('blockchain_url').$withdrawal->tx_id }}" class="btn btn-sm btn-info @if(!$withdrawal->tx_id)disabled @endif" target="_blank" title="View on Blockchain"><i class="fa fa-eye"></i></a></td>
                                    </tr>
                                @empty
                                    <tr><td class="text-center" colspan="6">No withdrawals found!</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($withdrawals->hasPages())
                <div class="card-footer">
                    {{ $withdrawals->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection