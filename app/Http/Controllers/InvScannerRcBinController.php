<?php

namespace App\Http\Controllers;

use App\Imports\ScannerImport;
use App\Models\Department;
use App\Models\InvScanner;
use Dedoc\Scramble\Scramble;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvScannerRcBinController extends Controller
{
    public function index()
    {
        
        $dataInventory = InvScanner::onlyTrashed()->get();

        $site = '';

        $role = auth()->user()->role;
        return Inertia::render('RcBin/Scanner/Scanner', ['scanner' => $dataInventory,'site' => $site,'role' => $role]);
    }

    public function detail($id)
    {
        $scanner = InvScanner::onlyTrashed()->where('id', $id)->first();

        if (empty($scanner)) {
            abort(404, 'Data not found');
        }
        
        return Inertia::render('RcBin/Scanner/ScannerDetail', [
            'scanners' => $scanner,
        ]);
    }

    public function restore($id)
    {
        $dataInventory = InvScanner::onlyTrashed()->findOrFail($id);
        $dataInventory->restore();
        return redirect()->back();
    }

    public function forceDelete($id)
    {
        $dataInventory = InvScanner::onlyTrashed()->findOrFail($id);
        $dataInventory->forceDelete();
        return redirect()->back();
    }
}
