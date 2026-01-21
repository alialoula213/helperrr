<div class="row">
    <div class="col-lg-6 col-sm-12 text-center">
        <img src="{{ $invoice_params->result->qrcode_url }}" alt="QrCode" style="width: 300px; height: 300px" class="img-thumbnail" />
    </div>
    <div class="col-lg-6 col-sm-12 text-center">
        <h1>Invoice# {{ $invoice->invoice }}</h1>
        <p>To pay, please send exact amount of {{ setting('currency_code') }} to the given address.</p>
        <h2>{{ crypto_currency($invoice->amount, 8) }}</h2>
        <p>≃ {{ fiat_currency($invoice->amount * $exchange_rate, setting('fiat_balance_decimals')) }}</p>
        <p class="text-sm">{{ $invoice_params->result->confirms_needed }} confirmations required</p>
        <div class="input-group mb-2">
            <input class="form-control text-center" id="paymentWallet" value="{{ $invoice_params->result->address }}" readonly onclick="this.select();">
            <div class="input-group-prepend">
                <button class="btn btn-primary" onclick="copy_wallet_address();" title="Copy to Clipboard"><i class="fa fa-copy"></i>&nbsp; Copy</button>
            </div>
        </div>
        <small class="form-text text-success" style="display: none" id="copied">Copied to Clipboard</small>
        <p><span id="timeout">{{ now()->diff($invoice->invoice_expire_date)->format('%dd %hh %Im %Ss') }}</span> minutes left to pay your order</p>
    </div>
</div>