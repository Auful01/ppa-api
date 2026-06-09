<?php

namespace App\Http\Controllers;

use App\Models\InspeksiPanelBoxNetwork;
use App\Models\InvPanelBox;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InspeksiPanelBoxNetworkController extends Controller
{
    public function index()
    {
        $inspeksi_panelbox = InspeksiPanelBoxNetwork::all();
        return response()->json($inspeksi_panelbox);
    }

    public function store(Request $request)
    {
        // // cek dile upload
        // if ($request->hasFile('findings_image')) {
        //     return response()->json('finding file');
        // }else{
        //     return response()->json('file not found');
        // }
        // // cek file upload

        // start generate code
        $currentDate = Carbon::now();
        $year = $currentDate->format('Y');
        $month = $currentDate->month;
        $day = $currentDate->day;

        $maxId = InspeksiPanelBoxNetwork::max('id');

        if (is_null($maxId)) {
            $maxId = 0;
        }

        $uniqueString = 'PICA/PB/' . $year . '/' . str_pad(($maxId % 10000) + 1, 2, '0', STR_PAD_LEFT);
        // end generate code

        if (!empty($request->file('findings_image'))) {
            if (!empty($request->file('action_image'))) {
                //upload image
                $findings_image = $request->file('findings_image');
                $path_findings_image = $findings_image->store('images', 'public');
                $new_path_findings = 'storage/' . $path_findings_image;

                $action_image = $request->file('action_image');
                $path_action_image = $action_image->store('images', 'public');
                $new_path_action = 'storage/' . $path_action_image;

                $dataInspection = [
                    'cleanliness' => $request->cleanliness,
                    'conditions' => $request->conditions,
                    'cable_arrangement' => $request->cable_arrangement,
                    'remarks' => $request->remarks,
                    'created_date' => Carbon::now()->format('Y-m-d'),
                    'inspection_by' => Auth::user()->name,
                    'inspection_at' => Carbon::now(),
                    'inspection_status' => 'sudah_inspeksi',
                    'findings' => $request->findings,
                    'findings_action' => $request->findings_action,
                    'findings_status' => $request->findings_status,
                    'due_date' => $request->due_date,
                    'pica_number' => $uniqueString,
                    'findings_image' => url($new_path_findings),
                    'action_image' => url($new_path_action),
                ];
                $data['udpateInspeksi'] = InspeksiPanelBoxNetwork::firstWhere('id', $request->id)->update($dataInspection);
            } else {
                //upload image
                $findings_image = $request->file('findings_image');
                $path_findings_image = $findings_image->store('images', 'public');
                $new_path_findings = 'storage/' . $path_findings_image;

                $dataInspection = [
                    'cleanliness' => $request->cleanliness,
                    'conditions' => $request->conditions,
                    'remarks' => $request->remarks,
                    'cable_arrangement' => $request->cable_arrangement,
                    'created_date' => Carbon::now()->format('Y-m-d'),
                    'inspection_by' => Auth::user()->name,
                    'inspection_at' => Carbon::now(),
                    'inspection_status' => 'sudah_inspeksi',
                    'findings' => $request->findings,
                    'findings_status' => $request->findings_status,
                    'due_date' => $request->due_date,
                    'pica_number' => $uniqueString,
                    'findings_image' => url($new_path_findings),
                ];
            }
            $data['udpateInspeksi'] = InspeksiPanelBoxNetwork::firstWhere('id', $request->id)->update($dataInspection);
        } else {
            if (!empty($request->file('action_image'))) {
                //upload image
                $action_image = $request->file('action_image');
                $path_action_image = $action_image->store('images', 'public');
                $new_path_action = 'storage/' . $path_action_image;

                $dataInspection = [
                    'cleanliness' => $request->cleanliness,
                    'conditions' => $request->conditions,
                    'remarks' => $request->remarks,
                    'cable_arrangement' => $request->cable_arrangement,
                    'created_date' => Carbon::now()->format('Y-m-d'),
                    'inspection_by' => Auth::user()->name,
                    'inspection_at' => Carbon::now(),
                    'inspection_status' => 'sudah_inspeksi',
                    'findings_action' => $request->findings_action,
                    'findings_status' => $request->findings_status,
                    'action_image' => url($new_path_action),
                ];
                $data['udpateInspeksi'] = InspeksiPanelBoxNetwork::firstWhere('id', $request->id)->update($dataInspection);
            } else {
                $dataInspection = [
                    'cleanliness' => $request->cleanliness,
                    'conditions' => $request->conditions,
                    'remarks' => $request->remarks,
                    'cable_arrangement' => $request->cable_arrangement,
                    'created_date' => Carbon::now()->format('Y-m-d'),
                    'inspection_by' => Auth::user()->name,
                    'inspection_at' => Carbon::now(),
                    'inspection_status' => 'sudah_inspeksi',
                ];
            }
            $data['udpateInspeksi'] = InspeksiPanelBoxNetwork::firstWhere('id', $request->id)->update($dataInspection);
        }

        if (!empty($request->inventory_status)) {
            $getDataInspeksi = InspeksiPanelBoxNetwork::where('id', $request->id)->first();
            $getDataInventory = InvPanelBox::where('id', $getDataInspeksi->inv_panel_box_id)->first();
            $dataInventory = [
                'status' => $request->inventory_status,
            ];
            $data['udpateInspeksi'] = InvPanelBox::firstWhere('id', $getDataInventory->id)->update($dataInventory);
        }
        return response()->json($dataInspection, 201);
    }

    public function approval(Request $request)
    {
        $dataCheckStatusInspeksi = InspeksiPanelBoxNetwork::where('id', $request->id)->value('inspection_status');
        if ($dataCheckStatusInspeksi == 'sudah_inspeksi') {
            if ($request->approvalType == 'accept') {
                $dataApproveal = [
                    'approved_by' => Auth::user()->name,
                    'status_approval' => 'approve',
                ];
            } else {
                $dataApproveal = [
                    'approved_by' => Auth::user()->name,
                    'status_approval' => 'reject',
                ];
            }
            $data['udpateInspeksiApproval'] = InspeksiPanelBoxNetwork::firstWhere('id', $request->id)->update($dataApproveal);
            return response()->json($data);
        }else{
            return response()->json(['message' => 'data ini belum di inspeksi']);
        }
    }

    public function approvalAll(Request $request)
    {
        $month = $request->month;
        $year = $request->year;
        if ($request->approvalType == 'accept') {
            $dataApproveal = [
                'approved_by' => Auth::user()->name,
                'status_approval' => 'approve',
            ];
        } else {
            $dataApproveal = [
                'approved_by' => Auth::user()->name,
                'status_approval' => 'reject',
            ];
        }
        InspeksiPanelBoxNetwork::where('month', $month)
            ->where('year', $year)
            ->where('inspection_status', 'sudah_inspeksi')
            ->update($dataApproveal);
        return response()->json(['message' => 'Approve all updated successfully']);
    }

    public function show($id)
    {
        $inspeksi_panelbox = InspeksiPanelBoxNetwork::find($id);
        if (is_null($inspeksi_panelbox)) {
            return response()->json(['message' => 'Panelbox Data not found'], 404);
        }
        return response()->json($inspeksi_panelbox);
    }

    public function destroy($id)
    {
        $inspeksi_panelbox = InspeksiPanelBoxNetwork::find($id);
        if (is_null($inspeksi_panelbox)) {
            return response()->json(['message' => 'Panelbox Data not found'], 404);
        }
        $inspeksi_panelbox->delete();
        return response()->json(['message' => 'Data has deleted'], 204);
    }
}