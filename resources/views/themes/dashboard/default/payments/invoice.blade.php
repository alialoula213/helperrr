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
                            <h3 class="mb-0">Confirm Purchase</h3>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p>Congratulations on your decision to invest in our project and get more profit with more Mining Power!</p>
                    @includeIf('themes.dashboard.default.payments.'.setting('deposit_gateway'))
                </div>
                <div class="card-footer">
                    <p>If you have already sent the payment to the address provided, please wait a few minutes for our system to identify your payment. This is an automatic process, it can take between <strong class="text-underline">1 minute or up to 1 hour</strong>, depending on the amount of confirmations needed.</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('footer_scripts')
    <script>
        function copy_wallet_address(){
            document.getElementById('paymentWallet').select();
            document.execCommand('copy');
            document.getElementById('copied').style.display='block';
            const copied = $('#copied');
            setTimeout(function(){
                copied.fadeOut(1500);
            }, 3000);
        }

        document.addEventListener("DOMContentLoaded", function(e) {
            // Set the date we're counting down to
            let countDownDate = new Date("{{ $invoice->invoice_expire_date }}").getTime();

            // Update the count down every 1 second
            let x = setInterval(function() {

                // Get today's date and time
                let now = new Date(new Date().toLocaleString('en-US', { timeZone: "{{ config('app.timezone') }}"})).getTime();

                // Find the distance between now and the count down date
                let distance = countDownDate - now;

                // Time calculations for days, hours, minutes and seconds
                let days = Math.floor(distance / (1000 * 60 * 60 * 24));
                let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                let seconds = Math.floor((distance % (1000 * 60)) / 1000);

                // Display the result in the element with id="demo"
                document.getElementById("timeout").innerHTML = days + "d " + hours + "h "
                    + minutes + "m " + seconds + "s ";

                // If the count down is finished, write some text
                if (distance < 0) {
                    clearInterval(x);
                    document.getElementById("timeout").innerHTML = "Expired!";
                    window.location.reload();
                }
            }, 1000);
        });
    </script>
@endpush