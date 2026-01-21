<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\UserMiningPower;
use Livewire\Component;

class Login extends Component
{
    public $wallet;
    public $response;
    public $referral;

    protected function mount()
    {
        $this->wallet = '';
        $this->response = '';
    }

    public function render()
    {
        if(isset($_COOKIE['referral'])){
            $this->referral = $_COOKIE['referral'];
        }
        return view('theme::login');
    }

    public function start()
    {
        $this->validate([
            'wallet' => 'required|alpha_num|min:10'
        ]);

        //Check if user exists
        $user = User::where('wallet', $this->wallet)->first();

        //User exists
        if($user){
            //Check account status
            if($user->status === 'inactive'){
                return $this->response = 'Your account has been disabled for inactivity!';
            }
            if($user->status === 'banned'){
                return $this->response = 'Your account has been banned! Reason: '.$user->banned_message;
            }
            //Try login user
            if(\Auth::loginUsingId($user->id)){
                return redirect()->intended()->route('account');
            }
            return $this->response = 'Incorrect wallet or password!';
        }

        //Register user
        return $this->registerUser();
    }

    /**
     * Register user
     * @return \Illuminate\Http\RedirectResponse|string
     * @throws \Exception
     */
    private function registerUser()
    {
        //Validate wallet
        $this->validate([
            'wallet' => 'required|alpha_num|min:10|unique:users,wallet'
        ]);

        //Avoid multiple accounts with the same ip per coin/wallet
        if(setting('multiple_accounts', 'no') === 'no') {
            $user_ip_exists = User::where('ip', request()->ip())->first();
            if ($user_ip_exists) {
                return $this->response = 'You cannot have more than one account!';
            }
        }

        //Check referral
        $ref_id = null;
        if($this->referral){
            $upline = User::where('uuid', $this->referral)->first();
            if($upline){
                $ref_id = $upline->id;
            }
        }

        //Register
        $register = User::create([
            'uuid' => random_int(100000, 999999999),
            'wallet' => $this->wallet,
            'password' => \Hash::make(\Str::random()),
            'ref_id' => $ref_id,
            'ip' => request()->ip(),
        ]);

        //Login new user
        \Auth::login($register);

        //Signup Bonus
        if(setting('signup_bonus') > 0)
        {
            //Create expiration date
            $expire_date = \Carbon\Carbon::now()->addDays(setting('period'))->toDateTimeString();
            //Create user mining
            UserMiningPower::create([
                'user_id' => auth()->user()->id,
                'power' => setting('signup_bonus'),
                'expire_date' => $expire_date
            ]);
            //Create user log
            $description = 'You received a sign-up bonus of '.setting('signup_bonus').' '.setting('hashpower_unit').'\s.';
            newUserLog(auth()->user()->id, 'others', $description);
        }
        return redirect()->route('account');
    }
}
