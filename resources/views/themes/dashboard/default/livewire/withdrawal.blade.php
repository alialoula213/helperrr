<div>
    <div class="modal fade" wire:ignore.self id="withdrawalModal" tabindex="-1" aria-labelledby="withdrawalModal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="withdrawalModal"><i class="fa fa-money-bill"></i> Request Withdrawal</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" wire:loading.attr="disabled" wire:target="withdrawal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @if (setting('withdrawal_gateway') === 'faucetpay')
                        <div class="alert alert-danger">You must have a FaucetPay account with the same wallet used in your account on our website to receive payments. If you don't have an account yet, <a href="https://faucetpay.io/?r={{ setting('faucetpay_referral') }}" target="_blank">click here</a> to create a new account before requesting your payment.</div>
                    @endif
                    @if(!auth()->user()->allow_withdrawal && setting('withdrawal_deposit_required') === 'yes')
                        <div class="alert alert-danger fade show mt-2" role="alert">
                            You cannot make a withdraw request without making a deposit first.
                        </div>
                    @else
                        <div class="row text-sm">
                            <div class="col-6 text-left"><strong>Min:</strong> <a href="#withdrawalMin" wire:click="setMin">{{ crypto_currency($min_withdraw) }}</a></div>
                            <div class="col-6 text-right"><strong>Max:</strong> <a href="#withdrawalMin" wire:click="setMax">{{ crypto_currency($max_withdraw) }}</a></div>
                        </div>
                        <div class="input-group">
                            <input type="number" wire:model="amount" class="form-control @error('amount') is-invalid @enderror" placeholder="Amount" wire:change="calcFees">
                            <div class="input-group-append">
                                <div class="input-group-text">{{ setting('currency_code') }}</div>
                            </div>
                            @error('amount')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        @if(setting('withdrawal_fee_percent') > '0' || setting('withdrawal_fee_fixed') > '0')
                        <small class="form-text"><strong>Fees</strong>: {{ setting('withdrawal_fee_percent') }}% + {{ crypto_currency(setting('withdrawal_fee_fixed')) }}.</small>
                        <small class="form-text"><strong>Total Amount</strong>: {{ crypto_currency($final_amount) }}</small>
                        @endif
                        @if($withdrawal_error_message)
                            <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert" wire:loading.remove>
                                {{ $withdrawal_error_message }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal" wire:loading.attr="disabled" wire:target="withdrawal">Cancel</button>
                    @if((auth()->user()->allow_withdrawal && setting('withdrawal_deposit_required') === 'yes') xor setting('withdrawal_deposit_required') === 'no')
                    <button type="submit" class="btn btn-success" id="startBtn" wire:click="withdrawal" wire:loading.remove><i class="fa fa-hand-holding-usd"></i> Confirm Request</button>
                    <button type="button" class="btn btn-success disabled" wire:loading wire:target="withdrawal"><i class="fa fa-spinner fa-spin"></i> Processing</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
