<?php

namespace App\Http\Controllers;

use App\Models\InvPanelBox;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InvPanelBoxController extends Controller
{
    public function index()
    {
        $invPanelBox = InvPanelBox::all();
        return response()->json($invPanelBox);
    }

    public function store(Request $request)
    {
         // start generate code
         $currentDate = Carbon::now();
         $year = $currentDate->format('y');
         $month = $currentDate->month;
         $day = $currentDate->day;
 
         $maxId = InvPanelBox::max('id');
 
         if (is_null($maxId)) {
             $maxId = 0;
         }
 
         $uniqueString = 'PBN' . $year . $month . $day . '-' . str_pad(($maxId % 10000) + 1, 2, '0', STR_PAD_LEFT);
         $request['inventory_number'] = $uniqueString;
         // end generate code

        $invPanelBox_get_data = InvPanelBox::find($request->id);
        if (empty($invPanelBox_get_data)) {
            $invPanelBox = InvPanelBox::create($request->all());
            return response()->json($invPanelBox, 201);
        } else {
            $invPanelBox = InvPanelBox::firstWhere('id', $request->id)->update($request->all());
            return response()->json($invPanelBox, 201);
        }
    }

    public function show($id)
    {
        $invPanelBox = InvPanelBox::find($id);
        if (is_null($invPanelBox)) {
            return response()->json(['message' => 'Panelbox Data not found'], 404);
        }
        return response()->json($invPanelBox);
    }

    public function destroy($id)
    {
        $invPanelBox = InvPanelBox::find($id);
        if (is_null($invPanelBox)) {
            return response()->json(['message' => 'Panelbox Data not found'], 404);
        }
        $invPanelBox->delete();
        return response()->json(null, 204);
    }
}
