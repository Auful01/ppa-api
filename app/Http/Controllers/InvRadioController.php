<?php

namespace App\Http\Controllers;

use App\Models\InvRadio;
use Illuminate\Http\Request;

class InvRadioController extends Controller
{
    public function index()
    {
        $invRadio = InvRadio::all();
        return response()->json($invRadio);
    }

    public function store(Request $request)
    {
        $invRadio_get_data = InvRadio::find($request->id);
        if (empty($invRadio_get_data)) {
            $invRadio = InvRadio::create($request->all());
            return response()->json($invRadio, 201);
        } else {
            $invRadio = InvRadio::firstWhere('id', $request->id)->update($request->all());
            return response()->json($invRadio, 201);
        }

    }

    public function show($id)
    {
        $invRadio = InvRadio::find($id);
        if (is_null($invRadio)) {
            return response()->json(['message' => 'Printers Data not found'], 404);
        }
        return response()->json($invRadio);
    }

    public function destroy($id)
    {
        $invRadio = InvRadio::find($id);
        if (is_null($invRadio)) {
            return response()->json(['message' => 'Printers Data not found'], 404);
        }
        $invRadio->delete();
        return response()->json(null, 204);
    }
}
