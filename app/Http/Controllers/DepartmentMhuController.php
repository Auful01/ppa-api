<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DepartmentMhuController extends Controller
{
    public function index()
    {
        $dataDepartment = Department::where('site', 'MHU')->get();
        return Inertia::render('Department/SiteMhu/Department', ['department' => $dataDepartment]);
    }

    public function create()
    {
        return Inertia::render('Department/SiteMhu/DepartmentCreate');
    }

    public function store(Request $request)
    {
        $params = $request->all();
        // return dd($params);
            if (empty($request->id)) {
                if($params['is_site'] == 'N'){
                    $is_site = null;
                }else{
                    $is_site = 'Y';
                }
                $data = [
                    'department_name' => $params['department_name'],
                    'is_site' => $is_site,
                    'code' => $params['singkatan'],
                ];
                // DB::table('inv_aps')->insert($data);
                Department::create($data);
            } else {
                if($params['is_site'] == 'N'){
                    $is_site = null;
                }else{
                    $is_site = 'Y';
                }
                $data = [
                    'department_name' => $params['department_name'],
                    'is_site' => $is_site,
                    'code' => $params['code'],
                ];
                // DB::table('inv_aps')->insert($data);
                Department::firstWhere('id', $request->id)->update($data);
            }
        return redirect()->route('departmentMhu.page');
    }

    public function edit($id)
    {
        $dataDepartment = Department::find($id);
        // return response()->json(['ap' => $dataDepartment]);
        return Inertia::render('Department/SiteMhu/DepartmentEdit', ['department' => $dataDepartment]);
    }

    public function destroy($id)
    {
        $dataDepartment = Department::find($id);
        // return response()->json(['ap' => $dataDepartment]);
        $dataDepartment->delete();
        return redirect()->back();
    }
}
