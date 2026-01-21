<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use App\Http\Requests\SettingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Setting;
use ZipArchive;

class ManageSettingController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:list-settings|show-settings', ['only' => ['index']]);
        $this->middleware('permission:edit-settings', ['only' => ['update']]);
        $this->middleware('permission:update-script|list-settings', ['only' => ['showUpdateForm','updateScript']]);
    }

    public function index()
    {
        return view('admin.settings.index')->with([
            'page_title' => 'Settings'
        ]);
    }

    public function update(SettingRequest $request)
    {
        //Demo
        if (config('smartyscripts.demo_mode') && config('app.env') === 'production'){
            $filled = $request->only('default_editor', 'default_alerts', 'hashpower_unit');
        }else{
            $filled = $request->except('_method', '_token');
            $filled['hashpower_price'] = currency_format($request->hashpower_price, 8);
            $filled['daily_profit'] = currency_format($request->daily_profit, 15);
            $filled['withdrawal_min'] = currency_format($request->withdrawal_min, 8);
            $filled['withdrawal_max_auto'] = currency_format($request->withdrawal_max_auto, 8);
            Setting::forgetAll();
            //Force HTTPS
            if(config('cyber_miner.force_https') != $request->force_https){
                $this->updateHttpsUrls($request->force_https);
            }
        }
        Setting::set($filled);
        Setting::save();

        if ($request->maintenance_status === 'active') {
            $new_down['redirect'] = null;
            $new_down['retry'] = (int)$request->maintenance_retry;
            $new_down['secret'] = $request->maintenance_secret ?? null;
            $new_down['status'] = 503;
            $new_down['template'] = null;

            if (app()->isDownForMaintenance()) {
                $down_file = json_decode(file_get_contents(storage_path('/framework/down')), true);
                if ($down_file['retry'] !== $new_down['retry']) {
                    $down_file['redirect'] = $new_down['redirect'];
                    $down_file['retry'] = $new_down['retry'];
                    $down_file['secret'] = $new_down['secret'];
                    $down_file['template'] = $new_down['template'];
                    file_put_contents(storage_path('/framework/down'), json_encode($down_file, JSON_PRETTY_PRINT));
                }
            } else {
                file_put_contents(storage_path('/framework/down'), json_encode($new_down, JSON_PRETTY_PRINT));
            }
        }

        if ($request->maintenance_status === 'inactive') {
            if (file_exists(storage_path('framework/down'))) {
                unlink(storage_path('framework/down'));
            }
        }

        return redirect()->refresh()->withSuccess('Settings updated successfully!');
    }

    private function updateHttpsUrls($status)
    {
        //Get config file
        $cyberminer = file_get_contents(config_path('cyber_miner.php'));
        $old_config = config('cyber_miner.force_https');
        $new_cyberminer = str_replace("'force_https' => '$old_config',", "'force_https' => '$status',", $cyberminer);
        file_put_contents(config_path('cyber_miner.php'), $new_cyberminer);
    }

    private function defineBasePath()
    {
        return config('smartyscripts.base_path') ? dirname(__DIR__, 5) : base_path() ;
    }

    public function flushCache()
    {
        //Delete cache
        Cache::flush();

        return redirect()->back()->withSuccess('Cache cleared successfully!');
    }

    public function showUpdateForm()
    {
        return view('admin.settings.update')->with([
            'page_title' => 'Update Script'
        ]);
    }

    public function updateScript(Request $request)
    {
        $this->validate($request, [
            'update_patch' => 'required|file|mimes:zip'
        ]);

        //Demo
        if (config('smartyscripts.demo_mode') && config('app.env') === 'production'){
            return redirect()->back()->withError('Disabled in Demo!');
        }

        //Store file
        $update_file = $request->file('update_patch')->store('updates');
        $full_path_file = storage_path('app/'.$update_file);
        //Unzip
        $zip = new ZipArchive();
        $file = $zip->open($full_path_file);
        if($file !== TRUE){
            return redirect()->back()->withError('Unable to extract the file, please try again!');
        }
        //Define base path
        $base_path = $this->defineBasePath();
        //Extract
        $zip->extractTo($base_path);
        //Delete uploaded file
        unlink($full_path_file);
        //Run migrations
        Artisan::call('migrate', ["--force" => true]);
        //Redirect
        return redirect()->back()->withSuccess('Update package successfully applied!');
    }
}
