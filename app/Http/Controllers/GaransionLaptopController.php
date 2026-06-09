<?php

namespace App\Http\Controllers;

use App\Models\GaransionLaptop;
use App\Models\InvLaptop;
use App\Models\PerangkatBreakdown;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GaransionLaptopController extends Controller
{
    public function cek()
    {
        $count = PerangkatBreakdown::get()->count();
        return response()->json($count);
    }
    public function index()
    {
        $gransionLaptop = GaransionLaptop::all();
        return response()->json($gransionLaptop);
    }

    public function store(Request $request)
    {
        // start generate code
        $currentDate = Carbon::now();
        $year = $currentDate->format('y');
        $month = $currentDate->month;
        $day = $currentDate->day;

        $maxId = GaransionLaptop::max('id');

        if (is_null($maxId)) {
            $maxId = 0;
        }

        $uniqueString = 'GARANSI' . $year . $month . $day . '-' . str_pad(($maxId % 10000) + 1, 2, '0', STR_PAD_LEFT);
        $request['garansion_code'] = $uniqueString;
        // end generate code

        $data_inv_laptop = DB::table('inv_laptops')->where('laptop_code', $request->inventory_number)->first();

        if (!empty($request->inventory_number)) {
            $data_perangkat_breakdown = DB::table('perangkat_breakdowns')->where('garansion_laptop_code', $request->garansion_laptop_code)->first();
            if (empty($data_perangkat_breakdown)) {
                $validatedDataPerangkatBreakdown = $request->validate([
                    'inventory_number' => 'nullable|string|max:255',
                    'start_progress' => 'nullable|date_format:Y-m-d H:i:s',
                    'end_progress' => 'nullable|date_format:Y-m-d H:i:s',
                    'status' => 'nullable|string|max:255',
                ]);
                $date = Carbon::now();
                $month = $date->format('m');
                $year = $date->format('Y');

                $validatedDataPerangkatBreakdown['device_category'] = 'Laptop';
                $validatedDataPerangkatBreakdown['root_cause'] = 'GARANSI';
                $validatedDataPerangkatBreakdown['garansion_laptop_code'] = $uniqueString;
                $validatedDataPerangkatBreakdown['location'] = $data_inv_laptop->location;
                $validatedDataPerangkatBreakdown['pic'] = Auth::user()->name;
                $validatedDataPerangkatBreakdown['created_date'] = Carbon::now();
                $validatedDataPerangkatBreakdown['month'] = $month;
                $validatedDataPerangkatBreakdown['year'] = $year;
                $dataGaransionAdd = $request->all();
                $dataGaransionAdd['month'] = $month;
                $dataGaransionAdd['year'] = $year;

                // return response()->json($validatedDataPerangkatBreakdown);

                $perangkatBreakdown = PerangkatBreakdown::create($validatedDataPerangkatBreakdown);
                $garansiLaptop = GaransionLaptop::create($dataGaransionAdd);
                return response()->json(['message' => 'Berhasil create data perangkat breakdown garansi laptop'], 201);
            } else {
                $date = Carbon::now();
                $month = $date->format('m');
                $year = $date->format('Y');
                $data_garansi_laptop_prev = DB::table('garansion_laptops')->where('garansion_code', $request->garansion_laptop_code)->first();

                $validatedDataGaransi = $request->validate([
                    'laptop_id' => 'nullable|string|max:255',
                    'inventory_number' => 'nullable|string|max:255',
                    'status' => 'nullable|string|max:255',
                    'start_progress' => 'nullable|date_format:Y-m-d H:i:s',
                    'end_progress' => 'nullable|date_format:Y-m-d H:i:s',
                    'date_of_garansion' => 'nullable|date_format:Y-m-d H:i:s',
                    'hardware_damage' => 'nullable|string|max:255',
                ]);
                $validatedDataGaransi['month'] = $month;
                $validatedDataGaransi['year'] = $year;
                $validatedDataGaransi['record_data'] = $data_garansi_laptop_prev->record_data . ' -> ' . $request->date_of_garansion . ' ' . $request->status . ' -> ';


                $validatedDataPerangkatBreakdown = $request->validate([
                    'inventory_number' => 'nullable|string|max:255',
                    'start_progress' => 'nullable|date_format:Y-m-d H:i:s',
                    'end_progress' => 'nullable|date_format:Y-m-d H:i:s',
                    'status' => 'nullable|string|max:255',
                ]);
                if ($request->status == 'CLOSED') {
                $validatedDataPerangkatBreakdown['created_date'] = Carbon::now();
                }
                // $validatedDataPerangkatBreakdown['garansion_laptop_code'] = $uniqueString;
                $validatedDataPerangkatBreakdown['location'] = $data_inv_laptop->location;
                $validatedDataPerangkatBreakdown['pic'] = Auth::user()->name;
                $validatedDataPerangkatBreakdown['month'] = $month;
                $validatedDataPerangkatBreakdown['year'] = $year;

                $perangkatBreakdown = PerangkatBreakdown::firstWhere('garansion_laptop_code', $request->garansion_laptop_code)->update($validatedDataPerangkatBreakdown);
                $garansiLaptop = GaransionLaptop::firstWhere('garansion_code', $request->garansion_laptop_code)->update($validatedDataGaransi);
                // return response()->json($garansiLaptop);
                return response()->json(['message' => ' data perangkat breakdown dan garansi laptop berhasil di update'], 201);
            }
        }
    }

    public function show($id)
    {
        $gransionLaptop = GaransionLaptop::find($id);
        if (is_null($gransionLaptop)) {
            return response()->json(['message' => 'Wirelless Device not found'], 404);
        }
        return response()->json($gransionLaptop);
    }

    public function destroy($id)
    {
        $gransionLaptop = GaransionLaptop::find($id);
        if (is_null($gransionLaptop)) {
            return response()->json(['message' => 'Wirelless Device not found'], 404);
        }
        $gransionLaptop->delete();
        return response()->json(null, 204);
    }
}
