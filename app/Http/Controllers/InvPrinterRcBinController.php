<?php

namespace App\Http\Controllers;

use App\Imports\PrinterImport;
use App\Models\Department;
use App\Models\InvPrinter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class InvPrinterRcBinController extends Controller
{
    public function index()
    {
        
        $dataInventory = InvPrinter::onlyTrashed()->get();

        $site = '';

        $role = auth()->user()->role;
        return Inertia::render('RcBin/Printer/Printer', ['printer' => $dataInventory,'site' => $site,'role' => $role]);
    }

    public function detail($id)
    {
        $printer = InvPrinter::onlyTrashed()->where('id', $id)->first();
        if (empty($printer)) {
            abort(404, 'Data not found');
        }
        
        return Inertia::render('RcBin/Printer/PrinterDetail', [
            'printers' => $printer,
        ]);
    }

    public function restore($id)
    {
        $dataInventory = InvPrinter::onlyTrashed()->findOrFail($id);
        $dataInventory->restore();
        return redirect()->back();
    }

    public function forceDelete($id)
    {
        $dataInventory = InvPrinter::onlyTrashed()->findOrFail($id);
        $dataInventory->forceDelete();
        return redirect()->back();
    }
}
