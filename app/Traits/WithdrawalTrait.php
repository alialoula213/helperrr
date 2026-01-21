<?php

namespace App\Traits;

use App\Models\Withdrawal;
use App\Traits\Gateways\CoinpaymentsTrait;
use App\Traits\Gateways\PayKassaTrait;
use App\Traits\Gateways\BlockIoTrait;
use App\Traits\Gateways\FaucetPayTrait;

trait WithdrawalTrait
{
    use CoinpaymentsTrait, PayKassaTrait, BlockIoTrait, FaucetPayTrait;

    public function requestWithdrawal(Withdrawal $withdrawal)
    {
        $mode = setting('withdrawal_method');
        $withdrawal_max_auto = setting('withdrawal_max_auto');

        if($mode === 'auto' && $withdrawal->amount <= $withdrawal_max_auto){
            return $this->autoWithdrawal($withdrawal);
        }

        if($mode === 'cron'){
            return ['success' => 'Your withdrawal request will be paid soon.'];
        }

        return ['success' => 'Your withdrawal request has been submitted.'];
    }

    private function autoWithdrawal(Withdrawal $withdrawal, $error_reporting = false)
    {
        $gateway = setting('withdrawal_gateway');
        $response = false;

        if($gateway === 'coinpayments'){
            $response = $this->coinpaymentsWithdrawal($withdrawal, $error_reporting);
        }
        if($gateway === 'paykassa'){
            $response = $this->paykassaWithdrawal($withdrawal, $error_reporting);
        }
        if($gateway === 'blockio'){
            $response = $this->blockioWithdrawal($withdrawal, $error_reporting);
        }
        if($gateway === 'faucetpay'){
            //Validate address
            $validate_address = $this->faucetpayCheckWalletAddress($withdrawal);
            if(!$validate_address){
                return ['error' => 'Oops, your wallet address is not associated with a FaucetPay account.'];
            }
            $response = $this->faucetpayWithdrawal($withdrawal, $error_reporting);
        }

        if(is_bool($response) && $response){
            return ['success' => 'Your withdrawal request is being processed, you will soon receive it in your wallet.'];
        }
        if($error_reporting){
            return ['error' => $response];
        }
        return ['error' => 'Oops, there was some error during your withdrawal request. Please try again, or contact us if the problem persists.'];
    }
}