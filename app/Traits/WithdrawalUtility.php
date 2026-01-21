<?php

namespace App\Traits;

use App\Models\UserLog;
use App\Models\Withdrawal;

trait WithdrawalUtility
{
    private function paidWithdrawal(Withdrawal $withdrawal, $response, $tx_id = null, $total_amount = null, $fee = null)
    {
        //Update withdrawal
        $withdrawal->update([
            'paid_amount' => $total_amount,
            'fees' => $withdrawal->fees + $fee,
            'tx_id' => $tx_id,
            'response' => json_encode($response, JSON_PRETTY_PRINT),
            'status' => 'paid',
        ]);
    }

    private function createWithdrawalUserLog($amount, $user_id)
    {
        UserLog::create([
            'user_id' => $user_id,
            'type' => 'withdrawal',
            'description' => 'You have successfully received a payment of '.$amount. ' '. setting('currency_code'). '!',
        ]);
    }
}