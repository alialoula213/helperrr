<?php

namespace App\Traits\Gateways;

use App\Libraries\SendBitApi;
use App\Models\Deposit;
use App\Traits\DepositTrait;
use Illuminate\Http\Request;

trait SendBitTrait
{
    use DepositTrait;

    protected function sendbit(Deposit $deposit)
    {
        $sendBit = new SendBitApi(setting('sendbit_api_key'), setting('sendbit_api_secret'));
        $sendBit->setCoin(setting('deposit_currency_code'));
        $sendBit->setField("callback_url", route('ipn.sendbit', ['invoice' => $deposit->invoice]));
        $sendBit->setMethod("generate-address");
        $response = $sendBit->process();

        if($response["response_code"] != 200) {
            //Log error
            payment_log('deposit', $response["response_message"], $deposit, $response);
            logger()->error('SendBit Error: '. $response["response_message"]);
            return false;
        }
        $timeout = now()->addHours(setting('sendbit_timeout'));
        //Update deposit with response
        $deposit->update(['response' => json_encode($response), 'qrcode' => $response["qr_code"], 'invoice_expire_date' => $timeout]);
        return true;
    }

    private function sendBitIpn(Request $request, $invoice)
    {
        //inputs
        $transaction_id = $request->input("transaction_id");
        $address = $request->input("address");
        $amount = $request->input("amount");
        $confirmations = $request->input("confirmations");
        $hash = $request->input("hash");
        $auth_hmac = $request->input("auth_hmac");

        //Get transaction
        $transaction = Deposit::whereInvoice($invoice)->where('status', '!=', 'paid')->first();

        //Check if transaction exists
        if ($transaction === null) {
            return $this->returnIpnError('Transaction not found. Tid: #' . $invoice, $request->all());
        }

        $sendBit = new SendBitApi(setting('sendbit_api_key'), setting('sendbit_api_secret'));
        $valid = $sendBit->validatePayment($hash, $auth_hmac);

        if($valid){
            //Check amount
            if ($amount < $transaction->amount) {
                return $this->returnIpnError('Amount is less than order total!', 'Amount1: ' . $amount . ' - Order Total: ' . $transaction->amount);
            }
            if($confirmations > 0 && $confirmations < 3){
                //Update transaction status
                $transaction->update(['status' => 'processing']);
                return ['status' => 'success', 'message' => 'Processing Payment'];
            }
            //Update transaction
            $transaction->update(['status' => 'paid', 'tx_id' => $transaction_id, 'paid_amount' => $amount, 'response' => json_encode($request->all(), JSON_PRETTY_PRINT)]);

            //Process deposit
            $this->depositConfirmed($transaction);
            return ['status' => 'success', 'message' => 'Payment confirmed'];
        }
        return $this->returnIpnError('Could not verify the payment', $request->all());
    }
}