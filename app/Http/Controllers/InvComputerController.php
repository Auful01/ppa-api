<?php

namespace App\Http\Controllers;

use App\Imports\ImportComputer;
use App\Models\Aduan;
use App\Models\Department;
use App\Models\InspeksiComputer;
use App\Models\InvComputer;
use App\Models\UserAll;
use Carbon\Carbon;
use Dedoc\Scramble\Scramble;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use League\Csv\Reader;
use Maatwebsite\Excel\Facades\Excel;
use PhpParser\Node\Expr\Empty_;

class InvComputerController extends Controller
{
    public function index()
    {

        $dataInventory = InvComputer::with('pengguna')->orderBy('computer_code', 'asc')->where('site', null)->orWhere('site', 'HO')->get();

        $site = '';

        $department = Department::orderBy('department_name')->where('code', '!=', null)->pluck('department_name')->map(function ($name) {
            return ['name' => $name];
        })->toArray();

        $role = auth()->user()->role;

        return Inertia::render('Inventory/Komputer/Komputer', ['komputer' => $dataInventory, 'site' => $site, 'role' => $role, 'department' => $department]);
    }

    public function create()
    {
        $department = Department::orderBy('department_name')->where('code', '!=', null)->pluck('department_name')->map(function ($name) {
            return ['name' => $name];
        })->toArray();

        $pengguna = UserAll::where('site', null)->orWhere('site', 'HO')->pluck('username')->map(function ($name) {
            return ['name' => $name];
        })->toArray();

        return Inertia::render('Inventory/Komputer/KomputerCreate', ['pengguna' => $pengguna, 'department' => $department, 'computer_code' => session('computer_code') ?? null, 'dept' => session('dept') ?? null]);
    }

    public function generateCode(Request $request)
    {
        $dataDept = $request->input('department');
        $dept = $dataDept['name'];
        $codeDept = Department::where('department_name', $dept)->first();
        $maxId = InvComputer::where('site', null)->orWhere('site', 'HO')->where('dept', $codeDept->code)->orderBy('max_id', 'desc')->first();

        if (is_null($maxId)) {
            $maxId = 0;
        } else {
            $parts = explode('-', $maxId->computer_code);
            $lastPart = end($parts);
            $maxId = (int) $lastPart;
        }
        $uniqueString = 'HO-PC-' . $codeDept->code . '-' . str_pad(($maxId % 10000) + 1, 3, '0', STR_PAD_LEFT);
        return redirect()->route('komputer.create')->with([
            'computer_code' => $uniqueString, 
            'dept' => $codeDept->code
        ]);
    }

    public function store(Request $request)
    {

        $params = $request->all();

        if ($request->serial_number === '' || $request->serial_number === '-' || $request->serial_number === null) {
            $existingDataSn = null; // Biarkan lanjut tanpa mendeteksi duplikasi
        } else {
            $existingDataSn = InvComputer::where('serial_number', $params['serial_number'])->first();
        }
        
        if ($existingDataSn) {
            $duplicatesInsertSn[] = [
                'number_asset_ho' => $existingDataSn->number_asset_ho,
                'laptop_code' => $existingDataSn->laptop_code,
                'serial_number' => $existingDataSn->serial_number,
                'site' => $existingDataSn->site,
            ];
            // dd($duplicatesInsertSn);
            return Redirect::route('komputer.page')->with([
                'message' => 'Import selesai!',
                'duplicatesInsertSn' => $duplicatesInsertSn, // Kirim daftar duplikat
            ]);
        }

        $maxId = InvComputer::max('max_id');
        if (is_null($maxId) || empty($maxId)) {
            $maxId = 1;
        } else {
            $maxId = $maxId + 1;
        }

        $documentation_image = $request->file('image');
        $destinationPath = 'images/';
        $path_documentation_image = $documentation_image->store('images', 'public');
        $new_path_documentation_image = $path_documentation_image;
        $documentation_image->move($destinationPath, $new_path_documentation_image);

        $aduan_get_data_user = UserAll::where('username', $params['user_alls_id'])->first();

        $dept = $params['dept'];

        $data = [
            'max_id' => $maxId,
            'computer_name' => $params['computer_name'],
            'computer_code' => $params['computer_code'],
            'number_asset_ho' => $params['number_asset_ho'],
            'assets_category' => $params['assets_category'],
            'spesifikasi' => $params['model'] . ', ' . $params['processor'] . ', ' . $params['hdd'] . ', ' . $params['ssd'] . ', ' . $params['ram'] . ', ' . $params['vga'] . ', ' . $params['warna_komputer'] . ', ' . $params['os_komputer'],
            'serial_number' => $params['serial_number'],
            'aplikasi' => $params['aplikasi'],
            'license' => $params['license'],
            'ip_address' => $params['ip_address'],
            'date_of_inventory' => $params['date_of_inventory'],
            'date_of_deploy' => $params['date_of_deploy'],
            'location' => $params['location'],
            'status' => $params['status'],
            'condition' => $params['condition'],
            'note' => $params['note'],
            'link_documentation_asset_image' => url($new_path_documentation_image),
            'user_alls_id' => $aduan_get_data_user['id'],
            'site' => 'HO',
            'dept' => $dept
        ];

        InvComputer::create($data);
        return redirect()->route('komputer.page');
    }

    public function uploadCsv(Request $request)
    {
        try {

            $import = new ImportComputer();
            Excel::import($import, $request->file('file'));

            $duplicates = $import->getDuplicateRecords();
            // dd($duplicates);
            return Redirect::route('komputer.page')->with([
                'message' => 'Import selesai!',
                'duplicates' => $duplicates, // Kirim daftar duplikat
            ]);
        } catch (\Exception $ex) {
            Log::info($ex);
            return response()->json(['data' => 'Some error has occur.', 400]);
        }
    }

    public function edit($id)
    {
        $komputer = InvComputer::find($id);

        if (empty($komputer)) {
            abort(404, 'Data not found');
        }

        if (!empty($komputer->user_alls_id)) {
            $aduan_get_data_user = UserAll::where('id', $komputer->user_alls_id)->first()->username;

            $pengguna_selected = array($aduan_get_data_user);
        } else {
            $pengguna_selected = array('data tidak ada !');
        }

        $pengguna_all = UserAll::where('site', auth()->user()->site)->pluck('username')->map(function ($name) {
            return ['name' => $name];
        })->toArray();

        $spesifikasi = explode(',', $komputer->spesifikasi);
        $model = trim($spesifikasi[0]);
        $processor = trim($spesifikasi[1]);
        $hdd = trim($spesifikasi[2]);
        $ssd = trim($spesifikasi[3]);
        $ram = trim($spesifikasi[4]);
        $vga = trim($spesifikasi[5]);
        $warna_komputer = trim($spesifikasi[6]);
        $os_komputer = trim($spesifikasi[7]);
        // return response()->json(['ap' => $komputer]);
        return Inertia::render('Inventory/Komputer/KomputerEdit', [
            'komputer' => $komputer,
            'model' => $model,
            'processor' => $processor,
            'hdd' => $hdd,
            'ssd' => $ssd,
            'ram' => $ram,
            'vga' => $vga,
            'warna_komputer' => $warna_komputer,
            'os_komputer' => $os_komputer,
            'pengguna_selected' => $pengguna_selected,
            'pengguna_all' => $pengguna_all
        ]);
    }

    public function detail($id)
    {
        $komputer = InvComputer::with('pengguna')->where('id', $id)->first();

        if (empty($komputer)) {
            abort(404, 'Data not found');
        }

        $aduan = Aduan::where('inventory_number', $komputer->computer_code)->get();
        return Inertia::render('Inventory/Komputer/KomputerDetail', [
            'komputer' => $komputer,
            'aduan' => $aduan,
        ]);
    }

    public function update(Request $request)
    {
        if (!empty($request->hasFile('image'))) {
            $documentation_image = $request->file('image');
            $destinationPath = 'images/';
            $path_documentation_image = $documentation_image->store('images', 'public');
            $new_path_documentation_image = $path_documentation_image;
            $documentation_image->move($destinationPath, $new_path_documentation_image);

            $aduan_get_data_user = UserAll::where('username', $request->user_alls_id)->first();

            $data = [
                'max_id' => $request->max_id,
                'computer_name' => $request->computer_name,
                'computer_code' => $request->computer_code,
                'number_asset_ho' => $request->number_asset_ho,
                'assets_category' => $request->assets_category,
                'spesifikasi' => $request->model . ', ' . $request->processor . ', ' . $request->hdd . ', ' . $request->ssd . ', ' . $request->ram . ', ' . $request->vga . ', ' . $request->warna_komputer . ', ' . $request->os_komputer,
                'serial_number' => $request->serial_number,
                'aplikasi' => $request->aplikasi,
                'license' => $request->license,
                'ip_address' => $request->ip_address,
                'date_of_inventory' => $request->date_of_inventory,
                'date_of_deploy' => $request->date_of_deploy,
                'location' => $request->location,
                'status' => $request->status,
                'condition' => $request->condition,
                'note' => $request->note,
                'link_documentation_asset_image' => url($new_path_documentation_image),
                'user_alls_id' => $aduan_get_data_user['id'],
                'site' => auth()->user()->site
            ];
        } else {

            $aduan_get_data_user = UserAll::where('username', $request->user_alls_id)->first();

            $data = [
                'max_id' => $request->max_id,
                'computer_name' => $request->computer_name,
                'computer_code' => $request->computer_code,
                'number_asset_ho' => $request->number_asset_ho,
                'assets_category' => $request->assets_category,
                'spesifikasi' => $request->model . ', ' . $request->processor . ', ' . $request->hdd . ', ' . $request->ssd . ', ' . $request->ram . ', ' . $request->vga . ', ' . $request->warna_komputer . ', ' . $request->os_komputer,
                'serial_number' => $request->serial_number,
                'aplikasi' => $request->aplikasi,
                'license' => $request->license,
                'ip_address' => $request->ip_address,
                'date_of_inventory' => $request->date_of_inventory,
                'date_of_deploy' => $request->date_of_deploy,
                'location' => $request->location,
                'status' => $request->status,
                'condition' => $request->condition,
                'note' => $request->note,
                'user_alls_id' => $aduan_get_data_user['id'],
                'site' => auth()->user()->site
            ];
        }

        InvComputer::firstWhere('id', $request->id)->update($data);
        return redirect()->route('komputer.page');
    }
    public function destroy($id)
    {
        $komputer = InvComputer::findOrFail($id);
        if (empty($komputer)) {
            abort(404, 'Data not found');
        }
        InspeksiComputer::where('inv_computer_id', $id)->delete();
        $komputer->delete();
        return redirect()->back();
    }
}
