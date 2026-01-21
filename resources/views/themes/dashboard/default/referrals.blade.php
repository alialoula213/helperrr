@extends('themes.dashboard.default.layout')

@section('header_content')
    <!-- Header -->
    <div class="header bg-gradient-primary pb-8"></div>
@endsection
@section('content')
    <div class="row mt-5">
        <div class="col-xl-12 mb-5 mb-xl-0">
            <div class="card shadow">
                <div class="card-header border-0">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0">Referrals Tools</h3>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p>You can earn coins by sharing referral link to your friends. For each referral deposit, you will receive
                        {{ setting('referral_bonus') }}% of the deposit amount.</p>
                    <div class="form-group">
                        <label for="link">Referral Link</label>
                        <div class="input-group input-group-merge">
                            <div class="input-group-prepend" title="Copy to Clipboard" onclick="copy_ref_link();">
                                <span class="input-group-text"><i class="fa fa-copy"></i></span>
                            </div>
                            <input type="link" name="link" id="link" class="form-control text-center" readonly value="{{ route('ref', ['uuid' => auth()->user()->uuid]) }}">
                            <div class="input-group-append">
                                <span class="input-group-text"><i class="fa fa-retweet"></i>&nbsp;<span class="leading">{{ auth()->user()->ref_hits }} hits</span></span>
                            </div>
                        </div>
                        <small class="form-text text-success" style="display: none" id="reflink_copied">Copied to Clipboard</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-xl-12 mb-5 mb-xl-0">
            <div class="card shadow">
                <div class="card-header border-0">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0">Referrals List</h3>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped text-center">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">Wallet</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($referrals as $ref)
                                    <tr>
                                        <td>{{ substr($ref->wallet, 0, -5) }}<span class="text-danger font-weight-bold">XXXXX</span></td>
                                        <td>
                                            @if($ref->status === 'active')
                                                <span class="badge badge-success">Active</span>
                                            @elseif($ref->status === 'inactive')
                                                <span class="badge badge-info">Inactive</span>
                                            @else
                                                <span class="badge badge-danger">Banned</span>
                                            @endif
                                        </td>
                                        <td>{{ $ref->created_at }}</td>
                                    </tr>
                                @empty
                                    <tr><td class="text-center" colspan="3">No referrals found!</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($referrals->hasPages())
                <div class="card-footer">
                    {{ $referrals->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('footer_scripts')
    <script>
        function copy_ref_link(){
            document.getElementById('link').select();
            document.execCommand('copy');
            document.getElementById('reflink_copied').style.display='block';
            const copied = $('#reflink_copied');
            setTimeout(function(){
                copied.fadeOut(1500);
            }, 3000);
        }
    </script>
@endpush