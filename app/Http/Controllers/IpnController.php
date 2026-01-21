<?php

namespace App\Http\Controllers;

use App\Models\ErrorLog;
use App\Traits\Gateways\CoingateTrait;
use App\Traits\Gateways\CoinpaymentsTrait;
use App\Traits\Gateways\CryptApiTrait;
use App\Traits\Gateways\FaucetPayTrait;
use App\Traits\Gateways\GoUrlTrait;
use App\Traits\Gateways\PayKassaTrait;
use App\Traits\Gateways\SendBitTrait;
use Illuminate\Http\Request;

class IpnController extends Controller
{
    use CoinpaymentsTrait, PayKassaTrait, GoUrlTrait, CoingateTrait, CryptApiTrait, SendBitTrait, FaucetPayTrait;

    public function success()
    {
        return redirect()->route('account')->with('success', 'Your payment was successful! After some confirmations from Blockchain your payment will be processed.');
    }

    public function fail()
    {
        return redirect()->route('account')->with('error', 'Your payment has not been approved! Please try again or contact us if the problem persists.');
    }

    /**
     * @param Request $request
     * @return string
     */
    public function ipnCoinpayments(Request $request)
    {
        $result = $this->coinpaymentsIpn($request);

        if($result['status'] !== 'success') {
            return $this->errorAndDie($result['type'], $result['message'], $result['params'], $result['id'], $result['user_id']);
        }
        echo $result['message'];
    }

    public function ipnPaykassa(Request $request)
    {
        //ip validation
        if(!in_array($request->server('REMOTE_ADDR'), ['54.37.60.196', '51.91.80.241', '138.68.137.53', '165.232.140.156', '2604:a880:4:1d0::1d1:d000'])){
            return $this->errorAndDie('deposit', 'Invalid remote ip: #' . $request->server('REMOTE_ADDR'));
        }

        $result = $this->paykassaIpn($request);

        if($result['status'] !== 'success') {
            return $this->errorAndDie($result['type'], $result['message'], $result['params'], $result['id'], $result['user_id']);
        }
        echo $result['message'];
    }

    public function paykassaSuccess(Request $request)
    {
        //ip validation
        if(!in_array($request->server('REMOTE_ADDR'), ['54.37.60.196', '51.91.80.241', '138.68.137.53', '165.232.140.156', '2604:a880:4:1d0::1d1:d000'])){
            return $this->errorAndDie('deposit', 'Invalid remote ip: #' . $request->server('REMOTE_ADDR'));
        }

        $result = $this->paykassaIpnSuccess($request);

        if($result['status'] !== 'success') {
            return $this->errorAndDie($result['type'], $result['message'], $result['params'], $result['id'], $result['user_id']);
        }

        return response($result['message'], 200)->header('Content-Type', 'text/plain');
    }

    public function ipnGourl(Request $request)
    {
        //ip validation
        if(!in_array($request->server('REMOTE_ADDR'), ['51.77.89.176', '51.68.180.216'])){
            return $this->errorAndDie('deposit', 'Invalid remote ip: #' . $request->server('REMOTE_ADDR'));
        }

        $result = $this->goUrlIpn($request);

        if($result['status'] !== 'success') {
            return $this->errorAndDie($result['type'], $result['message'], $result['params'], $result['id'], $result['user_id']);
        }
        echo $result['message'];
    }

    public function ipnCoingate(Request $request)
    {
        $MODE = setting('coingate_mode');
        if ($MODE === 'sandbox') {
            $IPS = array('18.184.112.162');
        } else {
            $IPS = array('52.28.22.118', '35.156.68.160', '35.156.140.163');
        }
        //ip validation
        if(!in_array($request->server('REMOTE_ADDR'), $IPS)){
            return $this->errorAndDie('deposit', 'Invalid remote ip: #' . $request->server('REMOTE_ADDR'));
        }

        $result = $this->coinGateIpn($request);

        if($result['status'] !== 'success') {
            return $this->errorAndDie($result['type'], $result['message'], $result['params'], $result['id'], $result['user_id']);
        }
        echo $result['message'];
    }

    public function ipnCryptApi(Request $request)
    {
        //ip validation
        if(!in_array($request->server('REMOTE_ADDR'), ['145.239.119.223', '135.125.112.47'])){
            return $this->errorAndDie('deposit', 'Invalid remote ip: #' . $request->server('REMOTE_ADDR'));
        }

        $result = $this->cryptApiIpn($request);

        if($result['status'] !== 'success') {
            return $this->errorAndDie($result['type'], $result['message'], $result['params'], $result['id'], $result['user_id']);
        }
        echo $result['message'];
    }

    public function ipnSendBit(Request $request, $invoice)
    {
        $result = $this->sendBitIpn($request, $invoice);

        if($result['status'] !== 'success') {
            return $this->errorAndDie($result['type'], $result['message'], $result['params'], $result['id'], $result['user_id']);
        }
        echo $result['message'];
    }

    public function ipnFaucetPay(Request $request)
    {
        $result = $this->faucetpayIpn($request);

        if($result['status'] !== 'success') {
            return $this->errorAndDie($result['type'], $result['message'], $result['params'], $result['id'], $result['user_id']);
        }
        echo $result['message'];
    }

    /**
     * @param $type
     * @param $error_msg
     * @param null $params
     * @param null $id
     * @param null $user_id
     */
    private function errorAndDie($type, $error_msg, $params = null, $id = null, $user_id = null)
    {
        $report = 'Error Type: ' . $error_msg . "\n\n";
        if ($params) {
            $encoded = json_encode($params);
            $report .= "-------Start Params--------\n";
            $report .= "\n{$encoded}\n";
            $report .= "-------End Params--------\n\n";
        }
        //Insert ipn error
        $log = new ErrorLog();
        $log->type = $type;
        $log->message = $error_msg;
        $log->response = json_encode($report);
        if (isset($id)) {
            if($type === 'deposit'){
                $log->deposit_id = $id;
            }
            if($type === 'withdrawal'){
                $log->withdrawal_id = $id;
            }
        }
        if (isset($user_id)) {
            $log->user_id = $user_id;
        }
        $log->save();
        die('IPN Error: ' . $error_msg);
    }
}
