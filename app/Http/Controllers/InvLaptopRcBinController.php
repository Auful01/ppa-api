<?php

namespace App\Http\Controllers;

use App\Imports\ImportLaptop;
use App\Models\Aduan;
use App\Models\Department;
use App\Models\InspeksiLaptop;
use App\Models\InvLaptop;
use App\Models\UnscheduleJob;
use App\Models\UserAll;
use Dedoc\Scramble\Scramble;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvLaptopRcBinController extends Controller
{
    public function index()
    {
        
        $dataInventory = InvLaptop::with('pengguna')->onlyTrashed()->get();

        $site = '';

        $department = Department::orderBy('department_name')->where('code', '!=' , null)->pluck('department_name')->map(function ($name) {
            return ['name' => $name];
        })->toArray();

        $role = auth()->user()->role;
        // dd($dataInventory);

        return Inertia::render('RcBin/Laptop/Laptop', ['laptop' => $dataInventory, 'site' => $site, 'role' => $role, 'department' => $department]);
    }

    public function detail($id)
    {
        $laptop = InvLaptop::onlyTrashed()->where('id', $id)->first();
        if (empty($laptop)) {
            abort(404, 'Data not found');
        }
        $aduan = Aduan::where('inventory_number', $laptop->laptop_code)->get();
        $unschedule = UnscheduleJob::where('inventory_number', $laptop->laptop_code)->get();
        $inspeksi = InspeksiLaptop::with('inventory')
            ->where('inv_laptop_id', $id) 
            ->get();
        
        $merge = [
            'aduan' => $aduan,
            'inspeksi' => $inspeksi
        ];
        return Inertia::render('RcBin/Laptop/LaptopDetail', [
            'laptop' => $laptop,
            'aduan' => $aduan,
            'inspeksi' => $inspeksi,
            'unschedule' => $unschedule,
        ]);
    }

    public function restore($id)
    {
        $dataInventory = InvLaptop::onlyTrashed()->findOrFail($id);
        $dataInventory->restore();
        return redirect()->back();
    }

    public function forceDelete($id)
    {
        $dataInventory = InvLaptop::onlyTrashed()->findOrFail($id);
        $dataInventory->forceDelete();
        return redirect()->back();
    }
}