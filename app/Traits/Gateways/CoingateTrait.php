<?php

namespace App\Traits\Gateways;

use App\Models\Deposit;
use App\Traits\DepositTrait;
use CoinGate\Merchant\Order as CoingateOrder;
use CoinGate\CoinGate;
use Illuminate\Http\Request;

trait CoingateTrait
{
    use DepositTrait;

    protected function coingate(Deposit $deposit)
    {
        try {
            //Create
            $cp_transaction = [
                'order_id' => $deposit->invoice,
                'price_amount' => $deposit->amount,
                'price_currency' => setting('deposit_currency_code'),
                'receive_currency' => setting('coingate_receive'),
                'callback_url' => route('ipn.coingate'),
                'cancel_url' => route('ipn.fail'),
                'success_url' => route('ipn.success'),
                'title' => setting('site_name'). ' Purchase of '.$deposit->power. ' '. setting('hashpower_unit'). '/s',
            ];
            $coingate = CoingateOrder::create($cp_transaction, array(), [
                'environment' => setting('coingate_mode'), // sandbox OR live
                'auth_token' => setting('coingate_auth_token'),
                'curlopt_ssl_verifypeer'    => false // default is false
            ]);
            if ($coingate) {
                //Update deposit with response
                $deposit->update(['response' => json_encode($coingate)]);
                //Return url
                return $coingate->payment_url;
            }
            //Log error
            payment_log('deposit', 'Unknown error when trying to create invoice', $deposit, $coingate);
            logger()->error('CoinGate Error: Unknown error when trying to create invoice');
            return false;
        }catch (\Exception $error){
            //Log error
            payment_log('deposit', $error->getMessage(), $deposit);
            logger()->error('CoinGate Error: '. $error->getMessage());
            return false;
        }
    }

    private function coinGateIpn(Request $request)
    {
        $_order_id = $request->input('order_id');
        $_txn_id = $request->input('id');
        $_amount1 = $request->input('price_amount');
        $_status = $request->input('status');

        //Get transaction
        $transaction = Deposit::whereInvoice($_order_id)->where('status', '!=', 'paid')->first();

        //Check if transaction exists
        if ($transaction === null) {
            return $this->returnIpnError('Transaction not found. Tid: #' . $_order_id, $request->all());
        }
        $this->transaction_id = $transaction->id;
        $this->user_id = $transaction->user_id;

        if ($_status == 'paid') {
            if ($_amount1 >= $transaction->amount) {
                //Update transaction
                $transaction->update(['status' => 'paid', 'tx_id' => $_txn_id, 'paid_amount' => $_amount1, 'response' => json_encode($request->all(), JSON_PRETTY_PRINT)]);

                //Process deposit
                $this->depositConfirmed($transaction);
                return ['status' => 'success', 'message' => 'Payment confirmed'];
            }
            return $this->returnIpnError('Amount is less than order total!', $request->all());
        }
        if ($_status == 'confirming') {
            if ($_amount1 >= $transaction->amount) {
                //Update transaction status
                $transaction->update(['status' => 'processing']);
                return ['status' => 'success', 'message' => 'Processing Payment'];
            }
            return $this->returnIpnError('Amount is less than order total!', $request->all());
        }
        if ($_status == 'invalid' || $_status == 'expired' || $_status == 'canceled') {
            //Cancel transaction
            $transaction->update(['status' => 'canceled', 'cancel_reason' => $_status]);
            return ['status' => 'error', 'message' => 'Payment canceled or expired!'];
        }
    }
}