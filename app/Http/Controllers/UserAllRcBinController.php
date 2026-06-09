<?php

namespace App\Http\Controllers;

use App\Imports\UserPenggunaImport;
use App\Models\Department;
use App\Models\UserAll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class UserAllRcBinController extends Controller
{
    public function index()
    {
        $dataPengguna = UserAll::onlyTrashed()->get();
        return Inertia::render('RcBin/ManagementUser/ManagementUser', ['pengguna' => $dataPengguna]);
    }

    public function restore($id)
    {
        $dataPengguna = UserAll::onlyTrashed()->findOrFail($id);
        $dataPengguna->restore();
        return redirect()->back();
    }

    public function forceDelete($id)
    {
        $dataPengguna = UserAll::onlyTrashed()->findOrFail($id);
        $dataPengguna->forceDelete();
        return redirect()->back();
    }
}
