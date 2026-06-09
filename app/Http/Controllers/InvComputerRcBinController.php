<?php

namespace App\Http\Controllers;

use App\Imports\ImportComputer;
use App\Models\Aduan;
use App\Models\Department;
use App\Models\InvComputer;
use App\Models\UserAll;
use Dedoc\Scramble\Scramble;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class InvComputerRcBinController extends Controller
{
    public function index()
    {
        
        $dataInventory = InvComputer::with('pengguna')->onlyTrashed()->get();

        $site = '';

        $department = Department::orderBy('department_name')->where('code', '!=' , null)->pluck('department_name')->map(function ($name) {
            return ['name' => $name];
        })->toArray();

        $role = auth()->user()->role;

        return Inertia::render('RcBin/Komputer/Komputer', ['komputer' => $dataInventory, 'site' => $site, 'role' => $role, 'department' => $department]);
    }

    public function detail($id)
    {
        $komputer = InvComputer::onlyTrashed()->where('id', $id)->first();

        if (empty($komputer)) {
            abort(404, 'Data not found');
        }

        $aduan = Aduan::where('inventory_number', $komputer->computer_code)->get();
        return Inertia::render('RcBin/Komputer/KomputerDetail', [
            'komputer' => $komputer,
            'aduan' => $aduan,
        ]);
    }
    
    public function restore($id)
    {
        $dataInventory = InvComputer::onlyTrashed()->findOrFail($id);
        $dataInventory->restore();
        return redirect()->back();
    }

    public function forceDelete($id)
    {
        $dataInventory = InvComputer::onlyTrashed()->findOrFail($id);
        $dataInventory->forceDelete();
        return redirect()->back();
    }
}
