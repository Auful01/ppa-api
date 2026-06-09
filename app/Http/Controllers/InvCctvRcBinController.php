<?php

namespace App\Http\Controllers;

use App\Models\Aduan;
use App\Models\InvCctv;
use App\Models\InvSwitch;
use Dedoc\Scramble\Scramble;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class InvCctvRcBinController extends Controller
{
    public function index()
    {
        
        $dataInventory = InvCctv::with('switch')->onlyTrashed()->get();

        $site = '';

        $role = auth()->user()->role;

        return Inertia::render('RcBin/Cctv/Cctv', ['cctv' => $dataInventory,'site' => $site,'role' => $role]);
    }

    public function detail($id)
    {
        $cctv = InvCctv::onlyTrashed()->where('cctv_code', $id)->first();
        $aduan = Aduan::where('inventory_number', $id)->get();
        // dd($aduan);

        return Inertia::render('RcBin/Cctv/CctvDetail', [
            'cctv' => $cctv,
            'aduan' => $aduan,
        ]);
    }

    public function restore($id)
    {
        $dataInventory = InvCctv::onlyTrashed()->findOrFail($id);
        $dataInventory->restore();
        return redirect()->back();
    }

    public function forceDelete($id)
    {
        $dataInventory = InvCctv::onlyTrashed()->findOrFail($id);
        $dataInventory->forceDelete();
        return redirect()->back();
    }
}
