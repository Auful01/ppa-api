<?php

namespace App\Http\Controllers;

use App\Models\Aduan;
use App\Models\User;
use App\Models\UserAll;
use Carbon\Carbon;
use Dedoc\Scramble\Scramble;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use League\Csv\Reader;
use Maatwebsite\Excel\Facades\Excel;

class AduanRcbinController extends Controller
{
    public function index()
    {
        $aduan = Aduan::onlyTrashed()->orderBy('date_of_complaint', 'desc')->get();
        // dd($aduan);
        $countOpen = Aduan::where('status', 'OPEN')->count();
        $countClosed = Aduan::where('status', 'CLOSED')->count();
        $countProgress = Aduan::where('status', 'PROGRESS')->count();
        $countCancel = Aduan::where('status', 'CANCEL')->count();
        return Inertia::render(
            'RcBin/Aduan/Aduan',
            [
                'aduan' => $aduan,
                'open' => $countOpen,
                'closed' => $countClosed,
                'progress' => $countProgress,
                'cancel' => $countCancel,
            ]
        );
    }

    public function detail($id)
    {
        $aduan = Aduan::onlyTrashed()->where('id', $id)->first();
        if (empty($aduan)) {
            abort(404, 'Data not found');
        }
        return Inertia::render('RcBin/Aduan/AduanDetail', [
            'aduan' => $aduan,
        ]);
    }

    public function restore($id)
    {
        $aduans = Aduan::onlyTrashed()->findOrFail($id);
        $aduans->restore();
        return redirect()->back();
    }

    public function forceDelete($id)
    {
        $aduans = Aduan::onlyTrashed()->findOrFail($id);
        $aduans->forceDelete();
        return redirect()->back();
    }
}
