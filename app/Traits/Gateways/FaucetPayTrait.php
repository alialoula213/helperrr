<?php

namespace App\Traits\Gateways;

use App\Models\Deposit;
use App\Models\Withdrawal;
use App\Traits\DepositTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

trait FaucetPayTrait
{
    use DepositTrait;

    protected function faucetpay(Deposit $deposit)
    {
        $description = setting('site_name'). ' Purchase of '.$deposit->power. ' '. setting('hashpower_unit'). '/s';

        //Generate URL
        $url = 'https://faucetpay.io/merchant/webscr';
        $params['merchant_username'] = setting('faucetpay_username');
        $params['item_description'] = html_entity_decode($description, ENT_QUOTES, 'UTF-8');
        $params['amount1'] = $deposit->amount;
        $params['currency1'] = setting('deposit_currency_code');
        $url .= '?' . http_build_query($params);

        //Return url
        return true;
        //return $url;
    }

    protected function faucetpayWithdrawal(Withdrawal $withdrawal, $error_reporting)
    {
        //Amount to satoshi
        $satoshi_amount = $withdrawal->amount * 100000000;

        //Send Payment
        $client = Http::asForm()->post('https://faucetpay.io/api/v1/send', [
            'api_key' => setting('faucetpay_api_key'),
            'to' => $withdrawal->user->wallet,
            'amount' => $satoshi_amount,
            'currency' => setting('withdrawal_currency_code'),
            'ip' => request()->server('SERVER_ADDR'),
        ]);

        $response = json_decode($client->body(), false);

        if($client->status() == 200 && $response->status == 200) {
            //Update withdrawal
            $this->paidWithdrawal($withdrawal, $response, $response->payout_user_hash);
            //Create user log
            $this->createWithdrawalUserLog($withdrawal->amount, $withdrawal->user->id);
            return true;
        }

        if($error_reporting){
            return $response->message;
        }
        //Log error
        payment_log('withdrawal', $response->message, $withdrawal, $response);
        logger()->error('FaucetPay Error: '. $response->message);
        return false;
    }

    public function faucetpayCheckWalletAddress(Withdrawal $withdrawal)
    {
        //Check address
        $client = Http::asForm()->post('https://faucetpay.io/api/v1/checkaddress', [
            'api_key' => setting('faucetpay_api_key'),
            'address' => $withdrawal->user->wallet,
            'currency' => setting('withdrawal_currency_code'),
        ]);

        $response = json_decode($client->body(), false);

        if($client->status() == 200 && $response->status == 200) {
            return true;
        }
        return false;
    }

    public function faucetpayIpn(Request $request)
    {
        $token = $request->input('token');

        $payment_info = file_get_contents("https://faucetpay.io/merchant/get-payment/" . $token);
        $payment_info = json_decode($payment_info, true);
        $token_status = $payment_info['valid'];

        $merchant_username = $payment_info['merchant_username'];
        $amount1 = $payment_info['amount1'];
        $currency1 = $payment_info['currency1'];
        $amount2 = $payment_info['amount2'];
        $currency2 = $payment_info['currency2'];
        $invoice = $payment_info['custom'];
        $transaction_id = $payment_info['transaction_id'];

        $my_username = setting('faucetpay_username');

        if ($my_username == $merchant_username && $token_status == true) {

            //Get transaction
            $transaction = Deposit::whereInvoice($invoice)->where('status', '!=', 'paid')->first();

            //Check if transaction exists
            if ($transaction === null) {
                return $this->returnIpnError('Transaction not found. Tid: #' . $invoice, $request->all());
            }
            //Check amount
            if ($amount1 < $transaction->amount) {
                return $this->returnIpnError('Amount is less than order total!', 'Amount1: ' . $amount1 . ' - Order Total: ' . $transaction->amount);
            }
            //Check currency
            if ($currency1 != setting('deposit_currency_code')) {
                return $this->returnIpnError('Original currency mismatch!', 'Currency1: ' . $currency1 . ' - Order currency: ' . setting('deposit_currency_code'));
            }
            //Update transaction
            $transaction->update(['status' => 'paid', 'tx_id' => $transaction_id, 'paid_amount' => $amount1, 'response' => json_encode($payment_info, JSON_PRETTY_PRINT)]);

            //Process deposit
            $this->depositConfirmed($transaction);
            return ['status' => 'success', 'message' => 'Payment confirmed'];
        }
        return $this->returnIpnError('Could not verify the payment', $payment_info);
    }
}