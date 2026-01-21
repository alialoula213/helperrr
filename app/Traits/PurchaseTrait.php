<?php

namespace App\Traits;

use App\Models\Deposit;
use App\Traits\Gateways\CoingateTrait;
use App\Traits\Gateways\CoinpaymentsTrait;
use App\Traits\Gateways\CryptApiTrait;
use App\Traits\Gateways\FaucetPayTrait;
use App\Traits\Gateways\GoUrlTrait;
use App\Traits\Gateways\PayKassaTrait;
use App\Traits\Gateways\SendBitTrait;

trait PurchaseTrait
{
    use CoinpaymentsTrait, PayKassaTrait, CoingateTrait, GoUrlTrait, CryptApiTrait, SendBitTrait, FaucetPayTrait;
    /**
     * @param $power_amount
     * @return mixed
     */
    public function createDeposit($power_amount)
    {
        $total_amount = $power_amount * setting('hashpower_price');
        return Deposit::create([
            'user_id' => auth()->user()->id,
            'invoice' => \Str::random(16),
            'power' => $power_amount,
            'amount' => $total_amount,
        ]);
    }

    public function createDepositAddress(Deposit $deposit)
    {
        $deposit_gateway = setting('deposit_gateway');
        if($deposit_gateway === 'coinpayments'){
            return $this->coinpayments($deposit);
        }
        if($deposit_gateway === 'paykassa'){
            return $this->paykassa($deposit);
        }
        if($deposit_gateway === 'gourl'){
            return $this->gourl($deposit);
        }
        if($deposit_gateway === 'coingate'){
            return $this->coingate($deposit);
        }
        if($deposit_gateway === 'cryptapi'){
            return  $this->cryptapi($deposit);
        }
        if($deposit_gateway === 'sendbit'){
            return $this->sendbit($deposit);
        }
        if($deposit_gateway === 'faucetpay'){
            return $this->faucetpay($deposit);
        }
    }

    /**
     * Generate QrCode
     * @param $amount
     * @param $address
     * @param $timeout
     * @param $confirmations
     * @return false|string
     */
    private function generateQrCode($amount, $address, $timeout, $confirmations)
    {
        $currency = strtolower(setting('currency_name'));
        $deposit['amount'] = sprintf('%.08f', $amount);
        $deposit['address'] = $address;
        $deposit['QR'] = strtolower($currency) . ":" . $deposit['address'] . "?amount=" . $deposit['amount'];
        $deposit['qrcode'] = "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=" . $deposit['QR'] . "&choe=UTF-8";
        $deposit['timeout'] = $timeout;
        $deposit['confirmations'] = $confirmations;

        return json_encode($deposit);
    }
}