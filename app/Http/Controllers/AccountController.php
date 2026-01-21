<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\News;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserLog;
use App\Models\Withdrawal;

class AccountController extends Controller
{
    public function index()
    {
        $total_refs = User::whereRefId(auth()->user()->id)->count();

        return view('dashboard::index')->with([
            'total_refs' => $total_refs,
            'user_daily_profit' => user_daily_profit(),
            'user_balance' => getUserBalance(),
            'unread_messages' => Ticket::where('user_id', auth()->user()->id)->where('read', 0)->count(),
        ]);
    }

    public function history()
    {
        $deposits = UserLog::whereUserId(auth()->user()->id)->latest()->paginate(setting('site_pagination'));

        return view('dashboard::history')->with([
            'items' => $deposits
        ]);
    }

    public function deposits()
    {
        $deposits = Deposit::whereUserId(auth()->user()->id)->latest()->paginate(setting('site_pagination'));

        return view('dashboard::deposits')->with([
            'deposits' => $deposits
        ]);
    }

    public function withdrawals()
    {
        $withdrawals = Withdrawal::whereUserId(auth()->user()->id)->latest()->paginate(setting('site_pagination'));

        return view('dashboard::withdrawals')->with([
            'withdrawals' => $withdrawals
        ]);
    }

    public function referralTools()
    {
        $referrals = User::whereRefId(auth()->user()->id)->latest()->paginate(setting('site_pagination'));

        return view('dashboard::referrals')->with([
            'referrals' => $referrals
        ]);
    }

    public function news()
    {
        return view('dashboard::news')->with([
            'news' => News::published()->latest()->paginate(setting('site_pagination'))
        ]);
    }
}
