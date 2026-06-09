<?php

namespace App\Http\Controllers;

use App\Models\InvWirelless;
use Dedoc\Scramble\Scramble;
use Illuminate\Container\Attributes\DB;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvWirellessRcbinController extends Controller
{
    public function index()
    {
        
        $dataInventory = InvWirelless::onlyTrashed()->get();

        $site = '';

        $role = auth()->user()->role;

        return Inertia::render('RcBin/Wirelless/Wirelless', ['wirelless' => $dataInventory,'site' => $site,'role' => $role]);
    }

    public function detail($id)
    {
        $dataInventory = InvWirelless::onlyTrashed()->where('id', $id)->first();
        if (empty($dataInventory)) {
            abort(404, 'Data not found');
        }
        return Inertia::render('RcBin/Wirelless/WirellessDetail', [
            'wirelless' => $dataInventory,
        ]);
    }

    public function restore($id)
    {
        $dataInventory = InvWirelless::onlyTrashed()->findOrFail($id);
        $dataInventory->restore();
        return redirect()->back();
    }

    public function forceDelete($id)
    {
        $dataInventory = InvWirelless::onlyTrashed()->findOrFail($id);
        $dataInventory->forceDelete();
        return redirect()->back();
    }

}
