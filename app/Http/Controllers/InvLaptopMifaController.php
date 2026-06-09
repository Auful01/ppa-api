<?php

namespace App\Http\Controllers;

use App\Imports\ImportLaptop;
use App\Models\Aduan;
use App\Models\Department;
use App\Models\InspeksiLaptop;
use App\Models\InvLaptop;
use App\Models\UnscheduleJob;
use App\Models\UserAll;
use Carbon\Carbon;
use Dedoc\Scramble\Scramble;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use League\Csv\Reader;
use Maatwebsite\Excel\Facades\Excel;

class InvLaptopMifaController extends Controller
{
    public function index()
    {
        $dataInventory = InvLaptop::with('pengguna')->orderBy('laptop_code', 'asc')->where('site', 'MIFA')->get();

        $site = 'MIFA';

        $department = Department::orderBy('department_name')->where('is_site', 'Y')->pluck('department_name')->map(function ($name) {
            return ['name' => $name];
        })->toArray();

        $role = auth()->user()->role;

        return Inertia::render('Inventory/SiteMifa/Laptop/Laptop', ['laptop' => $dataInventory, 'site' => $site, 'role' => $role, 'department' => $department]);
    }

    public function create()
    {
        $department = Department::orderBy('department_name')->where('is_site', 'Y')->pluck('department_name')->map(function ($name) {
            return ['name' => $name];
        })->toArray();

        $pengguna = UserAll::where('site', 'MIFA')->pluck('username')->map(function ($name) {
            return ['name' => $name];
        })->toArray();

        return Inertia::render('Inventory/SiteMifa/Laptop/LaptopCreate', ['pengguna' => $pengguna, 'department' => $department, 'laptop_code' => session('laptop_code') ?? null, 'dept' => session('dept') ?? null]);
    }

    public function generateCode(Request $request)
    {
        $dataDept = $request->input('department');
        $dept = $dataDept['name'];
        $codeDept = Department::where('department_name', $dept)->first();
        $maxId = InvLaptop::where('site', 'MIFA')->where('dept', $codeDept->code)->orderBy('max_id', 'desc')->first();
        // dd($maxId);

        if (is_null($maxId)) {
            $maxId = 0;
        } else {
            $parts = explode('-', $maxId->laptop_code);
            $lastPart = end($parts);
            $maxId = (int) $lastPart;
        }

        $uniqueString = 'MIFA-NB-' . $codeDept->code . '-' . str_pad(($maxId % 10000) + 1, 3, '0', STR_PAD_LEFT);
        // dd($codeDept->code);
        return redirect()->route('laptopMifa.create')->with([
            'laptop_code' => $uniqueString,
            'dept' => $codeDept->code
        ]);
    }

    public function store(Request $request)
    {

        $params = $request->all();

        $existingDataSn = InvLaptop::where('serial_number', $params['serial_number'])->first();
        if ($existingDataSn) {
            $duplicatesInsertSn[] = [
                'number_asset_ho' => $existingDataSn->number_asset_ho,
                'laptop_code' => $existingDataSn->laptop_code,
                'serial_number' => $existingDataSn->serial_number,
                'site' => $existingDataSn->site,
            ];
            // dd($duplicatesInsertSn);
            return Redirect::route('laptopMifa.page')->with([
                'message' => 'Import selesai!',
                'duplicatesInsertSn' => $duplicatesInsertSn, // Kirim daftar duplikat
            ]);
        }

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

        $aduan_get_data_user = UserAll::where('username', $params['user_alls_id'])->first();

        $dept = $params['dept'];

        $data = [
            'max_id' => $maxId,
            'laptop_name' => $params['laptop_name'],
            'laptop_code' => $params['laptop_code'],
            'number_asset_ho' => $params['number_asset_ho'],
            'assets_category' => $params['assets_category'],
            'spesifikasi' => $params['model'] . ', ' . $params['processor'] . ', ' . $params['hdd'] . ', ' . $params['ssd'] . ', ' . $params['ram'] . ', ' . $params['vga'] . ', ' . $params['warna_laptop'] . ', ' . $params['os_laptop'],
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
            'site' => 'MIFA',
            'dept' => $dept
        ];

        InvLaptop::create($data);
        return redirect()->route('laptopMifa.page');
    }

    public function uploadCsv(Request $request)
    {
        try {

            $import = new ImportLaptop();
            Excel::import($import, $request->file('file'));

            $duplicates = $import->getDuplicateRecords();
            // dd($duplicates);
            return Redirect::route('laptopMifa.page')->with([
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
        $laptop = InvLaptop::find($id);
        if (empty($laptop)) {
            abort(404, 'Data not found');
        }

        if (!empty($laptop->user_alls_id)) {
            $aduan_get_data_user = UserAll::where('id', $laptop->user_alls_id)->first()->nrp;

            $pengguna_selected = array($aduan_get_data_user);
        } else {
            $pengguna_selected = array('data tidak ada !');
        }

        // $pengguna_all = UserAll::where('site', 'MIFA')->pluck('username')->map(function ($name) {
        //     return ['name' => $name];
        // })->toArray();

        $pengguna_all = UserAll::where('site', 'MIFA')->select('username', 'nrp', 'department')->get()->map(function ($item) {
            return [
                'nrp' => $item->nrp,
                'name' => $item->username,
                'dept' => $item->department,
                'label' => $item->nrp . ' | ' . $item->username . ' | ' . $item->department,
            ];
        });

        // dd($aduan_get_data_user);

        $spesifikasi = explode(',', $laptop->spesifikasi);
        $model = trim($spesifikasi[0]);
        $processor = trim($spesifikasi[1]);
        $hdd = trim($spesifikasi[2]);
        $ssd = trim($spesifikasi[3]);
        $ram = trim($spesifikasi[4]);
        $vga = trim($spesifikasi[5]);
        $warna_laptop = trim($spesifikasi[6]);
        $os_laptop = trim($spesifikasi[7]);
        // return response()->json(['ap' => $laptop]);
        return Inertia::render('Inventory/SiteMifa/Laptop/LaptopEdit', [
            'laptop' => $laptop,
            'model' => $model,
            'processor' => $processor,
            'hdd' => $hdd,
            'ssd' => $ssd,
            'ram' => $ram,
            'vga' => $vga,
            'warna_laptop' => $warna_laptop,
            'os_laptop' => $os_laptop,
            'pengguna_selected' => $pengguna_selected,
            'pengguna_all' => $pengguna_all,
        ]);
    }

    public function detail($id)
    {
        $laptop = InvLaptop::with('pengguna')->where('id', $id)->first();
        if (empty($laptop)) {
            abort(404, 'Data not found');
        }
        $aduan = Aduan::where('inventory_number', $laptop->laptop_code)->get();
        $unschedule = UnscheduleJob::where('inventory_number', $laptop->laptop_code)->get();
        $inspeksi = InspeksiLaptop::with('inventory')
            ->where('inv_laptop_id', $id) // Only get posts from user_id 1
            ->get();
        // return response()->json($inspeksi);
        $merge = [
            'aduan' => $aduan,
            'inspeksi' => $inspeksi
        ];
        return Inertia::render('Inventory/SiteMifa/Laptop/LaptopDetail', [
            'laptop' => $laptop,
            'aduan' => $aduan,
            'inspeksi' => $inspeksi,
            'unschedule' => $unschedule,
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

            $aduan_get_data_user = UserAll::where('site', 'MIFA')->where('nrp', $request->user_alls_id)->first();

            $data = [
                'max_id' => $request->max_id,
                'laptop_name' => $request->laptop_name,
                'laptop_code' => $request->laptop_code,
                'number_asset_ho' => $request->number_asset_ho,
                'assets_category' => $request->assets_category,
                'spesifikasi' => $request->model . ', ' . $request->processor . ', ' . $request->hdd . ', ' . $request->ssd . ', ' . $request->ram . ', ' . $request->vga . ', ' . $request->warna_laptop . ', ' . $request->os_laptop,
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
                'site' => 'MIFA'
            ];
        } else {

            $aduan_get_data_user = UserAll::where('site', 'MIFA')->where('nrp', $request->user_alls_id)->first();

            $data = [
                'max_id' => $request->max_id,
                'laptop_name' => $request->laptop_name,
                'laptop_code' => $request->laptop_code,
                'number_asset_ho' => $request->number_asset_ho,
                'assets_category' => $request->assets_category,
                'spesifikasi' => $request->model . ', ' . $request->processor . ', ' . $request->hdd . ', ' . $request->ssd . ', ' . $request->ram . ', ' . $request->vga . ', ' . $request->warna_laptop . ', ' . $request->os_laptop,
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
                'site' => 'MIFA'
            ];
        }

        InvLaptop::firstWhere('id', $request->id)->update($data);
        return redirect()->route('laptopMifa.page');
    }
    public function destroy($id)
    {
        $laptop = InvLaptop::find($id);
        if (empty($laptop)) {
            abort(404, 'Data not found');
        }
        // return response()->json(['ap' => $laptop]);
        InspeksiLaptop::where('inv_laptop_id', $id)->delete();
        $laptop->delete();
        return redirect()->back();
    }
}
