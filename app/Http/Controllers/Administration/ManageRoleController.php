<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class ManageRoleController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:list-roles|edit-roles|delete-roles|role-roles', ['only' => ['index','store']]);
        $this->middleware('permission:create-roles', ['only' => ['create','store']]);
        $this->middleware('permission:show-roles', ['only' => ['edit']]);
        $this->middleware('permission:edit-roles', ['only' => ['update']]);
        $this->middleware('permission:delete-roles', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        return view('admin.roles.index')->with([
            'page_title' => 'Manage Roles',
            'items' => Role::paginate(setting('admin_pagination'))
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('admin.roles.form')->with([
            'page_title' => 'Create Role',
            'permissions' => Permission::get(),
            'form_params' => ['button_name' => 'Create', 'action' => route('admin.roles.store'), 'method' => 'post'],
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
            'name' => 'required|unique:roles,name',
            'permission' => 'required',
        ]);

        $role = Role::create(['name' => $request->input('name')]);
        $role->syncPermissions($request->input('permission'));

        return redirect()->route('admin.roles.index')->withSuccess('Role created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \Spatie\Permission\Models\Role $role
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit(Role $role)
    {
        return view('admin.roles.form')->with([
            'page_title' => 'Edit Role',
            'permissions' => Permission::get(),
            'rolePermissions' => \DB::table("role_has_permissions")->where("role_has_permissions.role_id", $role->id)
                ->pluck('role_has_permissions.permission_id', 'role_has_permissions.permission_id')
                ->all(),
            'form_params' => ['button_name' => 'Update', 'action' => route('admin.roles.update', $role->id), 'method' => 'put'],
            'item' => $role,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Spatie\Permission\Models\Role $role
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Role $role)
    {
        $this->validate($request, [
            'name' => 'required',
            'permission' => 'required',
        ]);

        $role->update($request->only('name'));
        $role->syncPermissions($request->input('permission'));

        return redirect()->route('admin.roles.index')->withSuccess('Role updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     * @param \Spatie\Permission\Models\Role $role
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy(Role $role)
    {
        $role->delete();

        return redirect()->route('admin.roles.index')->withSuccess('Role deleted successfully!');
    }
}
