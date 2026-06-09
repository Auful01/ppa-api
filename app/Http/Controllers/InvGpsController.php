<?php

namespace App\Http\Controllers;

use App\Models\InvGps;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InvGpsController extends Controller
{
    public function index()
    {
        $invGps = InvGps::all();
        return response()->json($invGps);
    }

    public function store(Request $request)
    {
        // start generate code
        $currentDate = Carbon::now();
        $year = $currentDate->format('y');
        $month = $currentDate->month;
        $day = $currentDate->day;

        $maxId = InvGps::max('id');

        if (is_null($maxId)) {
            $maxId = 0;
        }

        $uniqueString = 'PPABIBGPS' . $year . $month . $day . '-' . str_pad(($maxId % 10000) + 1, 2, '0', STR_PAD_LEFT);
        $request['gps_code'] = $uniqueString;
        // end generate code

        $invGps_get_data = InvGps::find($request->id);
        if (empty($invGps_get_data)) {
            $invGps = InvGps::create($request->all());
            return response()->json($invGps, 201);
        } else {
            $invGps = InvGps::firstWhere('id', $request->id)->update($request->all());
            return response()->json($invGps, 201);
        }
    }

    public function show($id)
    {
        $invGps = InvGps::find($id);
        if (is_null($invGps)) {
            return response()->json(['message' => 'Gps Data not found'], 404);
        }
        return response()->json($invGps);
    }

    public function destroy($id)
    {
        $invGps = InvGps::find($id);
        if (is_null($invGps)) {
            return response()->json(['message' => 'Gps Data not found'], 404);
        }
        $invGps->delete();
        return response()->json(null, 204);
    }
}
