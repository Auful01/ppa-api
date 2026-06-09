<?php

namespace App\Http\Controllers;

use App\Models\InspeksiTower;
use App\Models\InvTower;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InspeksiTowerController extends Controller
{
    public function index()
    {
        $inspeksiTower = InspeksiTower::all();
        return response()->json($inspeksiTower);
    }

    public function store(Request $request)
    {
        // Gabungkan inputan menjadi satu string
        $physic_condition_tower = implode(', ', [
            $request['konstruksi_tower'],
            $request['kebersihan_tower'],
            $request['kondisi_warna_tower'],
            $request['lampu_area'],
            $request['lampu_obl'],
            $request['tower_verticality'],
            $request['informasi_pj_area'],
        ]);

        $physic_condition_tower_text = implode(', ', [
            $request['konstruksi_tower_text'],
            $request['kebersihan_tower_text'],
            $request['kondisi_warna_tower_text'],
            $request['lampu_area_text'],
            $request['lampu_obl_text'],
            $request['tower_verticality_text'],
            $request['informasi_pj_area_text'],
        ]);

        $grounding_tower = implode(', ', [
            $request['nilai_grounding_control_box'],
            $request['nilai_grounding_tower'],
            $request['nilai_grounding_fence'],
            $request['kondisi_bar_grounding_tower'],
            $request['kondisi_bar_grounding_control_box'],
            $request['kondisi_bolt_connection'],
        ]);

        $grounding_tower_text = implode(', ', [
            $request['nilai_grounding_control_boxtext'],
            $request['nilai_grounding_tower_text'],
            $request['nilai_grounding_fence_text'],
            $request['kondisi_bar_grounding_tower_text'],
            $request['kondisi_bar_grounding_control_box_text'],
            $request['kondisi_bolt_connection_text'],
        ]);

        $fence_tower = implode(', ', [
            $request['kondisi_pagar'],
            $request['kondisi_kebersihan_pagar'],
            $request['kondisi_saluran_air'],
            $request['kondisi_kawat_berduri'],
            $request['pintu_tower'],
        ]);

        $fence_tower_text = implode(', ', [
            $request['kondisi_pagar_text'],
            $request['kondisi_kebersihan_pagar_text'],
            $request['kondisi_saluran_air_text'],
            $request['kondisi_kawat_berduri_text'],
            $request['pintu_tower_text'],
        ]);

        // start generate code
        $currentDate = Carbon::now();
        $year = $currentDate->format('Y');
        $month = $currentDate->month;
        $day = $currentDate->day;

        $maxId = InspeksiTower::max('id');

        if (is_null($maxId)) {
            $maxId = 0;
        }

        $uniqueString = 'PICA/BLC/' . $year . '/' . str_pad(($maxId % 10000) + 1, 2, '0', STR_PAD_LEFT);
        // end generate code

        if (!empty($request->file('findings_image'))) {
            if (!empty($request->file('action_image'))) {
                //temuan string array
                $findingArray = [];
                foreach ($request->findings as $finds) {
                    $findingArray[] = $finds;
                }
                $findingArrayString = implode(', ', $findingArray);

                //temuan action string array
                $findingActionArray = [];
                foreach ($request->findings_action as $action) {
                    $findingActionArray[] = $action;
                }
                $findingActionArrayString = implode(', ', $findingActionArray);

                //upload image
                $imageFindingPaths = [];
                foreach ($request->file('findings_image') as $findings_image) {
                    $path_findings_image = $findings_image->store('images', 'public');
                    $new_path_findings = 'storage/' . $path_findings_image;
                    $imageFindingPaths[] = url($new_path_findings);
                }
                $imagePathsFindingString = implode(', ', $imageFindingPaths);

                $imageActionPaths = [];
                foreach ($request->file('action_image') as $action_image) {
                    $path_action_image = $action_image->store('images', 'public');
                    $new_path_action = 'storage/' . $path_action_image;
                    $imageActionPaths[] = $new_path_action;
                }
                $imagePathsActionString = implode(', ', $imageActionPaths);

                $dataInspection = [
                    'inspection_status' => 'sudah_inspeksi',
                    'inspector' => 'user login',
                    'condition' => $request->condition,
                    'physic_condition_tower' => $physic_condition_tower,
                    'physic_condition_tower_text' => $physic_condition_tower_text,
                    'grounding_tower' => $grounding_tower,
                    'grounding_tower_text' => $grounding_tower_text,
                    'fence_tower' => $fence_tower,
                    'fence_tower_text' => $fence_tower_text,
                    'findings' => $findingArrayString,
                    'findings_action' => $findingActionArrayString,
                    'findings_status' => $request->findings_status,
                    'findings_image' => $imagePathsFindingString,
                    'action_image' => $imagePathsActionString,
                    'remarks' => $request->remarks,
                    'due_date' => $request->due_date,
                    'list_of_needs' => $request->list_of_needs,
                    'pica_number' => $uniqueString,
                    'created_date' => Carbon::now()->format('Y-m-d'),
                ];
                // return response()->json($dataInspection);
                $data['udpateInspeksi'] = InspeksiTower::firstWhere('id', $request->id)->update($dataInspection);
            } else {

                //temuan string array
                $findingArray = [];
                foreach ($request->findings as $finds) {
                    $findingArray[] = $finds;
                }
                $findingArrayString = implode(', ', $findingArray);


                $imageFindingPaths = [];
                foreach ($request->file('findings_image') as $findings_image) {
                    $path_findings_image = $findings_image->store('images', 'public');
                    $new_path_findings = 'storage/' . $path_findings_image;
                    $imageFindingPaths[] = url($new_path_findings);
                }
                $imagePathsFindingString = implode(', ', $imageFindingPaths);

                $dataInspection = [
                    'inspection_status' => 'sudah_inspeksi',
                    'inspector' => 'user login',
                    'condition' => $request->condition,
                    'physic_condition_tower' => $physic_condition_tower,
                    'physic_condition_tower_text' => $physic_condition_tower_text,
                    'grounding_tower' => $grounding_tower,
                    'grounding_tower_text' => $grounding_tower_text,
                    'fence_tower' => $fence_tower,
                    'fence_tower_text' => $fence_tower_text,
                    'findings' => $findingArrayString,
                    'findings_status' => $request->findings_status,
                    'findings_image' => $imagePathsFindingString,
                    'remarks' => $request->remarks,
                    'due_date' => $request->due_date,
                    'list_of_needs' => $request->list_of_needs,
                    'pica_number' => $uniqueString,
                    'created_date' => Carbon::now()->format('Y-m-d'),
                ];
            }
            // return response()->json($dataInspection);
            $data['udpateInspeksi'] = InspeksiTower::firstWhere('id', $request->id)->update($dataInspection);
        } else {
            if (!empty($request->file('action_image'))) {
                //temuan action string array
                $findingActionArray = [];
                foreach ($request->findings_action as $action) {
                    $findingActionArray[] = $action;
                }
                $findingActionArrayString = implode(', ', $findingActionArray);

                //upload image
                $imageActionPaths = [];
                foreach ($request->file('action_image') as $action_image) {
                    $path_action_image = $action_image->store('images', 'public');
                    $new_path_action = 'storage/' . $path_action_image;
                    $imageActionPaths[] = $new_path_action;
                }
                $imagePathsActionString = implode(', ', $imageActionPaths);

                $dataInspection = [
                    'inspection_status' => 'sudah_inspeksi',
                    'inspector' => 'user login',
                    'condition' => $request->condition,
                    'physic_condition_tower' => $physic_condition_tower,
                    'physic_condition_tower_text' => $physic_condition_tower_text,
                    'grounding_tower' => $grounding_tower,
                    'grounding_tower_text' => $grounding_tower_text,
                    'fence_tower' => $fence_tower,
                    'fence_tower_text' => $fence_tower_text,
                    'findings_action' => $findingActionArrayString,
                    'findings_status' => $request->findings_status,
                    'action_image' => $imagePathsActionString,
                    'remarks' => $request->remarks,
                    'due_date' => $request->due_date,
                    'list_of_needs' => $request->list_of_needs,
                    'pica_number' => $uniqueString,
                    'created_date' => Carbon::now()->format('Y-m-d'),
                ];
            // return response()->json($dataInspection);
            $data['udpateInspeksi'] = InspeksiTower::firstWhere('id', $request->id)->update($dataInspection);
            } else {
                $dataInspection = [
                    'inspection_status' => 'sudah_inspeksi',
                    'inspector' => 'user login',
                    'condition' => $request->condition,
                    'physic_condition_tower' => $physic_condition_tower,
                    'physic_condition_tower_text' => $physic_condition_tower_text,
                    'grounding_tower' => $grounding_tower,
                    'grounding_tower_text' => $grounding_tower_text,
                    'fence_tower' => $fence_tower,
                    'fence_tower_text' => $fence_tower_text,
                    'remarks' => $request->remarks,
                    'list_of_needs' => $request->list_of_needs,
                    'pica_number' => $uniqueString,
                    'created_date' => Carbon::now()->format('Y-m-d'),
                ];
            }
            // return response()->json($dataInspection);
            $data['udpateInspeksi'] = InspeksiTower::firstWhere('id', $request->id)->update($dataInspection);
        }

        if (!empty($request->inventory_status)) {
            $getDataInspeksi = InspeksiTower::where('id', $request->id)->first();
            $getDataInventory = InvTower::where('id', $getDataInspeksi->inv_tower_id)->first();
            $dataInventory = [
                'status' => $request->inventory_status,
            ];
            $data['udpateInspeksi'] = InvTower::firstWhere('id', $getDataInventory->id)->update($dataInventory);
        }
        return response()->json($data, 201);
    }

    public function approval(Request $request)
    {
        $dataCheckStatusInspeksi = InspeksiTower::where('id', $request->id)->value('inspection_status');
        if ($dataCheckStatusInspeksi == 'sudah_inspeksi') {
            if ($request->approvalType == 'accept') {
                $dataApproval = [
                    'approved_by' => Auth::user()->name,
                    'status_approval' => 'approve',
                ];
            } else {
                $dataApproval = [
                    'approved_by' => Auth::user()->name,
                    'status_approval' => 'reject',
                ];
            }
            $data['udpateInspeksiApproval'] = InspeksiTower::where('id', $request->id)->update($dataApproval);
            return response()->json($data);
        } else {
            return response()->json(['message' => 'data ini belum di inspeksi']);
        }
    }

    public function approvalAll(Request $request)
    {
        $yearNow = Carbon::now()->format('Y');

        if ($request->approvalType == 'accept') {
            $dataApproval = [
                'approved_by' => Auth::user()->name,
                'status_approval' => 'approve',
            ];
        } else {
            $dataApproval = [
                'approved_by' => Auth::user()->name,
                'status_approval' => 'reject',
            ];
        }
        $data['udpateInspeksiApprovalAll'] = InspeksiTower::where('year', $yearNow)->where('inspection_status', 'sudah_inspeksi')->update($dataApproval);
        return response()->json(['message' => 'Approve all updated successfully']);
    }

    public function showData()
    {
        // $findingArray = [];
        // foreach ($request->findings as $finds) {
        //     $findingArray[] = $finds;
        // }
        // $findingArrayString = implode(', ', $findingArray);

        // //explode get temuan
        // $explodeArrayFinding = explode(', ', $findingArrayString);
        // $i=0;
        // foreach ($explodeArrayFinding as $key => $explodeDataFinding) {
        //     $data['temuan'.$i] = $explodeArrayFinding[$i];
        //     $i++;
        // }
        // return response()->json($data);

    }

    public function show($id)
    {
        $inspeksiTower = InspeksiTower::find($id);
        if (is_null($inspeksiTower)) {
            return response()->json(['message' => 'Data not found'], 404);
        }
        return response()->json($inspeksiTower);
    }

    public function destroy($id)
    {
        $inspeksiTower = InspeksiTower::find($id);
        if (is_null($inspeksiTower)) {
            return response()->json(['message' => 'Data not found'], 404);
        }
        $inspeksiTower->delete();
        return response()->json(['message' => 'Data has deleted'], 204);
    }
}
