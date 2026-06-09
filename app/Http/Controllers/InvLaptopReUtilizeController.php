<?php

namespace App\Http\Controllers;

use App\Models\Aduan;
use App\Models\InspeksiLaptop;
use App\Models\InvLaptopReUtilize;
use App\Models\UnscheduleJob;
use Carbon\Carbon;
use Dedoc\Scramble\Scramble;
use Illuminate\Http\Request;
use Inertia\Inertia;
use League\Csv\Reader;
use Maatwebsite\Excel\Facades\Excel;

class InvLaptopReUtilizeController extends Controller
{
    public function index()
    {
        $dataInventory = InvLaptopReUtilize::all();
        return Inertia::render('Inventory/LaptopReUtilize/LaptopReUtilize', ['laptopReUtilize' => $dataInventory]);
    }

    public function create()
    {
        // start generate code
        $currentDate = Carbon::tomorrow();
        $year = $currentDate->format('y');
        $month = $currentDate->month;
        $day = $currentDate->day;
        
        $maxId = InvLaptopReUtilize::max('max_id');
        
        if (is_null($maxId)) {
            $maxId = 0;
        }
        
        $uniqueString = 'PPABIBNBX' . str_pad(($maxId % 10000) + 1, 3, '0', STR_PAD_LEFT);
        $request['inventory_number'] = $uniqueString;
        // end generate code

        return Inertia::render('Inventory/LaptopReUtilize/LaptopReUtilizeCreate', ['inventoryNumber' => $uniqueString]);
    }

    public function store(Request $request)
    {
        $maxId = InvLaptopReUtilize::max('max_id');
        if (is_null($maxId)) {
            $maxId = 1;
        } else {
            $maxId = $maxId + 1;
        }

        $params = $request->all();

        $documentation_image = $request->file('image');
        $destinationPath = 'images/';
        $path_documentation_image = $documentation_image->store('images', 'public');
        $new_path_documentation_image = $path_documentation_image;
        $documentation_image->move($destinationPath, $new_path_documentation_image);

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
            'user_alls_id' => $params['user_alls_id'],
        ];

        InvLaptopReUtilize::create($data);
        return redirect()->route('laptopreutilize.page');
    }

    public function uploadCsv(Request $request)
    {
        try {
            Excel::import(new ImportAp, $request->file('file'));
            return redirect()->route('laptopreutilize.page');
        } catch (\Exception $ex) {
            Log::info($ex);
            return response()->json(['data' => 'Some error has occur.', 400]);
        }
    }

    public function edit($id)
    {
        $laptop = InvLaptopReUtilize::find($id);
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
        return Inertia::render('Inventory/LaptopReUtilize/LaptopReUtilizeEdit', [
            'laptop' => $laptop,
            'model' => $model,
            'processor' => $processor,
            'hdd' => $hdd,
            'ssd' => $ssd,
            'ram' => $ram,
            'vga' => $vga,
            'warna_laptop' => $warna_laptop,
            'os_laptop' => $os_laptop,
        ]);
    }

    public function detail($id)
    {
        $laptop = InvLaptopReUtilize::where('laptop_code', $id)->first();
        $aduan = Aduan::where('inventory_number', $id)->get();
        $unschedule = UnscheduleJob::where('inventory_number', $id)->get();
        $inspeksi = InspeksiLaptop::with('inventory')
        ->where('inv_laptop_id', $laptop->id) // Only get posts from user_id 1
        ->get();
        // return response()->json($inspeksi);
        $merge = [
            'aduan' => $aduan,
            'inspeksi' => $inspeksi
        ];
        return Inertia::render('Inventory/LaptopReUtilize/laptopReUtilizeDetail', [
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
                'user_alls_id' => null,
            ];
        } else {
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
                'user_alls_id' => null,
            ];
        }

        InvLaptopReUtilize::firstWhere('id', $request->id)->update($data);
        return redirect()->route('laptopreutilize.page');
    }
    public function destroy($id)
    {
        $laptop = InvLaptopReUtilize::find($id);
        // return response()->json(['ap' => $laptop]);
        $laptop->delete();
        return redirect()->back();
    }
}
