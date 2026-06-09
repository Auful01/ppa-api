<?php

namespace App\Http\Controllers;

use App\Models\PerangkatBreakdown;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KpiDeviceController extends Controller
{
    public function showKpi(Request $request)
    {
        $sortingDateStart = $request->start_date;
        $sortingDateStartLength = strtotime($sortingDateStart);
        $sortingDateEnd = $request->end_date;
        $sortingDateEndLength = strtotime($sortingDateEnd);

        $date_diff = $sortingDateEndLength - $sortingDateStartLength;
        $count['daysLength'] = floor($date_diff / (60 * 60 * 24)) + 1;

        $categoryKpi = $request->category;

        $count['count_device_breakdown'] = DB::table('perangkat_breakdowns')
            ->where('device_category', $categoryKpi)
            ->where('created_date', '>=', $request->start_date)
            ->where('created_date', '<=', $request->end_date)
            ->count(DB::raw('DISTINCT inventory_number'));

        if ($categoryKpi == 'Laptop') {
            $count['count_all_device'] = DB::table('inv_laptops')
                ->count(DB::raw('DISTINCT laptop_code'));
            $garansiOpen = DB::table('perangkat_breakdowns')->where('device_category', $categoryKpi)->where('root_cause', 'GARANSI')->where('status', '!=', 'CLOSED')->where('created_date', '<=', $request->end_date)->count();
            $garansiClosed = DB::table('perangkat_breakdowns')->where('device_category', $categoryKpi)->where('root_cause', 'GARANSI')->where('status', 'CLOSED')->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();
            $count['total_laptop_garansi'] = $garansiOpen + $garansiClosed;

            $count['hardisk'] = DB::table('perangkat_breakdowns')->where('device_category', $categoryKpi)->where('root_cause', 'HARDISK')->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();
            $count['os'] = DB::table('perangkat_breakdowns')->where('device_category', $categoryKpi)->where('root_cause', 'OS')->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();
            $count['motherboard'] = DB::table('perangkat_breakdowns')->where('device_category', $categoryKpi)->where('root_cause', 'MOTHERBOARD')->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();
            $count['power_supply'] = DB::table('perangkat_breakdowns')->where('device_category', $categoryKpi)->where('root_cause', 'POWER SUPPLY')->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();
            $count['ram'] = DB::table('perangkat_breakdowns')->where('device_category', $categoryKpi)->where('root_cause', 'RAM')->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();
            $count['monitor'] = DB::table('perangkat_breakdowns')->where('device_category', $categoryKpi)->where('root_cause', 'MONITOR')->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();
            $count['lain_lain'] = DB::table('perangkat_breakdowns')->where('device_category', $categoryKpi)->where('root_cause', 'LAIN-LAIN')->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();
        } elseif ($categoryKpi == 'Komputer') {
            $count['count_all_device'] = DB::table('inv_computers')
                ->count(DB::raw('DISTINCT computer_code'));

            $count['hardisk'] = DB::table('perangkat_breakdowns')->where('device_category', $categoryKpi)->where('root_cause', 'HARDISK')->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();
            $count['os'] = DB::table('perangkat_breakdowns')->where('device_category', $categoryKpi)->where('root_cause', 'OS')->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();
            $count['motherboard'] = DB::table('perangkat_breakdowns')->where('device_category', $categoryKpi)->where('root_cause', 'MOTHERBOARD')->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();
            $count['power_supply'] = DB::table('perangkat_breakdowns')->where('device_category', $categoryKpi)->where('root_cause', 'POWER SUPPLY')->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();
            $count['ram'] = DB::table('perangkat_breakdowns')->where('device_category', $categoryKpi)->where('root_cause', 'RAM')->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();
            $count['monitor'] = DB::table('perangkat_breakdowns')->where('device_category', $categoryKpi)->where('root_cause', 'MONITOR')->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();
            $count['lain_lain'] = DB::table('perangkat_breakdowns')->where('device_category', $categoryKpi)->where('root_cause', 'LAIN-LAIN')->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();
        } elseif ($categoryKpi == 'Printer') {
            $count['count_all_device'] = DB::table('inv_printers')
                ->count(DB::raw('DISTINCT printer_code'));

            $count['tinta_habis'] = DB::table('perangkat_breakdowns')->where('device_category', $categoryKpi)->where('root_cause', 'TINTA HABIS')->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();
            $count['tinta_bocor'] = DB::table('perangkat_breakdowns')->where('device_category', $categoryKpi)->where('root_cause', 'TINTA BOCOR')->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();
            $count['install'] = DB::table('perangkat_breakdowns')->where('device_category', $categoryKpi)->where('root_cause', 'INSTALL')->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();
            $count['pemindahan'] = DB::table('perangkat_breakdowns')->where('device_category', $categoryKpi)->where('root_cause', 'PEMINDAHAN')->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();
            $count['perlu_reset'] = DB::table('perangkat_breakdowns')->where('device_category', $categoryKpi)->where('root_cause', 'PERLU RESET')->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();
            $count['sensor'] = DB::table('perangkat_breakdowns')->where('device_category', $categoryKpi)->where('root_cause', 'SENSOR')->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();
            $count['inspeksi'] = DB::table('perangkat_breakdowns')->where('device_category', $categoryKpi)->where('root_cause', 'INSPEKSI')->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();
            $count['lain_lain_printer'] = DB::table('perangkat_breakdowns')->where('device_category', $categoryKpi)->where('root_cause', 'LAIN-LAIN')->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();
        }

        $count['get_data_all_breakdown'] = DB::table('perangkat_breakdowns')->where('device_category', $categoryKpi)->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->get();
        if (!empty($count['get_data_all_breakdown'])) {
            $data_perangkat['totalBreakDownTime'] = 0;
            foreach ($count['get_data_all_breakdown'] as $data) {
                if ($categoryKpi == 'Laptop') {
                    $data_perangkat['totalBreakdown'] = DB::table('perangkat_breakdowns')->where('device_category', 'Laptop')->count();
                    $data_inv_perangkat['data_laptop'] = DB::table('inv_laptops')->where('laptop_code', $data->inventory_number)->first();
                    $data_inv_perangkat['data_users_alls'] = DB::table('user_alls')->where('id', $data_inv_perangkat['data_laptop']->user_alls_id)->first();
                } elseif ($categoryKpi == 'Komputer') {
                    $data_inv_perangkat['data_komputer'] = DB::table('inv_computers')->where('computer_code', $data->inventory_number)->first();
                    $data_perangkat['totalBreakdown'] = DB::table('perangkat_breakdowns')->where('device_category', 'Komputer')->count();
                    // dd($data_perangkat['totalBreakdown']);
                    $data_inv_perangkat['data_users_alls'] = DB::table('user_alls')->where('id', $data_inv_perangkat['data_komputer']->user_alls_id)->first();
                } elseif ($categoryKpi == 'Printer') {
                    $data_inv_perangkat['data_printer'] = DB::table('inv_printers')->where('printer_code', $data->inventory_number)->first();
                    $data_perangkat['totalBreakdown'] = DB::table('perangkat_breakdowns')->where('device_category', 'Printer')->count();
                    // $data_inv_perangkat['data_users_alls'] = DB::table('user_alls')->where('id', $data->user_alls_id)->get();
                }

                if ($data->end_progress == null || $data->end_progress == '') {
                    $end_progress = Carbon::now();
                    $start = Carbon::parse($data->start_progress);
                    $end = Carbon::parse($end_progress);
                } else {
                    $start = Carbon::parse($data->start_progress);
                    $end = Carbon::parse($data->end_progress);
                }

                $diff = $end->diff($start);

                $count['days'] = $diff->d;
                $count['hours'] = $diff->h;
                $count['minutes'] = $diff->i;
                $count['seconds'] = $diff->s;

                $count['hoursInLaravel'] = $start->diffInHours($end);
                $count['minutesInLaravel'] = $start->diffInMinutes($end);
                $count['humansFormat'] = $start->diffForHumans($end);

                $data_perangkat['deviceCategory'] = $data->device_category;
                $data_perangkat['totalPerangkatInventory'] = $count['count_all_device'];
                $data_perangkat['inventoryNumber'] = $data->inventory_number;
                if ($categoryKpi == 'Printer') {
                    $data_perangkat['username'] = $data_inv_perangkat['data_printer']->division;
                    $data_perangkat['userDepartment'] = $data_inv_perangkat['data_printer']->department;
                }else{
                    $data_perangkat['username'] = $data_inv_perangkat['data_users_alls']->username;
                    $data_perangkat['userDepartment'] = $data_inv_perangkat['data_users_alls']->department;
                }
                $data_perangkat['targetsRunningTimesInRangeDatePerangkat'] = $count['daysLength'] * 24; // in hours
                $data_perangkat['targetsRunningTimesInRangeDateTable'] = $count['daysLength'] * 24 * $data_perangkat['totalBreakdown']; // in hours
                $data_perangkat['breakDownTime'] = round($count['hoursInLaravel'], 2);
                $data_perangkat['totalBreakDownTime'] += $data_perangkat['breakDownTime'];
                $data_perangkat['runningTime'] = round($data_perangkat['targetsRunningTimesInRangeDatePerangkat'] - $data_perangkat['breakDownTime'], 2);
                $data_perangkat['runningTimePercentage'] = round(($data_perangkat['runningTime'] / $data_perangkat['targetsRunningTimesInRangeDatePerangkat']) * 100, 2);
                $data_perangkat['totalBreakDownTimeTableBreakDown'] = round($data_perangkat['totalBreakDownTime'], 2);
                $data_perangkat['RunningTimesInRangeDateTable'] = round($count['daysLength'] * 24 * $data_perangkat['totalBreakdown'] - ($data_perangkat['totalBreakDownTimeTableBreakDown']), 2); // in hours
                $data_perangkat['achievement'] = round(($data_perangkat['RunningTimesInRangeDateTable'] / $data_perangkat['targetsRunningTimesInRangeDateTable']) * 100, 2);
                
                $dataPharse[] = array(
                    'deviceCategory' => $data_perangkat['deviceCategory'],
                    'username' => $data_perangkat['username'],
                    'department' => $data_perangkat['userDepartment'],
                    'inventoryNumber' => $data_perangkat['inventoryNumber'],
                    'breakdownTime' => $data_perangkat['breakDownTime'],
                    'runningTargetsPerangkat' => $data_perangkat['targetsRunningTimesInRangeDatePerangkat'],
                    'runningTimePerangkat' => $data_perangkat['runningTime'],
                    'runningTimePercentage' => $data_perangkat['runningTimePercentage'],
                );
                if ($categoryKpi == 'Laptop' || $categoryKpi == 'Komputer') {

                    $dataRootCausePerangkat = array(
                        'hardisk' => $count['hardisk'],
                        'os' => $count['os'],
                        'motherboard' => $count['motherboard'],
                        'power_supply' => $count['power_supply'],
                        'ram' => $count['ram'],
                        'monitor' => $count['monitor'],
                    );
                }elseif ($categoryKpi == 'Printer') {
                    $dataRootCausePerangkat = array(
                        'tinta_habis' => $count['tinta_habis'],
                        'tinta_bocor' => $count['tinta_bocor'],
                        'install' => $count['install'],
                        'pemindahan' => $count['pemindahan'],
                        'perlu_reset' => $count['perlu_reset'],
                        'sensor' => $count['sensor'],
                        'inspeksi' => $count['inspeksi'],
                        'lain_lain' => $count['lain_lain_printer'],
                    );
                }
                $dataTableBreakdown = array(
                    'achievement' => $data_perangkat['achievement'],
                    'totalPerangkat' => $data_perangkat['totalPerangkatInventory'],
                    'runningAllPerangkat' => $data_perangkat['targetsRunningTimesInRangeDateTable'],
                    'totalBreakDownTimeTableBreakDown' => $data_perangkat['totalBreakDownTimeTableBreakDown'],
                    'totalRunningTimeTableBreakDown' => $data_perangkat['RunningTimesInRangeDateTable'], 
                    'data' => $dataPharse,
                    'dataRootCause' => $dataRootCausePerangkat,
                );

            }
            return response()->json($dataTableBreakdown);
        }
    }
}
