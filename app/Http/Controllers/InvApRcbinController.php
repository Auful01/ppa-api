<?php

namespace App\Http\Controllers;

use App\Imports\ImportAp;
use App\Models\InvAp;
use Carbon\Carbon;
use Dedoc\Scramble\Scramble;
use Illuminate\Container\Attributes\DB;
use Illuminate\Http\Request;
use Inertia\Inertia;
use League\Csv\Reader;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class InvApRcbinController extends Controller
{
    public function index()
    {
        
        $dataInventory = InvAp::onlyTrashed()->get();

        $site = '';

        $role = auth()->user()->role;
        
        return Inertia::render('RcBin/AccessPoint/AccessPoint', ['accessPoint' => $dataInventory,'site' => $site,'role' => $role]);
    }

    public function detail($id)
    {
        $dataInventory = InvAp::onlyTrashed()->where('id', $id)->first();
        if (empty($dataInventory)) {
            abort(404, 'Data not found');
        }
        return Inertia::render('RcBin/AccessPoint/AccessPointDetail', [
            'accessPoints' => $dataInventory,
        ]);
    }

    public function restore($id)
    {
        $dataInventory = InvAp::onlyTrashed()->findOrFail($id);
        $dataInventory->restore();
        return redirect()->back();
    }

    public function forceDelete($id)
    {
        $dataInventory = InvAp::onlyTrashed()->findOrFail($id);
        $dataInventory->forceDelete();
        return redirect()->back();
    }
}
