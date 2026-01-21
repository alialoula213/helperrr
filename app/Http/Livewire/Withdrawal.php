<?php

namespace App\Http\Livewire;

use App\Traits\WithdrawalTrait;
use Livewire\Component;

class Withdrawal extends Component
{
    use WithdrawalTrait;

    public $amount;
    public $withdrawal_error_message;
    public $min_withdraw;
    public $max_withdraw;
    public $final_amount = 0;

    public function rules()
    {
        return [
            'amount' => 'required|numeric|min:' . setting('withdrawal_min').'|max:' . auth()->user()->balance
        ];
    }

    public function render()
    {
        $this->min_withdraw = setting('withdrawal_min');
        $this->max_withdraw = auth()->user()->balance;
        return view('dashboard::livewire.withdrawal');
    }

    public function calcFees()
    {
        $this->final_amount = $this->amount - ($this->amount * setting('withdrawal_fee_percent', 0) / 100) - setting('withdrawal_fee_fixed', 0);
    }

    public function withdrawal()
    {
        unset($this->withdrawal_error_message);

        $this->validate();

        // Check if user has already withdrawn today
        $max_daily_withdrawal = setting('withdrawal_max_daily');
        if($max_daily_withdrawal){
            $withdrawal_count = auth()->user()->withdrawals()->whereDate('created_at', today())->count();
            if($withdrawal_count >= $max_daily_withdrawal){
                $this->withdrawal_error_message = 'You have reached the maximum withdrawal limit for today. Try again tomorrow.';
                return;
            }
        }

        //Avoid negative balance
        if((auth()->user()->balance - $this->amount) < 0) {
            $this->withdrawal_error_message = 'You cannot withdraw more than your available balance.';
            return;
        }

        // Decrement user balance
        auth()->user()->decrement('balance', $this->amount);
        $this->max_withdraw = currency_format(auth()->user()->balance, 15);

        //Calculate fees
        $fees = 0;
        $total_amount = $this->amount;
        if(setting('withdrawal_fee_percent') > 0 || setting('withdrawal_fee_fixed') > 0){
            $this->calcFees();
            $fees = number_format($total_amount - $this->final_amount, 8, '.', '');
            $total_amount = $this->final_amount;
        }

        // Create withdrawal
        $withdrawal = auth()->user()->withdrawals()->create([
            'amount' => $total_amount,
            'fees' => $fees,
        ]);

        $withdrawal_response = $this->requestWithdrawal($withdrawal);

        return redirect()->route('my-withdrawals')->with($withdrawal_response);
    }

    public function setMin()
    {
        $this->amount = $this->min_withdraw;
        $this->calcFees();
    }

    public function setMax()
    {
        $this->amount = auth()->user()->balance;
        $this->calcFees();
    }
}
