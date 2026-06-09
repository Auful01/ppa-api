<?php

namespace App\Http\Controllers;

use App\Models\UserAll;
use App\Models\InvLaptop;
use App\Models\InvComputer;
use App\Models\pengajuanAksesUser;
use Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GuestAllController extends Controller
{
    public function index()
    {
        $user = Auth::user()->nrp;
            $idUser = UserAll::where('nrp', $user)->first();
            $dataInventoryLaptop = InvLaptop::where('user_alls_id', $idUser->id)->get();
            $dataInventoryKomp = InvComputer::where('user_alls_id', $idUser->id)->get();

            $invs = [];

            foreach ($dataInventoryLaptop as $l) {
                $invs[] = [
                        'id' => $l->id,
                        'no_inv' => $l->laptop_code,
                        'asset_ho_number' => $l->number_asset_ho,
                        'jenis' => 'Laptop',
                        'brand' => $l->laptop_name,
                        'spek' => $l->spesifikasi,
                        'sn' => $l->serial_number,
                        'status' => $l->status,
                        'kondisi' => $l->condition,
                ];
            }
            foreach ($dataInventoryKomp as $k) {
                $invs[] = [
                        'id' => $k->id,
                        'no_inv' => $k->computer_code,
                        'asset_ho_number' => $k->number_asset_ho,
                        'jenis' => 'Komputer',
                        'brand' => $k->computer_name,
                        'spek' => $k->spesifikasi,
                        'sn' => $k->serial_number,
                        'status' => $k->status,
                        'kondisi' => $k->condition,
                ];
            }

        $data_user = pengajuanAksesUser::where('nrp_user', $user)->first();
        $data_pengajuan = isset($data_user) ? 'Y' : 'N';
        // dd($data_pengajuan);


            return Inertia::render(
                'Guest/GuestDashboard',
                [
                    'invs' => $invs,
                    'data_pengajuan' => $data_pengajuan
                ]
            );
    }

    public function pengajuanAkses() 
    {
        $existingUser = pengajuanAksesUser::where('nrp_user', Auth::user()->nrp)->first();

        if (!$existingUser) {
            $data = [
                'id_user' => Auth::user()->id,
                'nrp_user' => Auth::user()->nrp,
                'nama_user' => Auth::user()->name,
                'divisi' => Auth::user()->position,
                'dept' => Auth::user()->department,
                'site' => Auth::user()->site,
                'status' => 'BELUM',
            ];
    
            pengajuanAksesUser::create($data);
        }

        // dd(Auth::user()->nrp);
        return redirect()->route('asetDashboard');
    }
}
