<?php

namespace App\Http\Controllers\Administration\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\TwoFactor;

class TwoFactorController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest:admin')->except('index', 'send', 'resend');
    }

    protected function guard()
    {
        return Auth::guard('admin');
    }

    /*
     * Two Factor form
     */
    public function index()
    {
        if (!auth('admin')->user()) {
            return redirect(route('admin.login'));
        }
        $check2faStatus = auth('admin')->user()->two_factor_method !== 'none';
        $check2faExpire = session()->get('two_factor_expiry');
        $method = auth('admin')->user()->two_factor_method;

        $message = 'Please confirm access to your account by entering the authentication code sent to your email.';
        if($method === 'app'){
            $message = 'Please confirm access to your account by entering the authentication code provided by your authenticator application.';
        }

        if (!$check2faStatus or $check2faExpire) {
            return redirect(route('admin.index'));
        }

        return view('admin.auth.2fa')->with(['page_title' => 'Two Factor Authorization', 'message' => $message]);
    }

    /**
     * Send 2FA code to user email
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function send(Request $request)
    {
        if (auth('admin')->user()->two_factor_method === 'email') {
            $user_token = auth('admin')->user()->two_factor_token;
            $request->validate([
                'token' => 'required|in:' . $user_token,
            ]);
            session()->put('two_factor_expiry', TRUE);
        }

        if (auth('admin')->user()->two_factor_method === 'app') {
            $request->validate([
                'token' => ['required', 'digits:6',
                    function ($attribute, $value, $fail) {
                        if (!app('pragmarx.google2fa')->verifyGoogle2FA(auth('admin')->user()->google2fa_secret, $value)) {
                            $fail('Invalid or expired Token');
                        }
                    }],
            ]);
            app('pragmarx.google2fa')->login();
        }
        return redirect()->intended(route('admin.index'));
    }

    /**
     * Resend 2FA code to user email
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resend()
    {
        $user = auth('admin')->user();
        $user->two_factor_token = \Str::random(16);
        $user->save();

        $user->notify(new TwoFactor($user->two_factor_token));
        request()->session()->flash('success', 'New 2FA Token sent to your email address');
        return redirect()->back();
    }
}
