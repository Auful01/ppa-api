<?php

namespace App\Http\Controllers;

use App\Imports\ScannerImport;
use App\Models\Department;
use App\Models\InvScanner;
use Carbon\Carbon;
use Dedoc\Scramble\Scramble;
use Illuminate\Container\Attributes\DB;
use Illuminate\Http\Request;
use Inertia\Inertia;
use League\Csv\Reader;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class InvScannerController extends Controller
{
    public function index()
    {

        $dataInventory = InvScanner::where('site', null)->orWhere('site', 'HO')->get();

        $site = '';

        $role = auth()->user()->role;
        return Inertia::render('Inventory/Scanner/Scanner', ['scanner' => $dataInventory, 'site' => $site, 'role' => $role]);
    }

    public function create()
    {
        $department = Department::pluck('department_name')->map(function ($name) {
            return ['name' => $name];
        })->toArray();

        // end generate code

        return Inertia::render('Inventory/Scanner/ScannerCreate', ['inventory_number' => session('inventory_number') ?? null, 'department' => $department]);
    }

    public function generateCode(Request $request)
    {
        $dataCompany = $request->input('company')['name'];

        // Tentukan prefix berdasarkan perusahaan yang dipilih
        $prefix = $dataCompany === 'PPA' ? 'PPAHOSCN' : 'AMMHOSCN';

        // Ambil max_id hanya untuk perusahaan yang dipilih
        $maxId = InvScanner::Where(function ($query) use ($dataCompany) {
            $query->where('site', 'HO')->where('scanner_code', 'like', $dataCompany . '%');
        })
            ->orderBy('max_id', 'desc')
            ->first();

        if (is_null($maxId)) {
            $maxId = 0;
        } else {
            preg_match('/(\d+)$/', $maxId->scanner_code, $matches);
            $maxId = isset($matches[1]) ? (int) $matches[1] : 0;
        }

        // Buat nomor baru berdasarkan perusahaan
        $uniqueString = $prefix . str_pad(($maxId % 10000) + 1, 3, '0', STR_PAD_LEFT);
        // dd($uniqueString);
        return redirect()->route('scanner.create')->with([
            'inventory_number' => $uniqueString,
        ]);
    }

    public function generateCodeEdit(Request $request)
    {
        $id = $request->input('id');
        // dd($id);
        $dataCompany = $request->input('company')['name'];

        // Tentukan prefix berdasarkan perusahaan yang dipilih
        $prefix = $dataCompany === 'PPA' ? 'PPAHOSCN' : 'AMMHOSCN';

        // Ambil max_id hanya untuk perusahaan yang dipilih
        $maxId = InvScanner::Where(function ($query) use ($dataCompany) {
            $query->where('site', 'HO')->where('scanner_code', 'like', $dataCompany . '%');
        })
            ->orderBy('max_id', 'desc')
            ->first();

        if (is_null($maxId)) {
            $maxId = 0;
        } else {
            preg_match('/(\d+)$/', $maxId->scanner_code, $matches);
            $maxId = isset($matches[1]) ? (int) $matches[1] : 0;
        }

        // Buat nomor baru berdasarkan perusahaan
        $uniqueString = $prefix . str_pad(($maxId % 10000) + 1, 3, '0', STR_PAD_LEFT);
        // dd($uniqueString);
        return redirect()->route('scanner.edit', ['id' => $id])
            ->with(['inventory_number' => $uniqueString]);
    }

    public function store(Request $request)
    {
        $isoDate = $request->date_of_inventory;
        $formattedDate = Carbon::parse($isoDate)->setTimezone('Asia/Ujung_Pandang')->toDateString();

        $maxId = InvScanner::max('max_id');
        if (is_null($maxId)) {
            $maxId = 1;
        } else {
            $maxId = $maxId + 1;
        }
        $params = $request->all();
        $data = [
            'max_id' => $maxId,
            'item_name' => $params['item_name'],
            'scanner_code' => $params['scanner_code'],
            'asset_ho_number' => $params['asset_ho_number'],
            'serial_number' => $params['serial_number'],
            'mac_address' => $params['mac_address'],
            'ip_address' => $params['ip_address'],
            'scanner_brand' => $params['device_brand'],
            'scanner_type' => $params['device_type'],
            'division' => $params['divisi'],
            'department' => $params['dept'],
            'location' => $params['location'],
            'status' => $params['status'],
            'note' => $params['note'],
            'date_of_inventory' => $formattedDate,
            'site' => auth()->user()->site
        ];
        // DB::table('inv_aps')->insert($data);
        InvScanner::create($data);
        return redirect()->route('scanner.page');
    }

    public function uploadCsv(Request $request)
    {
        try {

            Excel::import(new ScannerImport, $request->file('file'));
            return redirect()->route('scanner.page');
        } catch (\Exception $ex) {
            Log::info($ex);
            return response()->json(['data' => 'Some error has occur.', 400]);
        }
    }

    public function detail($id)
    {
        $scanner = InvScanner::where('id', $id)->first();

        if (empty($scanner)) {
            abort(404, 'Data not found');
        }

        return Inertia::render('Inventory/Scanner/ScannerDetail', [
            'scanners' => $scanner,
        ]);
    }

    public function edit($scannerId)
    {
        $scanner = InvScanner::find($scannerId);

        $inventoryNumber = $scanner->scanner_code;
        $company = null;

        if (str_starts_with($inventoryNumber, 'PPAHOSCN')) {
            $company = 'PPA';
        } elseif (str_starts_with($inventoryNumber, 'AMMHOSCN')) {
            $company = 'AMM';
        }

        if (!empty($scanner->department)) {
            $department_select = array($scanner->department);
        } else {
            $department_select = array('data tidak ada !');
        }

        $department = Department::pluck('department_name')->map(function ($name) {
            return ['name' => $name];
        })->toArray();

        // return response()->json(['ap' => $accessPoint]);
        return Inertia::render('Inventory/Scanner/ScannerEdit', [
            'scanner' => $scanner, 'department' => $department, 'department_select' => $department_select,
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
            'item_name' => $params['item_name'],
            'scanner_code' => $params['scanner_code'],
            'asset_ho_number' => $params['asset_ho_number'],
            'serial_number' => $params['serial_number'],
            'mac_address' => $params['mac_address'],
            'ip_address' => $params['ip_address'],
            'scanner_brand' => $params['scanner_brand'],
            'scanner_type' => $params['scanner_type'],
            'division' => $params['divisi'],
            'department' => $params['dept'],
            'location' => $params['location'],
            'status' => $params['status'],
            'note' => $params['note'],
            'date_of_inventory' => $formattedDate,
            'site' => auth()->user()->site
        ];
        // DB::table('inv_aps')->insert($data);
        InvScanner::firstWhere('id', $request->id)->update($data);
        return redirect()->route('scanner.page');
    }


    public function destroy($id)
    {
        $scanner = InvScanner::findOrFail($id);
        if (empty($scanner)) {
            abort(404, 'Data not found');
        }
        // return response()->json(['ap' => $scanner]);
        $scanner->delete();
        return redirect()->back();
    }
}
