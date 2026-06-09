<?php

namespace App\Http\Controllers;

use App\Imports\SwitchImport;
use App\Models\InvSwitch;
use Carbon\Carbon;
use Dedoc\Scramble\Scramble;
use Illuminate\Container\Attributes\DB;
use Illuminate\Http\Request;
use Inertia\Inertia;
use League\Csv\Reader;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class InvSwitchRcbinController extends Controller
{
    public function index()
    {
        
        $dataInventory = InvSwitch::onlyTrashed()->get();

        $site = '';

        $role = auth()->user()->role;

        return Inertia::render('RcBin/Switch/Switch', ['switch' => $dataInventory,'site' => $site,'role' => $role]);
    }

    public function detail($id)
    {
        $dataInventory = InvSwitch::onlyTrashed()->where('id', $id)->first();
        if (empty($dataInventory)) {
            abort(404, 'Data not found');
        }
        return Inertia::render('RcBin/Switch/SwitchDetail', [
            'switch' => $dataInventory,
        ]);
    }

    public function restore($id)
    {
        $dataInventory = InvSwitch::onlyTrashed()->findOrFail($id);
        $dataInventory->restore();
        return redirect()->back();
    }

    public function forceDelete($id)
    {
        $dataInventory = InvSwitch::onlyTrashed()->findOrFail($id);
        $dataInventory->forceDelete();
        return redirect()->back();
    }
}
