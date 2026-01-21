<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use App\Models\ErrorLog;

class ManageErrorLogController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:list-error-logs|show-error-logs|delete-error-logs', ['only' => ['index']]);
        $this->middleware('permission:show-error-logs', ['only' => ['show']]);
        $this->middleware('permission:delete-error-logs', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        return view('admin.error-logs.index')->with([
            'page_title' => 'Manage Error Logs',
            'items' => ErrorLog::latest()->paginate(setting('admin_pagination'))
        ]);
    }

    /**
     * Show the form for view the specified resource.
     *
     * @param  \App\Models\ErrorLog  $error_log
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function show(ErrorLog $error_log)
    {
        return view('admin.error-logs.show')->with([
            'page_title' => 'View Error Log',
            'item' => $error_log,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ErrorLog  $error_log
     * @return \Illuminate\Http\Response
     */
    public function destroy(ErrorLog $error_log)
    {
        $error_log->delete();

        return redirect()->route('admin.error_logs.index')->withSuccess('Error log deleted successfully!');
    }
}
