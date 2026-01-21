<?php

namespace App\Traits\Gateways;

use App\Models\Deposit;
use App\Libraries\CryptApi;
use App\Traits\DepositTrait;
use Illuminate\Http\Request;

trait CryptApiTrait
{
    use DepositTrait;

    protected function cryptapi(Deposit $deposit)
    {
        $network = setting('cryptapi_network');
        $coin = setting('deposit_currency_code');
        $timeout = now()->addHours(setting('cryptapi_timeout'));

        if($network !== 'crypto'){
            $coin = $network.'_'.setting('deposit_currency_code');
        }

        try{
            $cryptapi = new CryptApi(strtolower($coin), setting('cryptapi_wallet'), route('ipn.cryptapi'), [
                'invoice' => $deposit->invoice,
            ], [
                'confirmations' => setting('cryptapi_confirmations'),
            ]);
            //Create
            $payment_address = $cryptapi->get_address();

            if ($payment_address) {
                //Generate QrCode
                $qrcode = $this->generateQrCode($deposit->amount, $payment_address, $timeout, setting('cryptapi_confirmations'));
                //Update deposit with response
                $deposit->update(['response' => $qrcode, 'qrcode' => json_decode($qrcode)->qrcode, 'invoice_expire_date' => $timeout]);
                return true;
            }
        }catch (\Exception $error){
            //Log error
            payment_log('deposit', $error->getMessage(), $deposit);
            logger()->error('CryptAPI Error: '. $error->getMessage());
            return false;
        }
    }

    private function cryptApiIpn(Request $request)
    {
        $payment_data = CryptAPI::process_callback($request->all());

        //Get transaction
        $transaction = Deposit::whereInvoice($payment_data['invoice'])->where('status', '!=', 'paid')->first();

        //Check if transaction exists
        if ($transaction === null) {
            return $this->errorAndDie('Transaction not found. Tid: #' . $payment_data['invoice'], $request->all());
        }
        $this->transaction_id = $transaction->id;
        $this->user_id = $transaction->user_id;

        //Check amount
        if ($payment_data['value_coin'] < $transaction->amount) {
            return $this->returnIpnError('Amount is less than order total!', 'Amount1: ' . $payment_data['value_coin'] . ' - Order Total: ' . $transaction->amount);
        }

        if($payment_data['pending']){
            //Update transaction status
            $transaction->update(['status' => 'processing']);
            return ['status' => 'success', 'message' => 'Processing Payment'];
        }
        if($payment_data['confirmations'] >= setting('cryptapi_confirmations')){
            //Update transaction
            $transaction->update(['status' => 'paid', 'tx_id' => $payment_data['txid_in'], 'paid_amount' => $payment_data['value_coin'], 'response' => json_encode($payment_data, JSON_PRETTY_PRINT)]);

            //Process deposit
            $this->depositConfirmed($transaction);
            return ['status' => 'success', 'message' => 'Payment confirmed'];
        }
        //Cancel transaction
        $transaction->update(['status' => 'canceled', 'cancel_reason' => 'Canceled or expired']);
        return ['status' => 'error', 'message' => 'Payment canceled or expired!'];
    }
}