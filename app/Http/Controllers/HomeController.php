<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $statistics = null;
        if(setting('frontend_statistics') === '1'){
            $statistics = DB::select("SELECT
                (SELECT count(*) FROM users) as total_users,
                (SELECT SUM(amount) FROM deposits WHERE status ='paid') as total_deposits,
                (SELECT SUM(amount) FROM withdrawals WHERE status ='paid') as total_withdrawals
           ")[0];
            $statistics->total_users += setting('fake_users', 0);
            $statistics->total_deposits += setting('fake_deposits', 0);
            $statistics->total_withdrawals += setting('fake_withdrawals', 0);
            $statistics->working_days = now()->diffInDays(\Carbon\Carbon::createFromTimeString(setting('start_date')))+setting('fake_days', 0);
        }
        $latest_deposits = null;
        $latest_withdrawals = null;
        if(setting('frontend_latest_transactions') === '1'){
            $latest_deposits = Deposit::with('user')->whereStatus('paid')->limit(setting('frontend_latest_deposits', 10))->latest()->get();
            $latest_withdrawals = Withdrawal::with('user')->whereStatus('paid')->limit(setting('frontend_latest_withdrawals', 10))->latest()->get();
        }

        return view('theme::index')->with(['statistics' => $statistics, 'latest_deposits' => $latest_deposits, 'latest_withdrawals' => $latest_withdrawals]);
    }

    public function logout()
    {
        \Auth::guard('web')->logout();

        return redirect()->route('index');
    }

    public function ref($uuid)
    {
        if($uuid){
            $check_user = User::whereUuid($uuid)->first();
            if($check_user){
                //Increase hits
                $check_user->increment('ref_hits');
                //Set cookie
                setcookie('referral', $uuid, time() + 7776000, '/'); // 90 days
            }
        }

        return redirect()->route('index');
    }
}
