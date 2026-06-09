<?php

namespace App\Http\Controllers;

use App\Models\InvServer;
use Illuminate\Http\Request;

class InvServerController extends Controller
{
    public function index()
    {
        $invServer = InvServer::all();
        return response()->json($invServer);
    }

    public function store(Request $request)
    {
        $invServer_get_data = InvServer::find($request->id);
        if (empty($invServer_get_data)) {
            $invServer = InvServer::create($request->all());
            return response()->json($invServer, 201);
        } else {
            $invServer = InvServer::firstWhere('id', $request->id)->update($request->all());
            return response()->json($invServer, 201);
        }

    }

    public function show($id)
    {
        $invServer = InvServer::find($id);
        if (is_null($invServer)) {
            return response()->json(['message' => 'Server Data not found'], 404);
        }
        return response()->json($invServer);
    }

    public function destroy($id)
    {
        $invServer = InvServer::find($id);
        if (is_null($invServer)) {
            return response()->json(['message' => 'Server Data not found'], 404);
        }
        $invServer->delete();
        return response()->json(null, 204);
    }
}
