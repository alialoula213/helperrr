<?php

namespace App\Http\Livewire;

use Livewire\Component;

class ProfitCalculator extends Component
{
    public $hashpower = 0;
    public $amount = 0;
    public $profits = array();

    protected function mount()
    {
        $this->hashpower = 0;
        $this->amount = 0;
    }

    public function render()
    {
        return view('dashboard::livewire.profit-calculator');
    }

    public function calculate()
    {
        $this->validate([
            'hashpower' => 'required|gte:'.setting('purchase_min')
        ]);

        $profits = array_values(calculator_periods());
        $pricing = setting('hashpower_price');
        $daily_profit = setting('daily_profit');

        if(empty($this->hashpower)){
            $this->hashpower = 0;
        }
        $total_price = $this->hashpower * $pricing;
        $this->amount = currency_format($total_price, 8);

        foreach ($profits as $profit) {
            $this->profits[$profit] = $daily_profit * $this->hashpower * $profit;
        }
    }
}
