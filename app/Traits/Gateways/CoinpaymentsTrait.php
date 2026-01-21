<?php

namespace App\Traits\Gateways;

use App\Models\Deposit;
use App\Models\User;
use App\Models\UserMiningPower;
use App\Models\Withdrawal;
use App\Traits\DepositTrait;
use App\Traits\WithdrawalUtility;
use CoinpaymentsAPI;
use Illuminate\Http\Request;

trait CoinpaymentsTrait
{
    use WithdrawalUtility, DepositTrait;

    protected function coinpayments(Deposit $deposit)
    {
        $coinpayments = new CoinpaymentsAPI(setting('coinpayments_pvk'), setting('coinpayments_pbk'),'json');
        $description = setting('site_name'). ' Purchase of '.$deposit->power. ' '. setting('hashpower_unit'). '/s';
        //Gateway mode
        if(setting('coinpayments_mode') === 'gateway'){
            //Generate URL
            $url = 'https://www.coinpayments.net/index.php';

            $req = array();
            $req['cmd'] = '_pay';
            $req['reset'] = 1;
            $req['merchant'] = setting('coinpayments_mid');
            $req['item_name'] = html_entity_decode($description, ENT_QUOTES, 'UTF-8');
            $req['amountf'] = $deposit->amount;
            $req['want_shipping'] = 0;
            $req['currency'] = setting('deposit_currency_code');
            $req['invoice'] = $deposit->invoice;
            $req['email'] = config('mail.from.address');
            $req['success_url'] = route('ipn.success');
            $req['ipn_url'] = route('ipn.coinpayments');
            $req['cancel_url'] = route('ipn.fail');

            $url .= '?' . http_build_query($req);

            //Return url
            return $url;
        }
        //Create
        $cp_transaction = $coinpayments->CreateCustomTransaction([
            'amount' => $deposit->amount,
            'currency1' => setting('deposit_currency_code'),
            'currency2' => setting('deposit_currency_code'),
            'item_name' => $description,
            'invoice' => $deposit->invoice,
            'ipn_url' => route('ipn.coinpayments'),
            'buyer_email' => config('mail.from.address'),
        ]);
        if ($cp_transaction['error'] === 'ok') {
            //Generate QrCode
            $qrcode = $this->generateQrCode($cp_transaction['result']['amount'], $cp_transaction['result']['address'], $cp_transaction['result']['timeout'], $cp_transaction['result']['confirms_needed']);
            //Update deposit with response
            $deposit->update(['response' => json_encode($cp_transaction), 'qrcode' => $qrcode, 'invoice_expire_date' => now()->addSeconds($cp_transaction['result']['timeout'])]);
            return true;
        }
        //Log error
        payment_log('deposit', $cp_transaction['error'], $deposit, $cp_transaction);
        logger()->error('Coinpayments Error: '. $cp_transaction['error']);
        return false;
    }

    protected function coinpaymentsWithdrawal(Withdrawal $withdrawal, $error_reporting)
    {
        $coinpayments = new CoinpaymentsAPI(setting('coinpayments_pvk'), setting('coinpayments_pbk'),'json');

        //Create
        $cp_withdrawal = $coinpayments->CreateWithdrawal([
            'amount' => $withdrawal->amount,
            'address' => $withdrawal->user->wallet,
            'currency' => setting('withdrawal_currency_code'),
            'add_tx_fee' => setting('coinpayments_fee'),
            'ipn_url' => route('ipn.coinpayments'),
            'auto_confirm' => 1,
            'note' => setting('site_name'). ' Withdrawal Payment ID#'.$withdrawal->id,
        ]);
        if ($cp_withdrawal['error'] === 'ok') {
            $total_amount = $withdrawal->amount;
            $fees = null;
            if(setting('coinpayments_fee') == '0'){ //user pays fees
                $fees = currency_format($cp_withdrawal['result']['amount'] - $total_amount);
                $total_amount = currency_format($cp_withdrawal['result']['amount'] - $fees);
            }
            //Update withdrawal
            $this->paidWithdrawal($withdrawal, $cp_withdrawal, $cp_withdrawal['result']['id'], $total_amount, $fees);
            //Create user log
            $this->createWithdrawalUserLog($cp_withdrawal['result']['amount'], $withdrawal->user->id);
            return true;
        }
        if($error_reporting){
            return $cp_withdrawal['error'];
        }
        //Log error
        payment_log('withdrawal', $cp_withdrawal['error'], $withdrawal, $cp_withdrawal);
        logger()->error('Coinpayments Error: '. $cp_withdrawal['error']);
        return false;
    }

    public function coinpaymentsIpn(Request $request)
    {
        $this->input_request = $request->all();
        //Get settings
        $MID = setting('coinpayments_mid');
        $IPNS = setting('coinpayments_ipn');
        $CUR = setting('deposit_currency_code');

        //Store request values
        $_ipn_mode = $request->input('ipn_mode');
        $_ipn_type = $request->input('ipn_type');
        $_merchant = $request->input('merchant');
        $_status = $request->input('status');
        $_HTTP_HMAC = $request->server('HTTP_HMAC');
        $_converted_to = $request->input('converted_to');
        $_txn_id = $request->input('txn_id');
        $_block_txn_id = $request->input('send_tx');

        if ($_ipn_type == 'withdrawal') {
            $this->transaction_type = 'withdrawal';
            $_currency1 = $request->input('currency');
            $_amount1 = $request->input('amount');
            $_invoice = $request->input('id');
        } else {
            $this->transaction_type = 'deposit';
            $_currency1 = $request->input('currency1');
            $_amount1 = $request->input('amount1');
            $_invoice = $request->input('invoice');
        }

        //Get transaction
        if ($_ipn_type == 'withdrawal') {
            $transaction = Withdrawal::where('tx_id', $_invoice)->where('status', 'paid')->first();
        } else {
            $transaction = Deposit::where('invoice', $_invoice)->where('status', '!=', 'paid')->first();
        }

        //Check if transaction exists
        if ($transaction === null) {
            return $this->returnIpnError('Transaction not found. Tid: #' . $_invoice, $request->all());
        }
        $this->transaction_id = $transaction->id;
        $this->user_id = $transaction->user_id;

        if (!isset($_ipn_mode) || $_ipn_mode !== 'hmac') {
            return $this->returnIpnError('IPN Mode is not HMAC');
        }

        if (!isset($_HTTP_HMAC) || empty($_HTTP_HMAC)) {
            return $this->returnIpnError('No HMAC signature sent.');
        }

        $requestcontent = file_get_contents('php://input');
        if ($requestcontent === FALSE || empty($requestcontent)) {
            return $this->returnIpnError('Error reading POST data');
        }

        if (!isset($_merchant) || $_merchant !== $MID) {
            return $this->returnIpnError('No or incorrect Merchant ID passed');
        }

        $hmac = hash_hmac('sha512', $requestcontent, $IPNS);
        if (!hash_equals($hmac, $_HTTP_HMAC)) {
            return $this->returnIpnError('HMAC signature does not match');
        }
        // HMAC Signature verified at this point, load some variables

        // Check the original currency to make sure the buyer didn't change it.
        if ($_currency1 !== $CUR) {
            return $this->returnIpnError('Original currency mismatch!', 'Currency1: ' . $_currency1 . ' - Order currency: ' . $CUR);
        }

        // Check amount against order total
        if ($_amount1 < $transaction->amount) {
            return $this->returnIpnError('Amount is less than order total!', 'Amount1: ' . $_amount1 . ' - Order Total: ' . $transaction->amount);
        }

        //Start process order
        //Process withdrawal response
        if ($_ipn_type == 'withdrawal') {
            //Numeric status of the withdrawal, currently <0 = failed, 0 = waiting email confirmation, 1 = pending, and 2 = sent/complete.
            if ($_status == 2) {
                //Update tx_id from internal Coinpayments id to blockchain tx_id
                $transaction->update(['tx_id' => $_block_txn_id ?? $_txn_id]);
            }
        } else {
            //Paid status
            if ($_status >= 100 || $_status == 2) {
                //Update transaction
                $transaction->update(['status' => 'paid', 'tx_id' => $_block_txn_id ?? $_txn_id, 'paid_amount' => $_amount1, 'response' => json_encode($request->all(), JSON_PRETTY_PRINT)]);

                //Process deposit
                $this->depositConfirmed($transaction);
            } //Waiting for confirmations
            elseif ($_status == 1) {
                //Update transaction status
                $transaction->update(['status' => 'processing']);
            } //Other status cancelled/timeout/etc
            elseif ($_status < 0) {
                //Update transaction status
                $transaction->update(['status' => 'canceled', 'cancel_reason' => $request->input('status_text')]);
            }
        }
        //Stop Coinpayments IPN notifications
        return ['status' => 'success', 'message' => 'IPN OK'];
    }
}