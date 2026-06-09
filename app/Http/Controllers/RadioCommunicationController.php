<?php

namespace App\Http\Controllers;

use App\Models\RadioCommunication;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RadioCommunicationController extends Controller
{
    public function index()
    {
        $radio_job = RadioCommunication::all();
        return response()->json($radio_job);
    }

    public function store(Request $request)
    {
        $radio_job_get_data = RadioCommunication::find($request->id);
        if (empty($radio_job_get_data)) {
            $data_radio_job = RadioCommunication::create($request->all());
            return response()->json($data_radio_job, 201);
        } else {
            $data_radio_job = RadioCommunication::firstWhere('id', $request->id)->update($request->all());
            return response()->json($data_radio_job, 201);
        }

    }

    public function show($id)
    {
        $radio_job_get_data = RadioCommunication::find($id);
        if (is_null($radio_job_get_data)) {
            return response()->json(['message' => 'Printers Data not found'], 404);
        }
        return response()->json($radio_job_get_data);
    }

    public function destroy($id)
    {
        $radio_job_get_data = RadioCommunication::find($id);
        if (is_null($radio_job_get_data)) {
            return response()->json(['message' => 'Printers Data not found'], 404);
        }
        $radio_job_get_data->delete();
        return response()->json(null, 204);
    }
}
