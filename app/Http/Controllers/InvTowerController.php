<?php

namespace App\Http\Controllers;

use App\Models\InvTower;
use Illuminate\Http\Request;

class InvTowerController extends Controller
{
    public function index()
    {
        $invTower = InvTower::all();
        return response()->json($invTower);
    }

    public function store(Request $request)
    {
        $invTower_get_data = InvTower::find($request->id);
        if (empty($invTower_get_data)) {
            $invTower = InvTower::create($request->all());
            return response()->json($invTower, 201);
        } else {
            $invTower = InvTower::firstWhere('id', $request->id)->update($request->all());
            return response()->json($invTower, 201);
        }

    }

    public function show($id)
    {
        $invTower = InvTower::find($id);
        if (is_null($invTower)) {
            return response()->json(['message' => 'Tower Data not found'], 404);
        }
        return response()->json($invTower);
    }

    public function destroy($id)
    {
        $invTower = InvTower::find($id);
        if (is_null($invTower)) {
            return response()->json(['message' => 'Tower Data not found'], 404);
        }
        $invTower->delete();
        return response()->json(null, 204);
    }
}
