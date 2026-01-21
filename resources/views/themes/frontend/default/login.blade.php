<div>
    <div class="modal fade" wire:ignore.self id="loginModal" tabindex="-1" aria-labelledby="loginModal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModal">Get Started</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="wallet" wire:model.defer="wallet" class="form-control @error('wallet') is-invalid @enderror" placeholder="Your {{ setting('currency_name') }} wallet address">
                    <input name="ref_id" wire:model="referral" id="ref_id" type="hidden" value="{{ $referral }}">
                    @error('wallet')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                    @if($response)
                        <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
                            {{ $response }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal" wire:loading.attr="disabled">Close</button>
                    <button type="submit" class="btn btn-success" id="startBtn" wire:click="start" wire:loading.remove>Start Mining</button>
                    <button type="button" class="btn btn-success disabled" wire:loading><i class="fa fa-spinner fa-spin"></i> Processing</button>
                </div>
            </div>
        </div>
    </div>

</div>