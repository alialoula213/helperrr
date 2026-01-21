<div>
    <div class="modal fade" wire:ignore.self id="depositModal" tabindex="-1" aria-labelledby="depositModal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="depositModal"><i class="fa fa-piggy-bank"></i> Buy Hashpower</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" wire:loading.attr="disabled" wire:target="deposit">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row text-sm">
                        <div class="offset-6 col-6 text-right"><strong>Min:</strong> <a href="#purchaseMin" wire:click="setMin">{{ $min_buy }} {{ setting('hashpower_unit') }}/s</a></div>
                    </div>
                    <div class="input-group">
                        <input type="number" wire:model="hashpower_amount" step="1" min="0" class="form-control @error('hashpower_amount') is-invalid @enderror" placeholder="Hashpower" wire:keyup="calculate" wire:target="deposit" wire:loading.attr="disabled">
                        <div class="input-group-append">
                            <div class="input-group-text">{{ setting('hashpower_unit') }}/s</div>
                        </div>
                        @error('hashpower_amount')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <small class="form-text"><strong>Total</strong>: {{ $purchase_total_price }} {{ setting('currency_code') }}</small>
                    @if($deposit_error_message)
                        <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert" wire:loading.remove>
                            {{ $deposit_error_message }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal" wire:loading.attr="disabled" wire:target="deposit">Cancel</button>
                    <button type="submit" class="btn btn-success" id="startBtn" wire:click="deposit" wire:loading.remove><i class="fa fa-shopping-cart"></i> Buy Now</button>
                    <button type="button" class="btn btn-success disabled" wire:loading wire:target="deposit"><i class="fa fa-spinner fa-spin"></i> Processing</button>
                </div>
            </div>
        </div>
    </div>
</div>
