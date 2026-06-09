<?php

namespace App\Http\Controllers;

use App\Models\DispatchVhms;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DispatchVhmsController extends Controller
{
    public function index()
    {
        $vhms = DispatchVhms::all();
        return response()->json($vhms);
    }

    public function store(Request $request)
    {
        $vhms_get_data = DispatchVhms::find($request->id);
        if (empty($vhms_get_data)) {
            $vhms = DispatchVhms::create($request->all());
            return response()->json($vhms, 201);
        } else {
            $vhms = DispatchVhms::firstWhere('id', $request->id)->update($request->all());
            return response()->json($vhms, 201);
        }
    }

    public function show($id)
    {
        $vhms = DispatchVhms::find($id);
        if (is_null($vhms)) {
            return response()->json(['message' => 'Cctv Device not found'], 404);
        }
        return response()->json($vhms);
    }

    public function destroy($id)
    {
        $vhms = DispatchVhms::find($id);
        if (is_null($vhms)) {
            return response()->json(['message' => 'Cctv Device not found'], 404);
        }
        $vhms->delete();
        return response()->json(null, 204);
    }


    public function doRepairVhms(Request $request)
    {
        $checkingByString = implode(', ', $request->checking_by);
        $dataDoRepairVhms = [
            'unit_vhms_id' => $request->id,
            'status_vhms' => $request->status_vhms,
            'root_cause' => $request->root_cause,
            'other' => $request->other,
            'action' => $request->action,
            'update_by' => Auth::user()->name,
            'repair_note' => $request->repair_note,
            'checking_by' => $checkingByString,
            'date' => Carbon::now(),
        ];

        $dataUpdateVhms = [
            'status' => $request->status_vhms,
            'remark' => $request->repair_note,
        ];

        $dataInsertRepairVhms = DB::table('dispatch_repair_vhms')->insert($dataDoRepairVhms);
        $vhmsUpdateRepair = DispatchVhms::firstWhere('id', $request->id)->update($dataUpdateVhms);

        if ($dataInsertRepairVhms == true && $vhmsUpdateRepair == true) {
            return response()->json(['message' => 'data repair berhasil di insert dan update status pada list master data vhms']);
        } elseif ($dataInsertRepairVhms == true && $vhmsUpdateRepair == false) {
            return response()->json(['message' => 'data repair berhasil di insert, gagal update status pada list master data vhms']);
        } elseif ($dataInsertRepairVhms == false && $vhmsUpdateRepair == true) {
            return response()->json(['message' => 'data repair gagal di insert, berhasil update status pada list master data vhms']);
        } elseif ($dataInsertRepairVhms == false && $vhmsUpdateRepair == false) {
            return response()->json(['message' => 'data repair gagal di insert dan gagal update status pada list master data vhms']);
        }
    }

    public function showDataRepiarHistory($id_unit)
    {
        $dataHistoryByCodeUnit = DB::table('dispatch_repair_vhms')->where('unit_vhms_id', $id_unit)->get();
        return response()->json($dataHistoryByCodeUnit);
    }

    public function updateBreakdownVhms(Request $request)
    {
        $idUnit = $request->unit_code;

        $dataUpdateBreakdown = [
            'status' => 'PROBLEM',
            'remark' => 'Perlu Pengecekan',
        ];
        foreach ($idUnit as $index => $id) {
            // update berdasarkan unit code
            $vhmsUpdateBreakdown = DispatchVhms::firstWhere('unit_code', $id)->update($dataUpdateBreakdown);
        }
        return response()->json($vhmsUpdateBreakdown, 201);
    }

    public function showDataVhmsProblem(Request $request)
    {
        $vhmsProblem = DispatchVhms::where('status', '!=', 'TERDOWNLOAD')->get();
        return response()->json($vhmsProblem, 201);
    }
}
