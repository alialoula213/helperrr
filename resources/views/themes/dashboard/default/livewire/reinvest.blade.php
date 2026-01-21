<div>
    <div class="modal fade" wire:ignore.self id="reinvestModal" tabindex="-1" aria-labelledby="reinvestModal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reinvestModal"><i class="fa fa-sync"></i> Reinvest</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" wire:loading.attr="disabled" wire:target="reinvest">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row text-sm">
                        <div class="col-6 text-left"><strong>Min:</strong> <a href="#reinvestMin" wire:click="setMin">{{ $min_amount }}</a></div>
                        <div class="col-6 text-right"><strong>Available:</strong> <a href="#reinvestMax" wire:click="setMax">{{ crypto_currency($balance) }}</a></div>
                    </div>
                    <div class="input-group">
                        <input type="number" wire:model="amount" class="form-control @error('amount') is-invalid @enderror" placeholder="Amount" wire:keyup="calculate" wire:target="reinvest" wire:loading.attr="disabled">
                        <div class="input-group-append">
                            <div class="input-group-text">{{ setting('currency_code') }}</div>
                        </div>
                        @error('amount')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                    <small class="form-text"><strong>Total</strong>: {{ $total_hashpower }} {{ setting('hashpower_unit') }}/s</small>
                    @if($error_message)
                        <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert" wire:loading.remove>
                            {{ $error_message }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal" wire:loading.attr="disabled" wire:target="reinvest">Cancel</button>
                    <button type="submit" class="btn btn-success" id="startBtn" wire:click="reinvest" wire:loading.remove><i class="fa fa-sync"></i> Reinvest Now</button>
                    <button type="button" class="btn btn-success disabled" wire:loading wire:target="reinvest"><i class="fa fa-spinner fa-spin"></i> Processing</button>
                </div>
            </div>
        </div>
    </div>
</div>
