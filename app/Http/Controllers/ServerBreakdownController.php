<?php

namespace App\Http\Controllers;

use App\Models\PerangkatBreakdown;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ServerBreakdownController extends Controller
{
    public function index()
    {
        $server_breakdown = PerangkatBreakdown::all();
        return response()->json($server_breakdown);
    }

    public function store(Request $request)
    {
        $server_breakdown_get_data = PerangkatBreakdown::find($request->id);
        // return response()->json($server_breakdown_get_data);

        $dataServer = DB::table('inv_servers')->where('server_name', $request->server_name)->first();

        $validatedDataPerangkatBreakdown = [
            'device_category' => 'Server',
            'inventory_number' => $dataServer->id,
            'device_name' => $dataServer->server_name,
            'start_progress' => $request->start_progress,
            'end_progress' => $request->end_progress,
            'location' => $request->location,
            'root_cause_category' => $request->root_cause_category,
            'root_cause' => $request->root_cause,
            'status' => $request->status,
            'pic' => Auth::user()->name,
            // 'created_by' => 'USER LOGIN',
        ];
        $date = Carbon::now();
        $month = $date->format('m');
        $year = $date->format('Y');

        $validatedDataPerangkatBreakdown['month'] = $month;
        $validatedDataPerangkatBreakdown['created_date'] = $date;
        $validatedDataPerangkatBreakdown['year'] = $year;

        if (empty($server_breakdown_get_data)) {
            $server_breakdown = PerangkatBreakdown::create($validatedDataPerangkatBreakdown);
            return response()->json($validatedDataPerangkatBreakdown, 201);
        } else {
            $server_breakdown = PerangkatBreakdown::firstWhere('id', $request->id)->update($validatedDataPerangkatBreakdown);
            return response()->json($server_breakdown, 201);
        }
    }



    public function show($id)
    {
        $server_breakdown = PerangkatBreakdown::find($id);
        if (is_null($server_breakdown)) {
            return response()->json(['message' => 'Switch Device not found'], 404);
        }
        return response()->json($server_breakdown);
    }

    public function destroy($id)
    {
        $server_breakdown = PerangkatBreakdown::find($id);
        if (is_null($server_breakdown)) {
            return response()->json(['message' => 'Switch Device not found'], 404);
        }
        $server_breakdown->delete();
        return response()->json(null, 204);
    }
}
