<?php

namespace App\Traits\Gateways;

use App\Models\Deposit;
use App\Libraries\Cryptobox;
use App\Traits\DepositTrait;
use Illuminate\Http\Request;

trait GoUrlTrait
{
    use DepositTrait;

    protected function gourl(Deposit $deposit)
    {
        $gourl = new Cryptobox([
            "public_key" => setting('gourl_pbk'),
            "private_key" => setting('gourl_pvk'),
            "orderID" => $deposit->invoice,
            "userID" => $deposit->user_id,   // optional
            "userFormat" => "SESSION",  // SESSION, IPADDRESS, COOKIE
            "amount" => $deposit->amount,
            "period" => "NOEXPIRY",    // 2 HOUR, 1 DAY, 1 MONTH, NOEXPIRY, etc..
            "iframeID" => "",    // optional
            "language" => "EN",
            "webdev_key" => 'DEV1847GF5091234D52D9DCG252955852'
        ]);
        $api_response = $gourl->cryptobox_json_url();
        $json_response = \Http::get($api_response);
        if (!$json_response->successful()) {
            //Log error
            payment_log('deposit', 'GoURL error', $deposit, $json_response->body());
            logger()->error('GoURL Error: '. $json_response);
            return false;
        }
        $qrcode = json_decode($json_response->body())->wallet_url;
        //Update response
        $deposit->update(['response' => $json_response->body(), 'qrcode' => 'https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=' . $qrcode, 'invoice_expire_date' => now()->addHours(setting('gourl_timeout'))]);
        return true;
    }

    /**
     * @param Request $request
     * @return array|string[]|void
     */
    private function goUrlIpn(Request $request)
    {
        // a. check if private key valid
        $valid_key = false;
        $private_key_hash = $request->input('private_key_hash');

        if (isset($private_key_hash) && strlen($private_key_hash) == 128 && preg_replace('/[^A-Za-z0-9]/', '', $private_key_hash) == $private_key_hash) {
            $keyshash = array();
            $arr = explode("^", setting('gourl_pvk'));
            foreach ($arr as $v) {
                $keyshash[] = strtolower(hash("sha512", $v));
            }
            if (in_array(strtolower($private_key_hash), $keyshash)) {
                $valid_key = true;
            }
        }

        // b. alternative - ajax script send gourl.io json data
        if (!$valid_key && isset($_POST["json"]) && $_POST["json"] == "1") {
            $data_hash = $boxID = "";
            if (isset($_POST["data_hash"]) && strlen($_POST["data_hash"]) == 128 && preg_replace('/[^A-Za-z0-9]/', '', $_POST["data_hash"]) == $_POST["data_hash"]) {
                $data_hash = strtolower($_POST["data_hash"]);
                unset($_POST["data_hash"]);
            }
            if (isset($_POST["box"]) && is_numeric($_POST["box"]) && $_POST["box"] > 0) {
                $boxID = (int)$_POST["box"];
            }

            if ($data_hash && $boxID) {
                $private_key = "";
                $arr = explode("^", setting('gourl_pvk'));
                foreach ($arr as $v) {
                    if (strpos($v, $boxID . "AA") === 0) {
                        $private_key = $v;
                    }
                }

                if ($private_key) {
                    $data_hash2 = strtolower(hash("sha512", $private_key . json_encode($_POST) . $private_key));
                    if ($data_hash == $data_hash2) {
                        $valid_key = true;
                    }
                }
                unset($private_key);
            }
            if (!$valid_key) {
                return $this->errorAndDie("Error! Invalid Json Data sha512 Hash!", $request->all());
            }
        }

        // c.
        if ($_POST) {
            foreach ($_POST as $k => $v) {
                if (is_string($v)) {
                    $_POST[$k] = trim($v);
                }
            }
        }

        // d.
        if (isset($_POST["plugin_ver"]) && !isset($_POST["status"]) && $valid_key) {
            return $this->errorAndDie("cryptoboxver_" . "php_" . CRYPTOBOX_VERSION, $request->all());
        }

        // e.
        if (isset($_POST["status"]) && in_array($_POST["status"], array("payment_received", "payment_received_unrecognised")) &&
            $_POST["box"] && is_numeric($_POST["box"]) && $_POST["box"] > 0 && $_POST["amount"] && is_numeric($_POST["amount"]) && $_POST["amount"] > 0 && $valid_key) {

            foreach ($_POST as $k => $v) {
                if ($k == "datetime") {
                    $mask = '/[^0-9\ \-\:]/';
                } elseif (in_array($k, array("err", "date", "period"))) {
                    $mask = '/[^A-Za-z0-9\.\_\-\@\ ]/';
                } else {
                    $mask = '/[^A-Za-z0-9\.\_\-\@]/';
                }
                if ($v && preg_replace($mask, '', $v) != $v) {
                    $_POST[$k] = "";
                }
            }

            if (!$_POST["amountusd"] || !is_numeric($_POST["amountusd"])) {
                $_POST["amountusd"] = 0;
            }
            if (!$_POST["confirmed"] || !is_numeric($_POST["confirmed"])) {
                $_POST["confirmed"] = 0;
            }

            //Get transaction
            $transaction = Deposit::whereInvoice($request->input('order'))->where('status', '!=', 'paid')->first();

            //Check if transaction exists
            if ($transaction === null) {
                return $this->errorAndDie('Transaction not found. Tid: #' . $request->input('order'), $request->all());
            }
            $this->transaction_id = $transaction->id;
            $this->user_id = $transaction->user_id;

            if($request->input('confirmed') && $request->input('status') == 'payment_received'){
                //Update transaction
                $transaction->update(['status' => 'paid', 'tx_id' => $request->input('tx'), 'paid_amount' => $request->input('amount'), 'response' => json_encode($request->all(), JSON_PRETTY_PRINT)]);

                //Process deposit
                $this->depositConfirmed($transaction);

                return ['status' => 'success', 'message' => 'Payment confirmed'];
            }
        }

        return ['status' => 'error', 'message' => 'Only POST Data Allowed'];
    }
}