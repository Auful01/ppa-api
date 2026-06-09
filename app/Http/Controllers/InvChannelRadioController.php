<?php

namespace App\Http\Controllers;

use App\Models\InvChannelRadio;
use Illuminate\Http\Request;

class InvChannelRadioController extends Controller
{
    public function index()
    {
        $invChannelRadio = InvChannelRadio::all();
        return response()->json($invChannelRadio);
    }

    public function store(Request $request)
    {
        $invChannelRadio_get_data = InvChannelRadio::find($request->id);
        if (empty($invChannelRadio_get_data)) {
            $invChannelRadio = InvChannelRadio::create($request->all());
            return response()->json($invChannelRadio, 201);
        } else {
            $invChannelRadio = InvChannelRadio::firstWhere('id', $request->id)->update($request->all());
            return response()->json($invChannelRadio, 201);
        }

    }

    public function show($id)
    {
        $invChannelRadio = InvChannelRadio::find($id);
        if (is_null($invChannelRadio)) {
            return response()->json(['message' => 'Channel Radio Data not found'], 404);
        }
        return response()->json($invChannelRadio);
    }

    public function destroy($id)
    {
        $invChannelRadio = InvChannelRadio::find($id);
        if (is_null($invChannelRadio)) {
            return response()->json(['message' => 'Channel Radio Data not found'], 404);
        }
        $invChannelRadio->delete();
        return response()->json(null, 204);
    }
}
