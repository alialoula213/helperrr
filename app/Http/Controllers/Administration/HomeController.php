<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $statistics = DB::select("SELECT
            (SELECT count(*) FROM users) as total_users,
            (SELECT count(*) FROM admins) as total_admins,
            (SELECT count(*) FROM pages) as total_pages,
            (SELECT count(*) FROM news) as total_news,
            (SELECT count(*) FROM faqs) as total_faqs,
            (SELECT count(*) FROM withdrawals) as total_withdrawals,
            (SELECT count(*) FROM tickets WHERE admin_read = 0) as unread_tickets,
            (SELECT count(*) FROM deposits WHERE status ='pending') as pending_deposits,
            (SELECT SUM(amount) FROM deposits WHERE status ='paid') as paid_deposits,
            (SELECT count(*) FROM withdrawals WHERE status ='pending') as pending_withdrawals,
            (SELECT SUM(amount) FROM withdrawals WHERE status ='paid') as paid_withdrawals
       ")[0];
        return view('admin.index')->with([
            'page_title' => 'Dashboard',
            'statistics' => $statistics
        ]);
    }

    /**
     * @return mixed
     */
    public function updateCheck()
    {
        $req = \Http::post('https://smartyscripts.com/api/checkupdates', [
            'sku' => 'cyberminer'
        ]);
        if ($req->getStatusCode() !== 200) {
            return redirect()->route('admin.index')->withError('An error occurred while trying to check for a new version. If the error persists, contact smartyscripts.com for help.');
        }
        $latest_version = json_decode($req->getBody());
        if (is_object($latest_version)) {
            return redirect()->route('admin.index')->withError($latest_version->message);
        }
        if (config('smartyscripts.script_version') >= $latest_version) {
            $message = 'You already have the latest version of ' . config('smartyscripts.script_name') . '!';
        } else {
            $message = 'A new update v' . $latest_version . ' is available for ' . config('smartyscripts.script_name') . '. Access your account on our website and download the new version. Go to <a href="https://smartyscripts.com" target="_blank">SmartyScripts</a> site.';
        }
        return redirect()->route('admin.index')->withSuccess($message);
    }
}
