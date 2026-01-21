<?php

namespace App\Http\Livewire;

use App\Models\UserLog;
use App\Models\UserMiningPower;
use Livewire\Component;

class Reinvest extends Component
{
    public $amount;
    public $balance;
    public $total_hashpower = 0;
    public $error_message;
    public $min_amount;

    public function mount()
    {
        $this->balance = auth()->user()->balance;
        $this->min_amount = number_format(0.01 * setting('hashpower_price'), 8, '.', '');
    }

    public function rules()
    {
        return [
          'amount' => 'required|numeric|min:'.$this->min_amount.'|max:'.$this->balance
        ];
    }

    public function render()
    {
        return view('dashboard::livewire.reinvest');
    }

    public function setMax()
    {
        $this->amount = $this->balance >= $this->min_amount ? $this->balance : 0;
        return $this->calculate();
    }

    public function setMin()
    {
        $this->amount = $this->min_amount;
        return $this->calculate();
    }

    public function calculate()
    {
        unset($this->error_message);
        $this->preValidate($this->amount);
        $this->validate();
    }

    private function preValidate($amount)
    {
        try {
            $format_amount = currency_format($amount, 15);
            $power = currency_format($format_amount / setting('hashpower_price'), 2);
            $this->amount = number_format($power * setting('hashpower_price'), 8, '.', '');
            $this->total_hashpower = $power;
        }catch (\Exception $e) {
            $this->error_message = 'Math error, please try again!';
            logger()->info('Reinvest Error: Amount = '.$amount. ' | Hashpower Price = '. setting('hashpower_price'));
        }
    }

    public function reinvest()
    {
        //Unset error message
        $this->error_message = null;

        //Create session time limiter
        if(!session()->has('rateLimiter')){
            session()->put('rateLimiter', now()->addSeconds(20));
        }else{
            if(session()->get('rateLimiter')->lt(now())){
                session()->put('rateLimiter', now()->addSeconds(20));
            }else{
                $this->error_message = 'Please wait 20 seconds before you can reinvest again.';
                return;
            }
        }

        $this->validate();

        //Avoid negative balance
        if(($this->balance - $this->amount) < 0) {
            $this->error_message = 'You can not reinvest more than your balance.';
            return;
        }

        //Update user balance
        auth()->user()->decrement('balance', $this->amount);
        $this->balance = currency_format(auth()->user()->balance, 15);

        //Calculate hashpower
        $hashes = currency_format($this->amount / setting('hashpower_price'), 2);

        //Create expiration date
        $expire_date = \Carbon\Carbon::now()->addDays(setting('period'))->toDateTimeString();

        //Create user mining
        UserMiningPower::create([
            'user_id' => auth()->user()->id,
            'power' => $hashes,
            'expire_date' => $expire_date
        ]);

        //Create user log
        newUserLog(auth()->user()->id, 'reinvest', [
            'power' => $hashes,
            'amount' => $this->amount,
            'expire_date' => $expire_date,
        ]);

        //Redirect
        return redirect()->route('account')->with('success', 'Reinvestment successfully confirmed!');
    }
}
