<?php

namespace App\Http\Controllers;

use App\Imports\ImportAp;
use App\Models\InvAp;
use Carbon\Carbon;
use Dedoc\Scramble\Scramble;
use Illuminate\Container\Attributes\DB;
use Illuminate\Http\Request;
use Inertia\Inertia;
use League\Csv\Reader;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class InvApSksController extends Controller
{
    public function index()
    {
        $dataInventory = InvAp::where('site', 'SKS')->get();
        $site = 'SKS';
        $role = auth()->user()->role;

        return Inertia::render('Inventory/SiteSks/AccessPoint/AccessPoint', ['accessPoint' => $dataInventory, 'site' => $site, 'role' => $role]);
    }

    public function create()
    {
        return Inertia::render('Inventory/SiteSks/AccessPoint/AccessPointCreate', ['inventory_number' => session('inventory_number') ?? null]);
    }

    public function generateCode(Request $request)
    {
        $dataCompany = $request->input('company')['name'];

        // Tentukan prefix berdasarkan perusahaan yang dipilih
        $prefix = $dataCompany === 'PPA' ? 'PPASKSAP' : 'AMMSKSAP';

        // Ambil max_id hanya untuk perusahaan yang dipilih
        $maxId = InvAp::Where(function ($query) use ($dataCompany) {
            $query->where('site', 'SKS')->where('inventory_number', 'like', $dataCompany . '%');
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
        return redirect()->route('accessPointSks.create')->with([
            'inventory_number' => $uniqueString,
        ]);
    }

    public function generateCodeEdit(Request $request)
    {
        $id = $request->input('id');
        $dataCompany = $request->input('company')['name'];

        // Tentukan prefix berdasarkan perusahaan yang dipilih
        $prefix = $dataCompany === 'PPA' ? 'PPASKSAP' : 'AMMSKSAP';

        // Ambil max_id hanya untuk perusahaan yang dipilih
        $maxId = InvAp::Where(function ($query) use ($dataCompany) {
            $query->where('site', 'SKS')->where('inventory_number', 'like', $dataCompany . '%');
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
        return redirect()->route('accessPointSks.edit', ['apId' => $id])
            ->with(['inventory_number' => $uniqueString]);
    }

    public function store(Request $request)
    {
        $isoDate = $request->date_of_inventory;
        $formattedDate = Carbon::parse($isoDate)->setTimezone('Asia/Ujung_Pandang')->toDateString();


        $maxId = InvAp::max('max_id');
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
            'site' => 'SKS'
        ];
        // DB::table('inv_aps')->insert($data);
        InvAp::create($data);
        return redirect()->route('accessPointSks.page');
    }

    public function uploadCsv(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx,csv|max:10240', // 10MB = 10240 KB
        ]);

        try {
            $import = new ImportAp();
            Excel::import($import, $request->file('file'));
            $duplicates = $import->getDuplicateRecords();
            // dd($duplicates);
            return Redirect::route('accessPointSks.page')->with([
                'message' => 'Import selesai!',
                'duplicates' => $duplicates, // Kirim daftar duplikat
            ]);
        } catch (\Exception $ex) {
            Log::info($ex);
            return response()->json(['data' => 'Some error has occur.', 400]);
        }
    }

    public function edit($apId)
    {
        $accessPoint = InvAp::find($apId);
        $accessPoint = InvAp::find($apId);
        if (empty($accessPoint)) {
            abort(404, 'Data not found');
        }

        // Ambil prefix dari inventory_number untuk mendapatkan company
        $inventoryNumber = $accessPoint->inventory_number;
        $company = null;

        if (str_starts_with($inventoryNumber, 'PPASKSAP')) {
            $company = 'PPA';
        } elseif (str_starts_with($inventoryNumber, 'AMMSKSAP')) {
            $company = 'AMM';
        }

        return Inertia::render('Inventory/SiteSks/AccessPoint/AccessPointEdit', [
            'accessPoint' => $accessPoint,
            'selectedCompany' => $company,
            'inventory_number' => session('inventory_number') ?? null,
        ]);
    }

    public function detail($id)
    {
        $accessPoint = InvAp::where('id', $id)->first();
        if (empty($accessPoint)) {
            abort(404, 'Data not found');
        }

        return Inertia::render('Inventory/SiteSks/AccessPoint/AccessPointDetail', [
            'accessPoints' => $accessPoint,
        ]);
    }

    public function show($id)
    {
        $invap = InvAp::find($id);
        if (is_null($invap)) {
            return response()->json(['message' => 'AP Device not found'], 404);
        }
        return response()->json($invap);
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
            'site' => 'SKS'
        ];
        // DB::table('inv_aps')->insert($data);
        InvAp::firstWhere('id', $request->id)->update($data);
        return redirect()->route('accessPointSks.page');
    }

    public function destroy($apId)
    {
        $accessPoint = InvAp::find($apId);
        if (empty($accessPoint)) {
            abort(404, 'Data not found');
        }

        // return response()->json(['ap' => $accessPoint]);
        $accessPoint->delete();
        return redirect()->back();
    }
}
