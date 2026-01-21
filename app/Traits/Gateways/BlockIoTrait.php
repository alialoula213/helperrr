<?php

namespace App\Traits\Gateways;

use App\Models\Withdrawal;
use App\Traits\WithdrawalUtility;
use BlockIo\APIException;
use BlockIo\Client as BlockIo;

trait BlockIoTrait
{
    use WithdrawalUtility;

    protected function blockioWithdrawal(Withdrawal $withdrawal, $error_reporting)
    {
        $api_key = setting('blockio_mode') === 'live' ? setting('blockio_api') : setting('blockio_testapi');
        $block_io = new BlockIo($api_key, setting('blockio_pin'), 2);
        try {
            $formatted_amount = number_format($withdrawal->amount, 8, '.', '');
            //Estimate fee
            $fees = $block_io->get_network_fee_estimate([
                'amounts' => $formatted_amount,
                'to_addresses' => $withdrawal->user->wallet,
            ]);
            if($fees->status == 'success'){
                $total_amount = $formatted_amount;
                $fee = null;
                //Calculate fees
                if(setting('blockio_withdrawal_fee') === 'user'){
                    $total_amount = number_format($withdrawal->amount - $fees->data->estimated_network_fee, 8, '.', '');
                    $fee = $fees->data->estimated_network_fee;
                }
                //Prepare withdrawal
                $prepare = $block_io->prepare_transaction([
                    'amounts' => $total_amount,
                    'to_addresses' => $withdrawal->user->wallet,
                ]);
                //Check prepared withdrawal
                if($prepare->status != 'success'){
                    //Log error
                    payment_log('withdrawal', $prepare->data->error_message, $withdrawal, $prepare);
                    logger()->error('Block.io Error: '. $prepare->data->error_message);
                    return false;
                }
                //Create and sign withdrawal
                $create_and_sign = $block_io->create_and_sign_transaction($prepare);
                //Submit withdrawal
                $cashout = $block_io->submit_transaction(['transaction_data' => $create_and_sign]);
                if ($cashout->status == 'success') {
                    //Update withdrawal
                    $this->paidWithdrawal($withdrawal, $cashout, $cashout->data->txid, $total_amount, $fee);
                    //Create user log
                    $this->createWithdrawalUserLog($total_amount, $withdrawal->user->id);
                    return true;
                }
                if($error_reporting){
                    return  $cashout->data->error_message;
                }
                //Log error
                payment_log('withdrawal', $cashout->data->error_message, $withdrawal, $cashout);
                logger()->error('Block.io Error: '. $cashout->data->error_message);
                return false;
            }
        }catch (APIException $e){
            if($error_reporting){
                return $e->getMessage();
            }
            //Log error
            payment_log('withdrawal', $e->getMessage(), $withdrawal);
            logger()->error('Block.io Error: '. $e->getMessage());
            return false;
        }
    }
}