<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class ManagePermissionController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:list-permissions|create-permissions|edit-permissions|delete-permissions', ['only' => ['index','store']]);
        $this->middleware('permission:create-permissions', ['only' => ['create','store']]);
        $this->middleware('permission:show-permissions', ['only' => ['edit']]);
        $this->middleware('permission:edit-permissions', ['only' => ['update']]);
        $this->middleware('permission:delete-permissions', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        return view('admin.permissions.index')->with([
            'page_title' => 'Manage Permissions',
            'items' => Permission::paginate(setting('admin_pagination'))
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('admin.permissions.form')->with([
            'page_title' => 'Create Permission',
            'form_params' => ['button_name' => 'Create', 'action' => route('admin.permissions.store'), 'method' => 'post'],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:permissions,name',
        ]);

        Permission::create($request->all());

        return redirect()->route('admin.permissions.index')->withSuccess('Permission created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Permission $permission
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit(Permission $permission)
    {
        return view('admin.permissions.form')->with([
            'page_title' => 'Edit Permission',
            'form_params' => ['button_name' => 'Update', 'action' => route('admin.permissions.update', $permission->id), 'method' => 'put'],
            'item' => $permission,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param Permission $permission
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Permission $permission)
    {
        $this->validate($request, [
            'name' => 'required|unique:permissions,name,'. $permission->id,
        ]);

        $permission->update($request->all());

        return redirect()->route('admin.permissions.index')->withSuccess('Permission updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     * @param Permission $permission
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy(Permission $permission)
    {
        $permission->delete();

        return redirect()->route('admin.permissions.index')->withSuccess('Permission deleted successfully!');
    }
}
