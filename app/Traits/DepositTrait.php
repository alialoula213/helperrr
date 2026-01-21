<?php

namespace App\Traits;

use App\Models\Deposit;
use App\Models\User;
use App\Models\UserMiningPower;

trait DepositTrait
{
    private $input_request;
    private $transaction_type = 'deposit';
    private $transaction_id = null;
    private $user_id = null;

    private function depositConfirmed(Deposit $deposit)
    {
        //Create expiration date
        $expire_date = \Carbon\Carbon::now()->addDays(setting('period'))->toDateTimeString();
        //Create user mining
        UserMiningPower::create([
            'user_id' => $deposit->user_id,
            'power' => $deposit->power,
            'expire_date' => $expire_date
        ]);
        //Create user log
        newUserLog($deposit->user_id, 'deposit', [
            'power' => $deposit->power,
            'amount' => $deposit->amount,
            'expire_date' => $expire_date,
        ]);
        //Flush user mining power cache
        \Cache::forget('mining_power_user_'.$deposit->user_id);

        //Allow user to withdrawal
        if(!$deposit->user->allow_withdrawal){
            $deposit->user()->update(['allow_withdrawal' => 1]);
        }

        //Check referrer
        if($deposit->user->ref_id != null) {
            $referrer = User::find($deposit->user->ref_id);
            if($referrer != null) {
                //calculate commission
                $commission = currency_format($deposit->amount * setting('referral_bonus') / 100, 8);
                //increment user balance
                $referrer->increment('balance', $commission);
                //create ref user log
                newUserLog($referrer->id, 'affiliate', $commission);
            }
        }
    }

    private function returnIpnError($message, $params = null)
    {
        return [
            'status' => 'error',
            'message' => $message,
            'type' => $this->transaction_type ?? 'deposit',
            'params' => $params ?? $this->input_request,
            'id' => $this->transaction_id,
            'user_id' => $this->user_id
        ];
    }
}