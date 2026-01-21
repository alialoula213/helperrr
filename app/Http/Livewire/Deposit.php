<?php

namespace App\Http\Livewire;

use App\Traits\PurchaseTrait;
use Livewire\Component;

class Deposit extends Component
{
    use PurchaseTrait;

    public $hashpower_amount;
    public $deposit_error_message;
    public $purchase_total_price = 0;
    public $min_buy;

    public function render()
    {
        $this->min_buy = setting('purchase_min');
        return view('dashboard::livewire.deposit');
    }

    public function rules()
    {
        $min_purchase = setting('purchase_min');
        return [
            'hashpower_amount' => 'required|numeric|gte:'.$min_purchase
        ];
    }

    public function calculate()
    {
        unset($this->deposit_error_message);
        $this->validate();
        $this->purchase_total_price = currency_format($this->hashpower_amount * setting('hashpower_price'), 8);
    }

    public function deposit()
    {
        unset($this->deposit_error_message);
        $this->validate();
        //Create deposit
        $new_deposit = $this->createDeposit($this->hashpower_amount);
        //Redirect
        $payment = $this->createDepositAddress($new_deposit);
        if($payment){
            if(filter_var($payment, FILTER_VALIDATE_URL)){
                return redirect($payment);
            }
            return redirect()->route('payment', $new_deposit->invoice);
        }
        $this->deposit_error_message = 'An unexpected error has occurred. Please try again or contact us if the error persists.';
    }

    public function setMin()
    {
        $this->hashpower_amount = $this->min_buy;
        return $this->calculate();
    }
}
