<?php

namespace App\Http\Controllers;

use App\Models\InspeksiAccessPoint;
use App\Models\InvAp;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InspeksiAccessPointController extends Controller
{
    public function ping()
    {
        $currentDate = Carbon::now();
        $year = $currentDate->format('Y');
        $month = $currentDate->month;

        $ips_inventory = InspeksiAccessPoint::where('month', $month)->where('year', $year)->where('device_status', 'OFFLINE')->orWhere('device_status', null)->get();
        $ips = [];

        // Loop through each product and push to the array
        foreach ($ips_inventory as $ipsInspeksi) {
            $ips[] = $ipsInspeksi->ip_address;
        }

        // Validate IP addresses -> ping all ip 4x lebih cepat
        if (!is_array($ips) || empty($ips)) {
            return response()->json(['error' => 'Not detect ip address or ip address is empty!'], 400);
        }
        $results = [];
        $timeout = 10; // Timeout in milliseconds (0.1 second)

        foreach ($ips_inventory as $ipPing) {
            $ip = trim($ipPing->ip_address);
            if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                $results[$ip] = 'Invalid IP address';
                continue;
            }

            // Execute the ping command for Windows with timeout
            $output = [];
            $status = null;
            exec("ping -n 1 -w {$timeout} " . escapeshellarg($ip), $output, $status);

            if ($status === 0) {
                $data['dataInspection'] = [
                    'condition' => 'BAGUS',
                    'inspection_status' => 'sudah_inspeksi',
                    'device_status' => 'ONLINE',
                    'inspection_at' => Carbon::now(),
                    'inspector' => 'User Login',
                    'created_date' =>  Carbon::now()->format('Y-m-d'),
                ];
                $data['updateInspeksi'] = InspeksiAccessPoint::firstWhere('id', $ipPing->id)->update($data['dataInspection']);
            } else {
                $data['dataInspection'] = [
                    // 'condition' => 'BAGUS',
                    'inspection_status' => 'belum_inspeksi',
                    'device_status' => 'OFFLINE',
                    'inspection_at' => Carbon::now(),
                    'inspector' => 'User Login',
                    'created_date' =>  Carbon::now()->format('Y-m-d'),
                ];
                $data['updateInspeksi'] = InspeksiAccessPoint::firstWhere('id', $ipPing->id)->update($data['dataInspection']);
            }

            $results[$ip] = [
                'success' => $status === 0,
                'output' => $output
            ];
        }
        return response()->json(['success' => 'Success ping all inspection access point', 'Result' => $results], 200);
    }

    public function singlePing(Request $request)
    {
        $id = $request->input('id');
        $ip = InspeksiAccessPoint::where('id', $id)->first();

        // Validate IP address
        if (!filter_var($ip->ip_address, FILTER_VALIDATE_IP)) {
            return response()->json(['error' => 'Invalid IP address'], 400);
        }

        // Set the timeout for the ping command
        $timeout = 10; // Timeout in milliseconds (1 second)

        // Execute the ping command for Windows with timeout
        $output = [];
        $status = null;
        exec("ping -n 3 -w {$timeout} " . escapeshellarg($ip->ip_address), $output, $status);

        if ($status === 0) {
            $data['dataInspection'] = [
                'condition' => 'BAGUS',
                'inspection_status' => 'sudah_inspeksi',
                'device_status' => 'ONLINE',
                'inspection_at' => Carbon::now(),
                'inspector' => 'User Login',
                'created_date' =>  Carbon::now()->format('Y-m-d'),
            ];
            $data['updateInspeksi'] = InspeksiAccessPoint::firstWhere('id', $id)->update($data['dataInspection']);
        } else {
            $data['dataInspection'] = [
                // 'condition' => 'BAGUS',
                'inspection_status' => 'belum_inspeksi',
                'device_status' => 'OFFLINE',
                'inspection_at' => Carbon::now(),
                'inspector' => 'User Login',
                'created_date' =>  Carbon::now()->format('Y-m-d'),
            ];
            $data['updateInspeksi'] = InspeksiAccessPoint::firstWhere('id', $id)->update($data['dataInspection']);
        }

        $result = [
            'success' => $status === 0,
            'output' => $output
        ];

        return response()->json($result);
    }

    public function update(Request $request)
    {
        $id = $request->id;
        $dataAP = InspeksiAccessPoint::where('id', $id)->first();

        $currentDate = Carbon::now();
        $year = $currentDate->format('Y');
        $month = $currentDate->month;
        $day = $currentDate->day;

        $maxId = InspeksiAccessPoint::max('id');

        if (is_null($maxId)) {
            $maxId = 0;
        }

        $uniqueString = 'PICA/AP/' . $year . '/' . str_pad(($maxId % 10000) + 1, 2, '0', STR_PAD_LEFT);

        if ($request->condition == 'RUSAK' || $request->condition == 'SCRAP') {
            if (!empty($request->file('findings_image')) && $request->findings) {

                $findings_image = $request->file('findings_image');
                // return response()->json($findings_image);
                $path_findings_image = $findings_image->store('images', 'public');
                $new_path_findings = 'storage/' . $path_findings_image;

                if (!empty($request->file('action_image')) && !empty($request->findings_action)) {
                    $action_image = $request->file('action_image');
                    $path_action_image = $action_image->store('images', 'public');
                    $new_path_action = 'storage/' . $path_action_image;

                    $data['dataInspectionEdit'] = [
                        'condition' => $request->condition,
                        'remarks' => $request->remark,

                        'findings' => $request->findings,
                        'findings_action' => $request->findings_action,
                        'findings_status' => $request->findings_status,
                        'findings_image' => url($new_path_findings),
                        'action_image' => url($new_path_action),
                        'due_date' => $request->due_date,
                        'pica_number' => $uniqueString,
                    ];
                } else {
                    $data['dataInspectionEdit'] = [
                        'condition' => $request->condition,
                        'remarks' => $request->remark,

                        'findings' => $request->findings,
                        'findings_status' => $request->findings_status,
                        'findings_image' => url($new_path_findings),
                        'due_date' => $request->due_date,
                        'pica_number' => $uniqueString,
                    ];
                }
            } else {
                $action_image = $request->file('action_image');
                $path_action_image = $action_image->store('images', 'public');
                $new_path_action = 'storage/' . $path_action_image;

                $data['dataInspectionEdit'] = [
                    'condition' => $request->condition,
                    'remarks' => $request->remark,

                    'findings_action' => $request->findings_action,
                    'findings_status' => $request->findings_status,
                    'action_image' => url($new_path_action),
                    'due_date' => $request->due_date,
                    'pica_number' => $uniqueString,
                ];
            }

            $data['dataInspectionEditInventory'] = [
                'inventory_status' => $request->inventory_status,
                'location' => $request->location,
                // 'inspection_remark' => $request->remark,
            ];

            $data['updateInspeksi'] = InspeksiAccessPoint::firstWhere('id', $id)->update($data['dataInspectionEdit']);
            $data['updateInventoryAp'] = InvAp::firstWhere('id', $dataAP->inv_ap_id)->update($data['dataInspectionEditInventory']);

            return response()->json($data);
        } else {
            $data['dataInspectionEdit'] = [
                'condition' => $request->condition,
                'remarks' => $request->remarks,
            ];

            $data['dataInspectionEditInventory'] = [
                'status' => $request->inventory_status,
                'location' => $request->location,
                // 'inspection_remark' => $request->remark,
            ];

            $data['updateInspeksi'] = InspeksiAccessPoint::firstWhere('id', $id)->update($data['dataInspectionEdit']);
            $data['updateInventoryAp'] = InvAp::firstWhere('id', $dataAP->inv_ap_id)->update($data['dataInspectionEditInventory']);
            return response()->json($data);
        }
    }

    public function approval(Request $request)
    {
        $dataCheckStatusInspeksi = InspeksiAccessPoint::where('id', $request->id)->value('inspection_status');
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
            $data['udpateInspeksiApproval'] = InspeksiAccessPoint::firstWhere('id', $request->id)->update($dataApproveal);
            return response()->json($data);
        }else{
            return response()->json(['message' => 'data ini belum di inspeksi']);
        }
    }

    public function approvalAll(Request $request)
    {
        $month =Carbon::now()->format('m');
        $year =Carbon::now()->format('Y');
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
        InspeksiAccessPoint::where('month', $month)
            ->where('year', $year)
            ->where('inspection_status', 'sudah_inspeksi')
            ->update($dataApproveal);
            return response()->json(['message' => 'Approve all updated successfully']);
    }
}
