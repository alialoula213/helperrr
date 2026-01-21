<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminProfileRequest;
use App\Models\Admin;

class AdminProfileController extends Controller
{
    public function index()
    {
        $two_factor_secret = null;
        $two_factor_qr_image = null;
        if(!auth()->user()->google2fa_secret){
            $two_factor_secret = app('pragmarx.google2fa')->generateSecretKey();
            $two_factor_qr_image = app('pragmarx.google2fa')->getQRCodeInline(
                config('app.name'),
                auth()->user()->email,
                $two_factor_secret
            );
        }

        return view('admin.profile')->with([
            'page_title' => 'Profile',
            'two_factor_secret' => $two_factor_secret,
            'two_factor_qr_image' => $two_factor_qr_image,
        ]);
    }

    public function update(AdminProfileRequest $request)
    {
        $user = Admin::find(auth()->user()->id);

        $user->name = $request->name;
        $user->username = $request->username;

        if($user->two_factor_method !== $request->two_factor_method){
            $user->two_factor_method = $request->two_factor_method;
            $user->google2fa_secret = null;
            session()->forget('google2fa');
        }

        if($request->filled('email')){
            $user->email = $request->email;
        }

        if($request->filled('password')){
            $user->password = $request->password;
        }

        if($request->has('two_factor_secret') && $request->two_factor_method==='app'){
            $user->google2fa_secret = $request->two_factor_secret;
        }

        if($request->hasFile('avatar')){
            $avatar_path = 'admin/dist/avatar';
            $avatar = $request->file('avatar')->store($avatar_path,'assets');
            if($user->avatar){
                \Storage::disk('assets')->delete($avatar_path.'/'.$user->avatar);
            }
            $user->avatar = explode($avatar_path.'/', $avatar)[1];
        }
        $user->save();

        return redirect(route('admin.profile'))->withSuccess('Profile updated successfully!');
    }
}
