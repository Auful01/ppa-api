<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\InvComputer;
use App\Models\InvLaptop;
use App\Models\PengalihanAsset;
use App\Models\User;
use App\Models\UserAll;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PengalihanAssetController extends Controller
{
    public function index($site)
    {
        // return "OKE";
        $sitePharse = $site;
        if ($sitePharse != 'HO') {
            $crew = User::whereIn('role', ['ict_technician', 'ict_group_leader'])->where('site', $sitePharse)->pluck('name')->map(function ($name) {
                return ['name' => $name];
            })->toArray();
        } else {
            $crew = User::whereIn('role', ['ict_ho'])->where('site', 'HO')->pluck('name')->map(function ($name) {
                return ['name' => $name];
            })->toArray();
        }
        return Inertia::render('PengalihanAsset/PengalihanAssetIndex', ['site' => $sitePharse, 'crew' => $crew]);
    }

    public function getDataPengalihanByDeviceRange(Request $request)
    {
        $deviceType = $request->input('device_type');
        $site = (string) $request->input('site');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        $startDate = $startDate ? Carbon::parse($startDate)->startOfDay() : null;
        $endDate = $endDate ? Carbon::parse($endDate)->endOfDay() : null;

        if (!$deviceType || !$site) {
            return response()->json(['message' => 'device_type dan site harus dikirim'], 400);
        }

        // Tentukan model dan kolom kode inventory
        $model = match (strtolower($deviceType)) {
            'laptop' => InvLaptop::class,
            'computer' => InvComputer::class,
            default => null,
        };

        $codeColumn = match (strtolower($deviceType)) {
            'laptop' => 'laptop_code',
            'computer' => 'computer_code',
            default => null,
        };

        if (!$model || !$codeColumn) {
            return response()->json(['message' => 'Device type tidak dikenali'], 400);
        }

        // Ambil data pengalihan
        $query = PengalihanAsset::where('site', $site)->where('device', $deviceType);

        if ($startDate && !$endDate) {
            $query->whereBetween('created_at', [$startDate, now()->toDateString()]);
        } elseif ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } elseif (!$startDate && $endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $pengalihans = $query->get();

        // Ambil ID inv_prev dan inv_next
        $idInvPrev = $pengalihans->pluck('id_inv_prev')->filter()->map(fn ($id) => trim($id))->unique()->values();

        // Ambil data inventory
        $invPrevData = collect();

        if ($idInvPrev->count() > 0) {
            $invPrevData = $model::onlyTrashed()
                ->whereIn('id', $idInvPrev)
                ->get();
        }

        // Ambil nrp user
        $nrpUserPrev = $pengalihans->pluck('nrp_user_prev')->filter()->map(fn ($id) => trim($id))->unique()->values();
        $nrpUserNext = $pengalihans->pluck('nrp_user_new')->filter()->map(fn ($id) => trim($id))->unique()->values();

        // get data dari user all
        $dataUserPrev = UserAll::whereIn('nrp', $nrpUserPrev)->get();
        $dataUserNext = UserAll::whereIn('nrp', $nrpUserNext)->get();

        $result = $pengalihans->map(function ($item) use ($invPrevData, $dataUserPrev, $dataUserNext, $codeColumn) {
            // Ambil inventory berdasarkan id_inv_prev
            $inv = $invPrevData->firstWhere(fn ($inv) => (string)$inv->id === (string)$item->id_inv_prev);

            // Ambil user prev dan next berdasarkan NRP
            $userPrev = $dataUserPrev->firstWhere('nrp', $item->nrp_user_prev);
            $userNext = $dataUserNext->firstWhere('nrp', $item->nrp_user_new);

            return [
                'pengalihan' => $item,
                'inventory_prev' => optional($inv)->{$codeColumn},

                // Data user sebelumnya
                'user_prev_name' => optional($userPrev)->username,
                'user_prev_position' => optional($userPrev)->position,
                'user_prev_dept' => optional($userPrev)->department,

                // Data user pengganti
                'user_next_name' => optional($userNext)->username,
                'user_next_position' => optional($userNext)->position,
                'user_next_dept' => optional($userNext)->department,
            ];
        });

        return response()->json($result);
    }

    public function create($site)
    {
        $site = $site;
        $department = Department::orderBy('department_name')
            ->where('is_site', 'Y')
            ->pluck('department_name', 'code')
            ->map(function ($name, $code) {
                return ['name' => $name, 'code' => $code];
            })
            ->values()
            ->toArray();

        $pengguna = UserAll::where('site', $site)
            ->get(['username', 'nrp']) // ambil dua kolom
            ->map(function ($user) {
                return [
                    'name' => $user->username,
                    'nrp' => $user->nrp,
                ];
            })
            ->values()
            ->toArray();

        // return Inertia::render('PengalihanAsset/PengalihanAssetCreate');
        return Inertia::render('PengalihanAsset/PengalihanAssetCreate', ['pengguna' => $pengguna, 'site' => $site, 'department' => $department, 'device_code' => session('device_code') ?? null, 'dept' => session('dept') ?? null]);
    }

    public function getInventoryByDeviceAndDept(Request $request)
    {
        $request->validate([
            'device_type' => 'required',
            'department' => 'required',
            'site' => 'required',
        ]);

        if ($request->device_type == 'Laptop') {
            $inventory = InvLaptop::withTrashed()->where('site', $request->site)
                ->where('dept', $request->department)
                ->get(['laptop_code as code', 'id']);
        } else {
            $inventory = InvComputer::withTrashed()->where('site', $request->site)
                ->where('dept', $request->department)
                ->get(['computer_code as code', 'id']);
        }

        return response()->json([
            'inventoryData' => $inventory,
        ]);
    }

    public function getInventoryByInvNumber(Request $request)
    {
        $request->validate([
            'device_type' => 'required',
            'idInv' => 'required',
        ]);
        // return response()->json($request);
        if ($request->device_type == 'Laptop') {
            $inventory = InvLaptop::withTrashed()->where('id', $request->idInv)
                ->whereHas('pengguna') // pastikan ada user juga
                ->with('pengguna')
                ->first();
        } else {
            $inventory = InvComputer::withTrashed()->with('pengguna')->where('id', $request->idInv)
                ->first();
        }

        return response()->json([
            'inventoryData' => $inventory,
        ]);
    }

    public function getUserByNrp(Request $request)
    {
        $request->validate([
            'nrp' => 'required',
        ]);

        $user = UserAll::where('nrp', $request->nrp)
            ->first();

        return response()->json([
            'userData' => $user,
        ]);
    }

    public function generateCode(Request $request)
    {
        $dept = $request->dept;
        if ($request->device_type == 'Laptop') {
            $maxId = InvLaptop::where('site', $request->site)->where('dept', $dept)->orderBy('max_id', 'desc')->first();

            if (is_null($maxId)) {
                $maxId = 0;
            } else {
                $parts = explode('-', $maxId->laptop_code);
                $lastPart = end($parts);
                $maxId = (int) $lastPart;
            }

            $uniqueString = $request->site . '-NB-' . $dept . '-' . str_pad(($maxId % 10000) + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $maxId = InvComputer::where('site', $request->site)->where('dept', $dept)->orderBy('max_id', 'desc')->first();

            if (is_null($maxId)) {
                $maxId = 0;
            } else {
                $parts = explode('-', $maxId->computer_code);
                $lastPart = end($parts);
                $maxId = (int) $lastPart;
            }

            $uniqueString = $request->site . '-PC-' . $dept . '-' . str_pad(($maxId % 10000) + 1, 3, '0', STR_PAD_LEFT);
        }

        return response()->json([
            'inventoryData' => $uniqueString,
        ]);
    }

    public function store(Request $request)
    {

        $params = $request->all();
        // dd($params);
        $maxId = InvLaptop::max('max_id');
        if (is_null($maxId)) {
            $maxId = 1;
        } else {
            $maxId = $maxId + 1;
        }

        $documentation_image = $request->file('image');
        $destinationPath = 'images/';
        $path_documentation_image = $documentation_image->store('images', 'public');
        $new_path_documentation_image = $path_documentation_image;
        $documentation_image->move($destinationPath, $new_path_documentation_image);

        $dataPengalihan = [
            'max_id' => $maxId,
            'id_inventory' => $params['id'],
            'device' => $params['deviceType'],
            'id_inv_prev' => $params['idInvPrev'],
            'inv_number_next' => $params['invNumberNext'],
            'nrp_user_prev' => $params['prevNrp'],
            'nrp_user_new' => $params['userNext'],
            'spek' => $params['spek'],
            'dept' => $params['deptNext'],
            'dept_prev' => $params['deptPrev'],
            'remark' => $params['remark'],
            'foto_pengalihan' => url($new_path_documentation_image),
            'site' => $params['site'],
        ];
        PengalihanAsset::create($dataPengalihan);

        if ($params['deviceType'] == 'Laptop') {
            $dataInvLaptop = InvLaptop::where('id', $params['idInvPrev'])->first();
            $dataPengguna = UserAll::where('nrp', $params['userNext'])->first();

            $dept = $params['deptNext'];
            $dataInvNew = [
                'max_id' => $dataInvLaptop->max_id,
                'laptop_name' => $dataInvLaptop->laptop_name,
                'laptop_code' => $params['invNumberNext'],
                'number_asset_ho' => $dataInvLaptop->number_asset_ho,
                'assets_category' => $dataInvLaptop->assets_category,
                'serial_number' => $dataInvLaptop->serial_number,
                'aplikasi' => $dataInvLaptop->aplikasi,
                'spesifikasi' => $dataInvLaptop->spesifikasi,
                'license' => $dataInvLaptop->license,
                'ip_address' => $dataInvLaptop->ip_address,
                'date_of_inventory' => $dataInvLaptop->date_of_inventory,
                'date_of_deploy' => $dataInvLaptop->date_of_deploy,
                'location' => $dataInvLaptop->location,
                'status' => $dataInvLaptop->status,
                'condition' => $dataInvLaptop->condition,
                'note' => $dataInvLaptop->note,
                'link_documentation_asset_image' => url($new_path_documentation_image),
                'user_alls_id' => $dataPengguna->id,
                'site' => $params['site'],
                'dept' => $dept
            ];

            $laptop = InvLaptop::find($params['idInvPrev']);
            if (empty($laptop)) {
                abort(404, 'Data not found');
            }
            $laptop->delete();
            InvLaptop::create($dataInvNew);

            // dd($dataInvNew);
        } else {
            $dataInvComputer = InvComputer::where('id', $params['idInvPrev'])->first();
            $dataPengguna = UserAll::where('nrp', $params['userNext'])->first();

            $dept = $params['deptNext'];
            $dataInvNew = [
                'max_id' => $dataInvComputer->max_id,
                'computer_name' => $dataInvComputer->computer_name,
                'computer_code' => $params['invNumberNext'],
                'number_asset_ho' => $dataInvComputer->number_asset_ho,
                'assets_category' => $dataInvComputer->assets_category,
                'serial_number' => $dataInvComputer->serial_number,
                'aplikasi' => $dataInvComputer->aplikasi,
                'spesifikasi' => $dataInvComputer->spesifikasi,
                'license' => $dataInvComputer->license,
                'ip_address' => $dataInvComputer->ip_address,
                'date_of_inventory' => $dataInvComputer->date_of_inventory,
                'date_of_deploy' => $dataInvComputer->date_of_deploy,
                'location' => $dataInvComputer->location,
                'status' => $dataInvComputer->status,
                'condition' => $dataInvComputer->condition,
                'note' => $dataInvComputer->note,
                'link_documentation_asset_image' => url($new_path_documentation_image),
                'user_alls_id' => $dataPengguna->id,
                'site' => $params['site'],
                'dept' => $dept
            ];

            $laptop = InvComputer::find($params['idInvPrev']);
            if (empty($laptop)) {
                abort(404, 'Data not found');
            }
            $laptop->delete();
            InvComputer::create($dataInvNew);
        }
        // return redirect()->route('laptopMhu.page');
    }

    public function edit($id)
    {
        $dataPengalihan = PengalihanAsset::find($id);

        $userSebelumnya = UserAll::where('nrp', $dataPengalihan->nrp_user_prev)->first();
        $userPengalihan = UserAll::where('nrp', $dataPengalihan->nrp_user_new)->first();
        if ($dataPengalihan->device == 'Laptop') {
            $dataAssetSebelumnya = InvLaptop::where('id', $dataPengalihan['id_inv_prev'])->withTrashed()->first();
            $dataAssetPengalihan = InvLaptop::where('laptop_code', $dataPengalihan['inv_number_next'])->withTrashed()->first();
            $noInvPrev = $dataAssetSebelumnya['laptop_code'] ?? '-';
            $noInvNext = $dataAssetPengalihan['laptop_code'] ?? '-';
        } elseif ($dataPengalihan->device == 'Computer') {
            $dataAssetSebelumnya = InvComputer::where('id', $dataPengalihan['id_inv_prev'])->withTrashed()->first();
            $dataAssetPengalihan = InvComputer::where('computer_code', $dataPengalihan['inv_number_next'])->withTrashed()->first();
            $noInvPrev = $dataAssetSebelumnya['computer_code'] ?? '-';
            $noInvNext = $dataAssetPengalihan['computer_code'] ?? '-';
        }

        $dataSebelumnya = [
            'deviceType' => $dataPengalihan['device'],
            'noInvPrev' => $noInvPrev,
            'deptPrev' => $dataPengalihan['dept_prev'],
            'spek' => $dataAssetSebelumnya['spesifikasi'] ?? '-',
            'prevNrp' => $userSebelumnya['nrp'] ?? '-',
            'prevUname' => $userSebelumnya['username'] ?? '-',
        ];

        $dataPengalihanNext = [
            'deviceType' => $dataPengalihan['device'],
            'deptNext' => $dataPengalihan['dept'],
            'noInvNext' => $noInvNext,
            'prevNrp' => $userSebelumnya['nrp'] ?? '-',
            'prevUname' => $userSebelumnya['username'] ?? '-',
            'remark' => $userSebelumnya['remark'] ?? '-',
        ];

        $dataDepartment = Department::orderBy('department_name')
            ->where('is_site', 'Y')
            ->pluck('department_name', 'code')
            ->map(function ($name, $code) {
                return ['name' => $name, 'code' => $code];
            })
            ->values()
            ->toArray();

        $dataPengguna = UserAll::where('site', $dataPengalihan->site)
            ->get(['username', 'nrp']) // ambil dua kolom
            ->map(function ($user) {
                return [
                    'name' => $user->username,
                    'nrp' => $user->nrp,
                ];
            })
            ->values()
            ->toArray();
        dd([
            'dataPengalihan' => $dataPengalihan,
            'dataAssetSebelumnya' => $dataAssetSebelumnya,
            'dataAssetPengalihan' => $dataAssetPengalihan,
            'dataPengalihanPrev' => $dataSebelumnya,
            'dataPengalihanNext' => $dataPengalihanNext,
            'dataDepartment' => $dataDepartment,
            'dataPengguna' => $dataPengguna,
        ]);

        // return Inertia::render('PengalihanAsset/PengalihanAssetEdit',  [
        //     'site' => $dataPengalihan->site,
        //     'dataPengalihanPrev' => $dataSebelumnya,
        //     'dataPengalihanNext' => $dataPengalihanNext,
        //     'dataPengalihan' => $dataPengalihan,
        //     'dataAssetSebelumnya' => $dataAssetSebelumnya,
        //     'dataAssetPengalihan' => $dataAssetPengalihan,
        //     'department' => $dataDepartment,
        //     'pengguna' => $dataPengguna,
        // ]);
    }
}
