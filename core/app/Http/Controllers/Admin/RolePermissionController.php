<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    //rolesIndex
    public function rolesIndex()
    {
        $pageTitle = 'Roles';
        $roles = Role::with('permissions')->paginate(10);
        return view('admin.role-permission.index', compact('pageTitle', 'roles'));
    }

    // Role create
    public function createRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:roles,name,except,id'
        ]);
        if (!$validator->fails()) {
            Role::create(['name' => $request->name, 'guard' => 'admin']);
            $notify[] = ['success', 'New role created successfully'];
            return back()->withNotify($notify);
        } else {
            return back()->withErrors($validator)->withInput();
        }
    }

    // Role has permissions
    public function roleHasPermissions($roleId)
    {
        $pageTitle = 'Role has permissions';
        $role = Role::with('permissions')->findOrFail($roleId);
        $permissions = Permission::get();
        return view('admin.role-permission.permissions', compact('pageTitle', 'role', 'permissions'));
    }
    // Role has permissions
    public function roleHasPermissionsUpdate(Request $request, $roleId)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', Rule::unique('roles')->ignore($roleId)],
            'permissions' => 'required|array|min:1'
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        } else {
            DB::beginTransaction();
            try {
                $role = Role::findOrFail($roleId);
                $permissions = Permission::findOrFail($request->permissions);
                $role->syncPermissions($permissions->pluck('name')->toArray());
                $role->name = $request->name;
                $role->save();
                DB::commit();
                $notify[] = ['success', 'Updated successfully'];
                return back()->withNotify($notify);
            } catch (\Exception $e) {
                DB::rollback();
                $errorMessage = $e->getMessage();
                $notify[] = ['error', $errorMessage];
                return back()->withNotify($notify);
            }
        }
    }
}
