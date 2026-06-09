<?php

namespace App\Http\Controllers;

use App\Models\Komputer;
use Illuminate\Http\Request;

class KomputerController extends Controller
{
    public function index()
    {
        $computers = Komputer::all();
        return response()->json($computers);
    }

    public function store(Request $request)
    {
        $komputer = Komputer::create($request->all());
        return response()->json($komputer, 201);
    }

    public function show($id)
    {
        $komputer = Komputer::find($id);
        if (is_null($komputer)) {
            return response()->json(['message' => 'komputer not found'], 404);
        }
        return response()->json($komputer);
    }

    public function update(Request $request, $id)
    {
        $komputer = Komputer::find($id);
        if (is_null($komputer)) {
            return response()->json(['message' => 'Post not found'], 404);
        }
        $komputer->update($request->all());
        return response()->json($komputer);
    }

    public function destroy($id)
    {
        $komputer = Komputer::find($id);
        if (is_null($komputer)) {
            return response()->json(['message' => 'Post not found'], 404);
        }
        $komputer->delete();
        return response()->json(null, 204);
    }
}
