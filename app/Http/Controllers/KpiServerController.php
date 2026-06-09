<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KpiServerController extends Controller
{
    public function showKpi(Request $request)
    {
        $sortingDateStart = $request->start_date;
        $sortingDateStartLength = strtotime($sortingDateStart);
        $sortingDateEnd = $request->end_date;
        $sortingDateEndLength = strtotime($sortingDateEnd);

        $date_diff = $sortingDateEndLength - $sortingDateStartLength;
        $count['daysLength'] = floor($date_diff / (60 * 60 * 24)) + 1;

        $count['serverBreakdown'] = DB::table('perangkat_breakdowns')
            ->where('device_category', 'Server')
            ->where('created_date', '>=', $request->start_date)
            ->where('created_date', '<=', $request->end_date)
            ->count(DB::raw('DISTINCT inventory_number'));

        $count['countInvServer'] = DB::table('inv_servers')
            ->count(DB::raw('DISTINCT server_name'));

        $count['maintenance'] = DB::table('perangkat_breakdowns')->where('device_category', 'Server')->where('root_cause', 'MAINTENANACE')->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();
        $count['os'] = DB::table('perangkat_breakdowns')->where('device_category', 'Server')->where('root_cause', 'OS')->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();
        $count['hdd'] = DB::table('perangkat_breakdowns')->where('device_category', 'Server')->where('root_cause', 'HDD-PHYSICAL')->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();
        $count['ram'] = DB::table('perangkat_breakdowns')->where('device_category', 'Server')->where('root_cause', 'RAM-PHYSICAL')->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();
        $count['wiring'] = DB::table('perangkat_breakdowns')->where('device_category', 'Server')->where('root_cause', 'WIRING')->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();
        $count['resource'] = DB::table('perangkat_breakdowns')->where('device_category', 'Server')->where('root_cause', 'RESOURCE')->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();
        $count['power_source'] = DB::table('perangkat_breakdowns')->where('device_category', 'Server')->where('root_cause', 'POWER SOURCE')->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();
        $count['other'] = DB::table('perangkat_breakdowns')->where('device_category', 'Server')->where('root_cause', 'other')->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();

        $count['get_data_server_breakdown'] = DB::table('perangkat_breakdowns')->where('device_category', 'Server')->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->get();

        if (!empty($count['get_data_server_breakdown'])) {
            $data_perangkat['totalBreakDownTime'] = 0;
            foreach ($count['get_data_server_breakdown'] as $data) {
                $data_perangkat['totalBreakdown'] = DB::table('perangkat_breakdowns')->where('device_category', 'Server')->count();

                $start = Carbon::parse($data->start_progress);
                $end = Carbon::parse($data->end_progress);

                $diff = $end->diff($start);

                $count['days'] = $diff->d;
                $count['hours'] = $diff->h;
                $count['minutes'] = $diff->i;
                $count['seconds'] = $diff->s;

                $count['hoursInLaravel'] = $start->diffInHours($end);
                $count['minutesInLaravel'] = $start->diffInMinutes($end);
                $count['humansFormat'] = $start->diffForHumans($end);

                $data_perangkat['startTimeBreakdown'] = $data->start_progress;
                $data_perangkat['endTimeBreakdown'] = $data->end_progress;
                $data_perangkat['deviceCategory'] = $data->device_category;
                $data_perangkat['serverName'] = $data->device_name;
                $data_perangkat['inventoryNumber'] = $data->device_name;
                $data_perangkat['totalPerangkatInventory'] = $count['countInvServer'];
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
                    'startTimeBreakdown' => $data_perangkat['startTimeBreakdown'],
                    'endTimeBreakdown' => $data_perangkat['endTimeBreakdown'],
                    'serverName' => $data_perangkat['serverName'],
                    'inventoryNumber' => $data_perangkat['inventoryNumber'],
                    'breakdownTime' => $data_perangkat['breakDownTime'],
                    'breakdownDays' => $count['days'] . ' Hari' . ' - ' . $count['hours'] . ' Jam' . ' - ' . $count['minutes'] . ' Menit',
                    'runningTargetsPerangkat' => $data_perangkat['targetsRunningTimesInRangeDatePerangkat'],
                    'runningTimePerangkat' => $data_perangkat['runningTime'],
                    'runningTimePercentage' => $data_perangkat['runningTimePercentage'],
                );

                $dataRootCausePerangkat = array(
                    'maintenance' => $count['maintenance'],
                    'os' => $count['os'],
                    'hdd' => $count['hdd'],
                    'ram' => $count['ram'],
                    'wiring' => $count['wiring'],
                    'resource' => $count['resource'],
                    'power_source' => $count['power_source'],
                    'other' => $count['other'],
                );

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

        return response()->json($count);
    }
}
