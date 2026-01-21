<?php

namespace App\Traits\Gateways;

use App\Libraries\PayKassaAPI;
use App\Libraries\PayKassaSCI;
use App\Models\Deposit;
use App\Models\Withdrawal;
use App\Traits\DepositTrait;
use Illuminate\Http\Request;

trait PayKassaTrait
{
    use DepositTrait;

    protected function paykassa(Deposit $deposit)
    {
        $paykassa = new PayKassaSCI(setting('paykassa_mid'), setting('paykassa_secret'));
        $system = strtolower(setting('paykassa_api_currency'));
        //Currencies
        $system_id = [
            "bitcoin" => 11, // supported currencies BTC
            "ethereum" => 12, // supported currencies ETH
            "litecoin" => 14, // supported currencies LTC
            "dogecoin" => 15, // supported currencies DOGE
            "dash" => 16, // supported currencies DASH
            "bitcoincash" => 18, // supported currencies BCH
            "zcash" => 19, // supported currencies ZEC
            "ripple" => 22, // supported currencies XRP
            "tron" => 27, // supported currencies TRX
            "stellar" => 28, // supported currencies XLM
            "binancecoin" => 29, // supported currencies BNB
            "tron_trc20" => 30, // supported currencies USDT
            "binancesmartchain_bep20" => 31, // supported currencies USDT, BUSD, USDC, ADA, EOS, BTC, ETH, DOGE
            "ethereum_erc20" => 32, // supported currencies USDT
        ];
        //Create
        $pk_transaction = $paykassa->sci_create_order_get_data(
            $deposit->amount,
            setting('deposit_currency_code'),
            $deposit->invoice,
            setting('site_name'). ' Purchase of '.$deposit->power. ' '. setting('hashpower_unit'). '/s',
            $system_id[$system]
        );
        if (!$pk_transaction['error']) {
            $timeout = setting('paykassa_timeout') * 60 * 60; // hours to seconds
            //Generate QrCode
            $qrcode = $this->generateQrCode($pk_transaction['data']['amount'], $pk_transaction['data']['wallet'], $timeout, setting('paykassa_confirmations'));
            //Update deposit with response
            $deposit->update(['response' => json_encode($pk_transaction), 'qrcode' => $qrcode, 'invoice_expire_date' => now()->addSeconds($timeout)]);
            return true;
        }
        //Log error
        payment_log('deposit', $pk_transaction['message'], $deposit, $pk_transaction);
        logger()->error('PayKassa Error: '. $pk_transaction['message']);
        return false;
    }

    protected function paykassaWithdrawal(Withdrawal $withdrawal, $error_reporting)
    {
        $paykassa = new PayKassaAPI(setting('paykassa_api_id'), setting('paykassa_api_secret'));
        $paykassa_currency = setting('paykassa_api_currency');
        $paykassa_api_priority = setting('paykassa_api_priority');
        $system_id = [
            "perfectmoney" => 2, // supported currencies USD
            "berty" => 7, // supported currencies RUB, USD
            "bitcoin" => 11, // supported currencies BTC
            "ethereum" => 12, // supported currencies ETH
            "litecoin" => 14, // supported currencies LTC
            "dogecoin" => 15, // supported currencies DOGE
            "dash" => 16, // supported currencies DASH
            "bitcoincash" => 18, // supported currencies BCH
            "zcash" => 19, // supported currencies ZEC
            "ripple" => 22, // supported currencies XRP
            "tron" => 27, // supported currencies TRX
            "stellar" => 28, // supported currencies XLM
            "binancecoin" => 29, // supported currencies BNB
            "tron_trc20" => 30, // supported currencies USDT
            "binancesmartchain_bep20" => 31, // supported currencies USDT, BUSD, USDC, ADA, EOS, BTC, ETH, DOGE
            "ethereum_erc20" => 32, // supported currencies USDT
        ];
        $comment = setting('site_name'). ' Withdrawal Payment ID#'.$withdrawal->id;
        $paid_commission = setting('paykassa_fee') ? 'shop' : 'client';

        //Create
        $create = $paykassa->api_payment(setting('paykassa_mid'), $system_id[$paykassa_currency], $withdrawal->user->wallet, $withdrawal->amount, setting('withdrawal_currency_code'), $comment, $paid_commission, null, true, $paykassa_api_priority);
        if ($create['error']) {
            if($error_reporting){
                return  $create['message'];
            }
            //Log error
            payment_log('withdrawal', $create['message'], $withdrawal, $create);
            logger()->error('PayKassa Error: '. $create['message']);
            return false;
        }
        $paid_fee = setting('paykassa_fee') ? null : $create['data']['shop_comission_amount'];
        //Update withdrawal
        $this->paidWithdrawal($withdrawal, $create, $create['data']['txid'], $create['data']['amount'], $paid_fee);
        //Create user log
        $this->createWithdrawalUserLog($create['data']['amount'], $withdrawal->user->id);
        return true;
    }

    public function paykassaIpnSuccess(Request $request)
    {
        //Paykassa settings
        $pk_mid = setting('paykassa_mid');
        $pk_sec = setting('paykassa_secret');

        $paykassa = new PayKassaSci($pk_mid, $pk_sec);

        $res = $paykassa->sci_confirm_order();

        if ($res['error']) {
            return $this->returnIpnError($res['message'], $res);
        }
        // Results actions in case of success
        $id = $res["data"]["order_id"];        // unique numeric identifier of the payment in your system, example: 150800

        return ['status' => 'success', 'message' => $id . '|success'];
    }

    public function paykassaIpn(Request $request)
    {
        //Paykassa settings
        $pk_mid = setting('paykassa_mid');
        $pk_sec = setting('paykassa_secret');
        $pk_confirmations = setting('paykassa_confirmations');

        $paykassa = new PayKassaSci($pk_mid, $pk_sec);

        $res = $paykassa->sci_confirm_transaction_notification();

        if ($res['error']) {
            return $this->returnIpnError($res['message'], $res);
        }
        // Results actions in case of success
        $id = $res["data"]["order_id"];              // unique numeric identifier of the payment in your system, example: 150800
        $amount = (float)$res["data"]["amount"];    // invoice amount example: 1.0000000
        $fee = (float)$res["data"]["fee"];          // payment processing fee: 0.0000000
        $confirmations = $res["data"]["confirmations"];     // Current number of network confirmations
        $required_confirmations = $res["data"]["required_confirmations"];         // Required number of network confirmations for crediting
        $status = $res["data"]["status"];                   // yes - if the payment is credited
        $txid = $res["data"]["txid"];                       // A transaction in a cryptocurrency network, an example: 0xb97189db3555015c46f2805a43ed3d700a706b42fb9b00506fbe6d086416b602

        //Get transaction
        $transaction = Deposit::whereInvoice($id)->where('status', '!=', 'paid')->first();

        //Check if transaction exists
        if ($transaction === null) {
            return $this->returnIpnError('Transaction not found. Tid: #' . $id, $request->all());
        }
        $this->transaction_id = $transaction->id;
        $this->user_id = $transaction->user_id;

        if($status === 'yes'){
            // Check paid amount
            if($amount >= $transaction->amount){
                //Update transaction
                $transaction->update(['status' => 'paid', 'tx_id' => $txid, 'paid_amount' => $amount, 'response' => json_encode($res, JSON_PRETTY_PRINT)]);

                //Process deposit
                $this->depositConfirmed($transaction);
            }
            return $this->returnIpnError('Value below required: #' . $id . 'Required: '.$transaction->amount.'. Total Paid: '.$amount, $request->all());
        }

        return ['status' => 'success', 'message' => $id . '|success'];
    }
}