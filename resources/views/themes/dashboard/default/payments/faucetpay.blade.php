<div class="row">
    <div class="col-lg-12 col-sm-12 text-center">
        <p>Redirecting to payment page, wait... <i class="fa fa-spinner fa-spin"></i></p>
        <form action="https://faucetpay.io/merchant/webscr" method="post" id="payment">
            <input type="hidden" name="merchant_username" value="{{ setting('faucetpay_username') }}">
            <br>
            <input type="hidden" name="item_description" value="{{ setting('site_name'). ' Purchase of '.$invoice->power. ' '. setting('hashpower_unit'). '/s' }}">
            <br>
            <input type="hidden" name="amount1" value="{{ $invoice->amount }}">
            <br>
            <input type="hidden" name="currency1" value="{{ setting('currency_code') }}">
            <br>
            <input type="hidden" name="custom" value="{{ $invoice->invoice }}">
            <br>
            <input type="hidden" name="callback_url" value="{{ route('ipn.faucetpay') }}">
            <br>
            <input type="hidden" name="success_url" value="{{ route('ipn.success') }}">
            <br>
            <input type="hidden" name="cancel_url" value="{{ route('ipn.fail') }}">
        </form>
    </div>
</div>

@push('footer_scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function(e) {
            const paymentForm = document.getElementById('payment');
            paymentForm.submit();
        });
    </script>
@endpush