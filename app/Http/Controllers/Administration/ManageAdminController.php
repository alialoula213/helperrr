<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Http\Requests\AdminRequest;
use Spatie\Permission\Models\Role;

class ManageAdminController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:list-admins|create-admins|edit-admins|delete-admins', ['only' => ['index','store']]);
        $this->middleware('permission:create-admins', ['only' => ['create','store']]);
        $this->middleware('permission:show-admins', ['only' => ['edit']]);
        $this->middleware('permission:edit-admins', ['only' => ['update']]);
        $this->middleware('permission:delete-admins', ['only' => ['destroy']]);
        $this->middleware('permission:impersonate-admins', ['only' => ['impersonate']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        return view('admin.admins.index')->with([
            'page_title' => 'Manage Admins',
            'items' => Admin::with('roles')->paginate(setting('admin_pagination'))
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('admin.admins.form')->with([
            'page_title' => 'Create Admin',
            'roles' => Role::pluck('name','name')->all(),
            'form_params' => ['button_name' => 'Create', 'action' => route('admin.admins.store'), 'method' => 'post'],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\AdminRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(AdminRequest $request)
    {
        $admin = Admin::create($request->all());
        $admin->assignRole($request->input('roles'));

        return redirect()->route('admin.admins.index')->withSuccess('Admin created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Admin $admin
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit(Admin $admin)
    {
        return view('admin.admins.form')->with([
            'page_title' => 'Edit Admin',
            'roles' => Role::pluck('name','name')->all(),
            'userRole' => $admin->roles->pluck('name','name')->all(),
            'form_params' => ['button_name' => 'Update', 'action' => route('admin.admins.update', $admin->id), 'method' => 'put'],
            'item' => $admin,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\AdminRequest $request
     * @param \App\Models\Admin $admin
     * @return \Illuminate\Http\Response
     */
    public function update(AdminRequest $request, Admin $admin)
    {
        $admin->syncRoles($request->input('roles'));

        if($request->filled('password')){
            $admin->update($request->all());
        }else{
            $admin->update($request->except('password'));
        }

        return redirect()->route('admin.admins.index')->withSuccess('Admin updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     * @param Admin $admin
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy(Admin $admin)
    {
        $logged_in = auth('admin')->user()->id;
        $toDelete = $admin->id;

        //Prevent delete own account
        if ($logged_in === $toDelete) {
            request()->session()->flash('error', 'You can not delete your own account!');
            return redirect()->back();
        }

        $admin->delete();

        return redirect()->route('admin.admins.index')->withSuccess('Admin deleted successfully!');
    }

    public function impersonate(Admin $admin)
    {
        if ($admin->hasRole('Super Admin')) {
            return redirect()->back()->withError('You can not impersonate this user.');
        }

        // Overwrite who we're logging in as, if we're already logged in as someone else.
        if (session()->has('impersonate_admin.admin_user_id') && session()->has('impersonate_admin.temp_admin_user_id')) {
            // Let's not try to login as ourselves.
            if (auth('admin')->id() == $admin->id || session()->get('impersonate_admin.admin_user_id') == $admin->id) {
                return redirect()->back()->withError('Do not try to login as yourself.');
            }

            // Overwrite temp user ID.
            session(['impersonate_admin.temp_admin_user_id' => $admin->id]);

            // Login.
            auth('admin')->loginUsingId($admin->id);

            // Redirect.
            return redirect()->route('admin.index');
        }

        // Remove any old session variables
        $this->flushTempSession();

        // Won't break, but don't let them "Login As" themselves
        if (auth('admin')->id() == $admin->id) {
            return redirect()->back()->withError('Do not try to login as yourself.');
        }

        // Add new session variables
        session(['impersonate_admin' => [
            'admin_user_id' => auth('admin')->id(),
            'temp_admin_user_id' => $admin->id,
        ]]);

        // Login user
        auth('admin')->loginUsingId($admin->id);

        // Redirect to frontend
        return redirect()->route('admin.index');
    }

    public function impersonateStop()
    {
        //If for some reason route is getting hit without someone already logged in
        if (!auth('admin')->user()) {
            return redirect()->route('admin.login');
        }

        //If admin id is set, re-login
        if (session()->has('impersonate_admin.admin_user_id') && session()->has('impersonate_admin.temp_admin_user_id')) {
            //Save admin id
            $admin_id = session()->get('impersonate_admin.admin_user_id');

            // Remove any old session variables
            $this->flushTempSession();

            //Re-login admin
            auth('admin')->loginUsingId((int) $admin_id);

            //Redirect to backend user page
            return redirect()->route('admin.index');
        }else{
            // Remove any old session variables
            $this->flushTempSession();

            //Otherwise logout and redirect to login
            auth('admin')->logout();

            return redirect()->route('admin.login');
        }
    }

    private function flushTempSession()
    {
        session()->forget('impersonate_admin');
    }
}
