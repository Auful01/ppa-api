<?php

namespace App\Http\Controllers;

use App\Imports\ImportCctv;
use App\Models\Aduan;
use App\Models\InvCctv;
use App\Models\InvSwitch;
use Carbon\Carbon;
use Dedoc\Scramble\Scramble;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use League\Csv\Reader;
use Maatwebsite\Excel\Facades\Excel;

class InvCctvBibController extends Controller
{
    public function index()
    {
        $dataInventory = InvCctv::with('switch')->where('site', 'BIB')->get();

        $site = auth()->user()->site;

        $role = auth()->user()->role;

        return Inertia::render('Inventory/SiteBib/Cctv/Cctv', ['cctv' => $dataInventory, 'site' => $site, 'role' => $role]);
    }

    public function create()
    {
        $switch = InvSwitch::select('id', 'inventory_number')->where('site', 'BIB')->get();


        return Inertia::render('Inventory/SiteBib/Cctv/CctvCreate', ['inventory_number' => session('inventory_number') ?? null, 'switch' => $switch]);
    }

    public function generateCode(Request $request)
    {
        $dataCompany = $request->input('company')['name'];

        // Tentukan prefix berdasarkan perusahaan yang dipilih
        $prefix = $dataCompany === 'PPA' ? 'PPABIBCCTV' : 'AMMBIBCCTV';

        // Ambil max_id hanya untuk perusahaan yang dipilih
        $maxId = InvCctv::Where(function ($query) use ($dataCompany) {
            $query->where('site', 'BIB')->where('cctv_code', 'like', $dataCompany . '%');
        })
            ->orderBy('max_id', 'desc')
            ->first();

        if (is_null($maxId)) {
            $maxId = 0;
        } else {
            preg_match('/(\d+)$/', $maxId->cctv_code, $matches);
            $maxId = isset($matches[1]) ? (int) $matches[1] : 0;
        }

        // Buat nomor baru berdasarkan perusahaan
        $uniqueString = $prefix . str_pad(($maxId % 10000) + 1, 3, '0', STR_PAD_LEFT);
        // dd($uniqueString);
        return redirect()->route('cctvBib.create')->with([
            'inventory_number' => $uniqueString,
        ]);
    }

    public function generateCodeEdit(Request $request)
    {
        $id = $request->input('id');
        // dd($id);
        $dataCompany = $request->input('company')['name'];

        // Tentukan prefix berdasarkan perusahaan yang dipilih
        $prefix = $dataCompany === 'PPA' ? 'PPABIBCCTV' : 'AMMBIBCCTV';

        // Ambil max_id hanya untuk perusahaan yang dipilih
        $maxId = InvCctv::Where(function ($query) use ($dataCompany) {
            $query->where('site', 'BIB')->where('cctv_code', 'like', $dataCompany . '%');
        })
            ->orderBy('max_id', 'desc')
            ->first();

        if (is_null($maxId)) {
            $maxId = 0;
        } else {
            preg_match('/(\d+)$/', $maxId->cctv_code, $matches);
            $maxId = isset($matches[1]) ? (int) $matches[1] : 0;
        }

        // Buat nomor baru berdasarkan perusahaan
        $uniqueString = $prefix . str_pad(($maxId % 10000) + 1, 3, '0', STR_PAD_LEFT);
        // dd($uniqueString);
        return redirect()->route('cctvBib.edit', ['id' => $id])
            ->with(['inventory_number' => $uniqueString]);
    }

    public function store(Request $request)
    {
        $maxId = InvCctv::max('max_id');
        if (is_null($maxId)) {
            $maxId = 1;
        } else {
            $maxId = $maxId + 1;
        }

        $params = $request->all();
        $data = [
            'max_id' => $maxId,
            'asset_ho_number' => $params['asset_ho_number'],
            'cctv_code' => $params['cctv_code'],
            'location' => $params['location'],
            'location_detail' => $params['location_detail'],
            'cctv_name' => $params['cctv_name'],
            'cctv_brand' => $params['cctv_brand'],
            'type_cctv' => $params['type_cctv'],
            'mac_address' => $params['mac_address'],
            'ip_address' => $params['ip_address'],
            'nvr_id' => null,
            'switch_id' =>  $params['switch_id'],
            'date_of_inventory' => $params['date_of_inventory'],
            'vlan' => $params['vlan'],
            'uplink' => $params['uplink'],
            'status' => $params['status'],
            'note' => $params['note'],
            'site' => 'BIB'
        ];
        // dd($data);
        InvCctv::create($data);
        return redirect()->route('cctvBib.page');
    }

    public function uploadCsv(Request $request)
    {
        try {
            $import = new ImportCctv();
            Excel::import($import, $request->file('file'));
            $duplicates = $import->getDuplicateRecords();
            // dd($duplicates);
            return Redirect::route('cctvBib.page')->with([
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
        $cctv = InvCctv::find($id);

        $inventoryNumber = $cctv->cctv_code;
        $company = null;

        if (str_starts_with($inventoryNumber, 'PPABIBCCTV')) {
            $company = 'PPA';
        } elseif (str_starts_with($inventoryNumber, 'AMMBIBCCTV')) {
            $company = 'AMM';
        }

        $selectSwitch = $cctv->switch_id;
        $switch = InvSwitch::select('id', 'inventory_number')->where('site', 'BIB')->get();

        return Inertia::render('Inventory/SiteBib/Cctv/CctvEdit', [
            'cctv' => $cctv,
            'switch' => $switch,
            'selectSwitch' => $selectSwitch,
            'selectedCompany' => $company,
            'inventory_number' => session('inventory_number') ?? null,
        ]);
    }

    public function detail($id)
    {
        $cctv = InvCctv::where('cctv_code', $id)->first();
        $aduan = Aduan::where('inventory_number', $id)->get();
        // dd($aduan);

        return Inertia::render('Inventory/SiteBib/Cctv/CctvDetail', [
            'cctv' => $cctv,
            'aduan' => $aduan,
        ]);
    }

    public function update(Request $request)
    {
        $params = $request->all();
        $data = [
            'asset_ho_number' => $params['asset_ho_number'],
            'cctv_code' => $params['cctv_code'],
            'location' => $params['location'],
            'location_detail' => $params['location_detail'],
            'cctv_name' => $params['cctv_name'],
            'cctv_brand' => $params['cctv_brand'],
            'type_cctv' => $params['type_cctv'],
            'mac_address' => $params['mac_address'],
            'ip_address' => $params['ip_address'],
            'nvr_id' => null,
            'switch_id' => $params['switch_id'],
            'date_of_inventory' => $params['date_of_inventory'],
            'vlan' => $params['vlan'],
            'uplink' => $params['uplink'],
            'status' => $params['status'],
            'note' => $params['note'],
            'site' => 'BIB'
        ];

        InvCctv::firstWhere('id', $request->id)->update($data);
        return redirect()->route('cctvBib.page');
    }
    public function destroy($id)
    {
        $cctv = InvCctv::find($id);
        if (empty($cctv)) {
            abort(404, 'Data not found');
        }
        // return response()->json(['ap' => $laptop]);
        $cctv->delete();
        return redirect()->back();
    }
}
