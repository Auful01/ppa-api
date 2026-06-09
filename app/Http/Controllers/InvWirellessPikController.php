<?php

namespace App\Http\Controllers;

use App\Imports\WirellessImport;
use App\Models\InvWirelless;
use Carbon\Carbon;
use Dedoc\Scramble\Scramble;
use Illuminate\Container\Attributes\DB;
use Illuminate\Http\Request;
use Inertia\Inertia;
use League\Csv\Reader;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class InvWirellessPikController extends Controller
{
    public function index()
    {
        $dataInventory = InvWirelless::where('site', 'PIK')->get();
        $site = auth()->user()->site;
        $role = auth()->user()->role;

        return Inertia::render('Inventory/SitePik/Wirelless/Wirelless', ['wirelless' => $dataInventory, 'site' => $site, 'role' => $role]);
    }

    public function create()
    {
        return Inertia::render('Inventory/SitePik/Wirelless/WirellessCreate', ['inventory_number' => session('inventory_number') ?? null]);
    }

    public function generateCode(Request $request)
    {
        $dataCompany = $request->input('company')['name'];

        // Tentukan prefix berdasarkan perusahaan yang dipilih
        $prefix = $dataCompany === 'PPA' ? 'PPAPIKBB' : 'AMMPIKBB';

        // Ambil max_id hanya untuk perusahaan yang dipilih
        $maxId = InvWirelless::Where(function ($query) use ($dataCompany) {
            $query->where('site', 'PIK')->where('inventory_number', 'like', $dataCompany . '%');
        })
            ->orderBy('max_id', 'desc')
            ->first();

        if (is_null($maxId)) {
            $maxId = 0;
        } else {
            preg_match('/(\d+)$/', $maxId->inventory_number, $matches);
            $maxId = isset($matches[1]) ? (int) $matches[1] : 0;
        }

        // Buat nomor baru berdasarkan perusahaan
        $uniqueString = $prefix . str_pad(($maxId % 10000) + 1, 3, '0', STR_PAD_LEFT);
        // dd($uniqueString);
        return redirect()->route('wirellessPik.create')->with([
            'inventory_number' => $uniqueString,
        ]);
    }

    public function generateCodeEdit(Request $request)
    {
        $id = $request->input('id');
        // dd($id);
        $dataCompany = $request->input('company')['name'];

        // Tentukan prefix berdasarkan perusahaan yang dipilih
        $prefix = $dataCompany === 'PPA' ? 'PPAPIKBB' : 'AMMPIKBB';

        // Ambil max_id hanya untuk perusahaan yang dipilih
        $maxId = InvWirelless::Where(function ($query) use ($dataCompany) {
            $query->where('site', 'PIK')->where('inventory_number', 'like', $dataCompany . '%');
        })
            ->orderBy('max_id', 'desc')
            ->first();

        if (is_null($maxId)) {
            $maxId = 0;
        } else {
            preg_match('/(\d+)$/', $maxId->inventory_number, $matches);
            $maxId = isset($matches[1]) ? (int) $matches[1] : 0;
        }

        // Buat nomor baru berdasarkan perusahaan
        $uniqueString = $prefix . str_pad(($maxId % 10000) + 1, 3, '0', STR_PAD_LEFT);
        // dd($uniqueString);
        return redirect()->route('wirellessPik.edit', ['id' => $id])
            ->with(['inventory_number' => $uniqueString]);
    }

    public function store(Request $request)
    {
        $isoDate = $request->date_of_inventory;
        $formattedDate = Carbon::parse($isoDate)->setTimezone('Asia/Ujung_Pandang')->toDateString();


        $maxId = InvWirelless::max('max_id');
        if (is_null($maxId)) {
            $maxId = 1;
        } else {
            $maxId = $maxId + 1;
        }
        $params = $request->all();
        $data = [
            'max_id' => $maxId,
            'device_name' => $params['device_name'],
            'inventory_number' => $params['inventory_number'],
            'asset_ho_number' => $params['asset_ho_number'],
            'serial_number' => $params['serial_number'],
            'frequency' => $params['frequency'],
            'mac_address' => $params['mac_address'],
            'ip_address' => $params['ip_address'],
            'device_brand' => $params['device_brand'],
            'device_type' => $params['device_type'],
            'device_model' => $params['device_model'],
            'location' => $params['location'],
            'status' => $params['status'],
            'date_of_inventory' => $formattedDate,
            'note' => $params['note'],
            'site' => 'PIK'
        ];
        // DB::table('inv_aps')->insert($data);
        InvWirelless::create($data);
        return redirect()->route('wirellessPik.page');
    }

    public function uploadCsv(Request $request)
    {
        try {
            $import = new WirellessImport();
            Excel::import($import, $request->file('file'));
            $duplicates = $import->getDuplicateRecords();
            // dd($duplicates);
            return Redirect::route('wirellessPik.page')->with([
                'message' => 'Import selesai!',
                'duplicates' => $duplicates, // Kirim daftar duplikat
            ]);
        } catch (\Exception $ex) {
            Log::info($ex);
            return response()->json(['data' => 'Some error has occur.', 400]);
        }
    }

    public function detail($id)
    {
        $wirelless = InvWirelless::where('id', $id)->first();

        if (empty($wirelless)) {
            abort(404, 'Data not found');
        }

        return Inertia::render('Inventory/SitePik/Wirelless/WirellessDetail', [
            'wirelless' => $wirelless,
        ]);
    }

    public function edit($id)
    {
        $wirelless = InvWirelless::find($id);
        if (empty($wirelless)) {
            abort(404, 'Data not found');
        }

        // Ambil prefix dari inventory_number untuk mendapatkan company
        $inventoryNumber = $wirelless->inventory_number;
        $company = null;

        if (str_starts_with($inventoryNumber, 'PPAPIKBB')) {
            $company = 'PPA';
        } elseif (str_starts_with($inventoryNumber, 'AMMPIKBB')) {
            $company = 'AMM';
        }

        return Inertia::render('Inventory/SitePik/Wirelless/WirellessEdit', [
            'wirelless' => $wirelless,
            'selectedCompany' => $company,
            'inventory_number' => session('inventory_number') ?? null,
        ]);
    }

    public function update(Request $request)
    {
        $params = $request->all();
        $isoDate = $params['date_of_inventory'];
        $formattedDate = Carbon::parse($isoDate)->setTimezone('Asia/Ujung_Pandang')->toDateString();


        $data = [
            'device_name' => $params['device_name'],
            'inventory_number' => $params['inventory_number'],
            'asset_ho_number' => $params['asset_ho_number'],
            'serial_number' => $params['serial_number'],
            'frequency' => $params['frequency'],
            'mac_address' => $params['mac_address'],
            'ip_address' => $params['ip_address'],
            'device_brand' => $params['device_brand'],
            'device_type' => $params['device_type'],
            'device_model' => $params['device_model'],
            'location' => $params['location'],
            'status' => $params['status'],
            'date_of_inventory' => $formattedDate,
            'note' => $params['note'],
            'site' => 'PIK'
        ];
        InvWirelless::firstWhere('id', $request->id)->update($data);
        return redirect()->route('wirellessPik.page');
    }

    public function destroy($id)
    {
        $wirelless = InvWirelless::find($id);
        if (empty($wirelless)) {
            abort(404, 'Data not found');
        }
        // return response()->json(['ap' => $wirelless]);
        $wirelless->delete();
        return redirect()->back();
    }
}
