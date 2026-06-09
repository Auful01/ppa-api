<?php

namespace App\Http\Controllers;

use App\Models\InvNvr;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InvNvrController extends Controller
{
    public function index()
    {
        $invnvr = InvNvr::all();
        return response()->json($invnvr);
    }

    public function store(Request $request)
    {
        // start generate code
        $currentDate = Carbon::now();
        $year = $currentDate->format('y');
        $month = $currentDate->month;
        $day = $currentDate->day;

        $maxId = InvNvr::max('id');

        if (is_null($maxId)) {
            $maxId = 0;
        }

        $uniqueString = 'PPABIBNVR' . $year . $month . $day . '-' . str_pad(($maxId % 10000) + 1, 2, '0', STR_PAD_LEFT);
        $request['nvr_code'] = $uniqueString;
        // end generate code

        $invnvr_get_data = InvNvr::find($request->id);
        if (empty($invnvr_get_data)) {
            $invnvr = InvNvr::create($request->all());
            return response()->json($invnvr, 201);
        } else {
            $invnvr = InvNvr::firstWhere('id', $request->id)->update($request->all());
            return response()->json($invnvr, 201);
        }
    }

    public function show($id)
    {
        $invnvr = InvNvr::find($id);
        if (is_null($invnvr)) {
            return response()->json(['message' => 'AP Device not found'], 404);
        }
        return response()->json($invnvr);
    }

    public function destroy($id)
    {
        $invnvr = InvNvr::find($id);
        if (is_null($invnvr)) {
            return response()->json(['message' => 'AP Device not found'], 404);
        }
        $invnvr->delete();
        return response()->json(null, 204);
    }
}
