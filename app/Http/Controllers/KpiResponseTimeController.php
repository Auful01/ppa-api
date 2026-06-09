<?php

namespace App\Http\Controllers;

use App\Models\Aduan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class KpiResponseTimeController extends Controller
{
    public function showKpi(Request $request)
    {
        if (!empty($request->year)) {
            return response()->json('Masuk Yearly');
            // $end_date = Carbon::parse($request->end_date)->toDateString();
            // $count['countAduan'] = DB::table('aduans')->where('', 'Server')->where('root_cause', 'MAINTENANACE')->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();
        } elseif (!empty($request->start_date)) {
            $data['countAduan'] = DB::table('aduans')->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();
            $data['countAduanClosed'] = DB::table('aduans')->where('status', 'CLOSED')->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();
            $data['countAduanOpen'] = DB::table('aduans')->where('status', 'OPEN')->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();
            $data['countAduanProcess'] = DB::table('aduans')->where('status', 'PROCESS')->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();
            $data['countAduanOutstanding'] = DB::table('aduans')->where('status', 'OUTSTANDING')->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();

            $data['dataAduan'] = DB::table('aduans')->where('start_response', '!=', null)->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->get();

            $data['countResponseTime'] = 0;
            $data['countResponseTimeWebsite'] = 0;
            $data['countResponseTimePcLaptop'] = 0;
            $data['countResponseTimeNetworkBuilding'] = 0;
            $data['countResponseTimePrinter'] = 0;
            $data['countResponseTimeCctv'] = 0;
            $data['countResponseTimeRadioRig'] = 0;
            $data['countResponseTimeSs6'] = 0;
            $data['countResponseTimeFileServer'] = 0;
            $data['countResponseTimeTelkomsel'] = 0;
            $data['countResponseTimeInternet'] = 0;
            $data['countResponseTimeNetworkMt'] = 0;
            $data['countResponseTimeOther'] = 0;
            $data['countResponseTimeRadioHt'] = 0;
            $data['countResponseTimeSupport'] = 0;
            $data['countResponseTimeAdministrasi'] = 0;
            $data['countResponseTimeGps'] = 0;
            $data['countResponseTime'] = 0;

            $data['tresholdTirty'] = 1800;
            $dataParse = array();

            foreach ($data['dataAduan'] as $dataAduan) {
                $data['dataResponseTimeInSecond'] = Carbon::parse($dataAduan->response_time)->secondsSinceMidnight();
                $data['countResponseTime'] += $data['dataResponseTimeInSecond'];
                if ($data['dataResponseTimeInSecond'] >= $data['tresholdTirty']) {
                    $dataParse[] = array(
                        'complaintCode' => $dataAduan->complaint_code,
                        'issue' => $dataAduan->complaint_note,
                        'dateOfComplaint' => $dataAduan->date_of_complaint,
                        'startResponse' => $dataAduan->start_response,
                        'responseTime' => $dataAduan->response_time,
                        'finishTime' => $dataAduan->end_progress,
                        'complaintName' => $dataAduan->complaint_name,
                        'location' => $dataAduan->location,
                        'complaintType' => $dataAduan->category_name,
                        'crew' => $dataAduan->crew,
                        'rootCauseProblem' => $dataAduan->root_cause,
                        'actionProblem' => $dataAduan->action_repair,
                    );
                }
            }
            $data['dataParseUptoThirtyMinutes'] = $dataParse;

            $data['avgResponseTimeInSecond'] = ($data['countResponseTime'] / $data['countAduan']);
            $data['avgResponseTime'] = Carbon::createFromTime(0, 0, 0)->addSeconds($data['avgResponseTimeInSecond'])->format('H:i:s');
            $data['achievement'] = round(($data['tresholdTirty'] / $data['avgResponseTimeInSecond']) * 100, 2);

            $data['dataAduanWebsite'] = DB::table('aduans')->where('category_name', 'WEBSITE')->where('start_response', '!=', null)->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->get();
            $data['dataAduanWebsiteCount'] = DB::table('aduans')->where('category_name', 'WEBSITE')->where('start_response', '!=', null)->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();

            $data['dataAduanPcLaptop'] = DB::table('aduans')->where('category_name', 'PC/LAPTOP')->where('start_response', '!=', null)->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->get();
            $data['dataAduanPcLaptopCount'] = DB::table('aduans')->where('category_name', 'PC/LAPTOP')->where('start_response', '!=', null)->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();

            $data['dataAduanNetworkBuilding'] = DB::table('aduans')->where('category_name', 'NETWORK BUILDING')->where('start_response', '!=', null)->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->get();
            $data['dataAduanNetworkBuildingCount'] = DB::table('aduans')->where('category_name', 'NETWORK BUILDING')->where('start_response', '!=', null)->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();

            $data['dataAduanPrinter'] = DB::table('aduans')->where('category_name', 'PRINTER')->where('start_response', '!=', null)->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->get();
            $data['dataAduanPrinterCount'] = DB::table('aduans')->where('category_name', 'PRINTER')->where('start_response', '!=', null)->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();

            $data['dataAduanCctv'] = DB::table('aduans')->where('category_name', 'CCTV')->where('start_response', '!=', null)->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->get();
            $data['dataAduanCctvCount'] = DB::table('aduans')->where('category_name', 'CCTV')->where('start_response', '!=', null)->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();

            $data['dataAduanRadioRig'] = DB::table('aduans')->where('category_name', 'RADIORIG')->where('start_response', '!=', null)->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->get();
            $data['dataAduanRadioRigCount'] = DB::table('aduans')->where('category_name', 'RADIORIG')->where('start_response', '!=', null)->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();

            $data['dataAduanSs6'] = DB::table('aduans')->where('category_name', 'SS6')->where('start_response', '!=', null)->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->get();
            $data['dataAduanSs6Count'] = DB::table('aduans')->where('category_name', 'SS6')->where('start_response', '!=', null)->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();

            $data['dataAduanFileServer'] = DB::table('aduans')->where('category_name', 'FILE SERVER')->where('start_response', '!=', null)->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->get();
            $data['dataAduanFileServerCount'] = DB::table('aduans')->where('category_name', 'FILE SERVER')->where('start_response', '!=', null)->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();

            $data['dataAduanTelkomsel'] = DB::table('aduans')->where('category_name', 'TELKOMSEL')->where('start_response', '!=', null)->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->get();
            $data['dataAduanTelkomselCount'] = DB::table('aduans')->where('category_name', 'TELKOMSEL')->where('start_response', '!=', null)->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();

            $data['dataAduanInternet'] = DB::table('aduans')->where('category_name', 'INTERNET')->where('start_response', '!=', null)->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->get();
            $data['dataAduanInternetCount'] = DB::table('aduans')->where('category_name', 'INTERNET')->where('start_response', '!=', null)->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();

            $data['dataAduanNetworkMt'] = DB::table('aduans')->where('category_name', 'NETWORK MT')->where('start_response', '!=', null)->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->get();
            $data['dataAduanNetworkMtCount'] = DB::table('aduans')->where('category_name', 'NETWORK MT')->where('start_response', '!=', null)->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();

            $data['dataAduanOther'] = DB::table('aduans')->where('category_name', 'OTHER')->where('start_response', '!=', null)->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->get();
            $data['dataAduanOtherCount'] = DB::table('aduans')->where('category_name', 'OTHER')->where('start_response', '!=', null)->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();

            $data['dataAduanRadioHt'] = DB::table('aduans')->where('category_name', 'RADIO HT')->where('start_response', '!=', null)->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->get();
            $data['dataAduanRadioHtCount'] = DB::table('aduans')->where('category_name', 'RADIO HT')->where('start_response', '!=', null)->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();

            $data['dataAduanSupport'] = DB::table('aduans')->where('category_name', 'SUPPORT')->where('start_response', '!=', null)->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->get();
            $data['dataAduanSupportCount'] = DB::table('aduans')->where('category_name', 'SUPPORT')->where('start_response', '!=', null)->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();

            $data['dataAduanAdministrasi'] = DB::table('aduans')->where('category_name', 'ADMINISTRASI')->where('start_response', '!=', null)->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->get();
            $data['dataAduanAdministrasiCount'] = DB::table('aduans')->where('category_name', 'ADMINISTRASI')->where('start_response', '!=', null)->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();

            $data['dataAduanGps'] = DB::table('aduans')->where('category_name', 'GPS')->where('start_response', '!=', null)->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->get();
            $data['dataAduanGpsCount'] = DB::table('aduans')->where('category_name', 'GPS')->where('start_response', '!=', null)->where('created_date', '>=', $request->start_date)->where('created_date', '<=', $request->end_date)->count();

            // AVG Per Category

            // Website
            foreach ($data['dataAduanWebsite'] as $website) {
                $data['dataResponseTimeInSecondWebsite'] = Carbon::parse($website->response_time)->secondsSinceMidnight();
                $data['countResponseTimeWebsite'] += $data['dataResponseTimeInSecondWebsite'];
            }
            if ($data['countResponseTimeWebsite'] != 0) {
                $data['dataResponseTimeInSecondWebsiteAvg'] = $data['countResponseTimeWebsite'] / $data['dataAduanWebsiteCount'];
                $data['avgResponseTimeWebsiteHisFormat'] = Carbon::createFromTime(0, 0, 0)->addSeconds($data['dataResponseTimeInSecondWebsiteAvg'])->format('H:i:s');
            }

            // Pc/Laptop
            foreach ($data['dataAduanPcLaptop'] as $pclaptop) {
                $data['dataResponseTimeInSecondPcLaptop'] = Carbon::parse($pclaptop->response_time)->secondsSinceMidnight();
                $data['countResponseTimePcLaptop'] += $data['dataResponseTimeInSecondPcLaptop'];
            }

            if ($data['countResponseTimePcLaptop'] != 0) {
                $data['dataResponseTimeInSecondPcLaptopAvg'] = $data['countResponseTimePcLaptop'] / $data['dataAduanPcLaptopCount'];
                $data['avgResponseTimePcLaptopHisFormat'] = Carbon::createFromTime(0, 0, 0)->addSeconds($data['dataResponseTimeInSecondPcLaptopAvg'])->format('H:i:s');
            }

            // Network Building
            foreach ($data['dataAduanNetworkBuilding'] as $networkBuilding) {
                $data['dataResponseTimeInSecondNetworkBuilding'] = Carbon::parse($networkBuilding->response_time)->secondsSinceMidnight();
                $data['countResponseTimeNetworkBuilding'] += $data['dataResponseTimeInSecondNetworkBuilding'];
            }

            if ($data['countResponseTimeNetworkBuilding'] != 0) {
                $data['dataResponseTimeInSecondNetworkBuildingAvg'] = $data['countResponseTimeNetworkBuilding'] / $data['dataAduanNetworkBuildingCount'];
                $data['avgResponseTimeNetworkBuildingHisFormat'] = Carbon::createFromTime(0, 0, 0)->addSeconds($data['dataResponseTimeInSecondNetworkBuildingAvg'])->format('H:i:s');
            }

            // Printer
            foreach ($data['dataAduanPrinter'] as $printer) {
                $data['dataResponseTimeInSecondPrinter'] = Carbon::parse($printer->response_time)->secondsSinceMidnight();
                $data['countResponseTimePrinter'] += $data['dataResponseTimeInSecondPrinter'];
            }

            if ($data['countResponseTimePrinter'] != 0) {
                $data['dataResponseTimeInSecondPrinterAvg'] = $data['countResponseTimePrinter'] / $data['dataAduanPrinterCount'];
                $data['avgResponseTimePrinterHisFormat'] = Carbon::createFromTime(0, 0, 0)->addSeconds($data['dataResponseTimeInSecondPrinterAvg'])->format('H:i:s');
            }


            // CCTV
            foreach ($data['dataAduanCctv'] as $cctv) {
                $data['dataResponseTimeInSecondCctv'] = Carbon::parse($cctv->response_time)->secondsSinceMidnight();
                $data['countResponseTimeCctv'] += $data['dataResponseTimeInSecondCctv'];
            }

            if ($data['countResponseTimeCctv'] != 0) {
                $data['dataResponseTimeInSecondCctvAvg'] = $data['countResponseTimeCctv'] / $data['dataAduanCctvCount'];
                $data['avgResponseTimeCctvHisFormat'] = Carbon::createFromTime(0, 0, 0)->addSeconds($data['dataResponseTimeInSecondCctvAvg'])->format('H:i:s');
            }

            // Radio Rig
            foreach ($data['dataAduanRadioRig'] as $radioRig) {
                $data['dataResponseTimeInSecondRadioRig'] = Carbon::parse($radioRig->response_time)->secondsSinceMidnight();
                $data['countResponseTimeRadioRig'] += $data['dataResponseTimeInSecondRadioRig'];
            }

            if ($data['countResponseTimeRadioRig'] != 0) {
                $data['dataResponseTimeInSecondRadioRigAvg'] = $data['countResponseTimeRadioRig'] / $data['dataAduanRadioRigCount'];
                $data['avgResponseTimeRadioRigHisFormat'] = Carbon::createFromTime(0, 0, 0)->addSeconds($data['dataResponseTimeInSecondRadioRigAvg'])->format('H:i:s');
            }

            // SS6
            foreach ($data['dataAduanSs6'] as $ss6) {
                $data['dataResponseTimeInSecondSs6'] = Carbon::parse($ss6->response_time)->secondsSinceMidnight();
                $data['countResponseTimeSs6'] += $data['dataResponseTimeInSecondSs6'];
            }

            if ($data['countResponseTimeSs6'] != 0) {
                $data['dataResponseTimeInSecondSs6Avg'] = $data['countResponseTimeSs6'] / $data['dataAduanSs6Count'];
                $data['avgResponseTimeSs6HisFormat'] = Carbon::createFromTime(0, 0, 0)->addSeconds($data['dataResponseTimeInSecondSs6Avg'])->format('H:i:s');
            }

            // File Server
            foreach ($data['dataAduanFileServer'] as $fileServer) {
                $data['dataResponseTimeInSecondFileServer'] = Carbon::parse($fileServer->response_time)->secondsSinceMidnight();
                $data['countResponseTimeFileServer'] += $data['dataResponseTimeInSecondFileServer'];
            }

            if ($data['countResponseTimeFileServer'] != 0) {
                $data['dataResponseTimeInSecondFileServerAvg'] = $data['countResponseTimeFileServer'] / $data['dataAduanFileServerCount'];
                $data['avgResponseTimeFileServerHisFormat'] = Carbon::createFromTime(0, 0, 0)->addSeconds($data['dataResponseTimeInSecondFileServerAvg'])->format('H:i:s');
            }

            // Telkomsel
            foreach ($data['dataAduanTelkomsel'] as $telkomsel) {
                $data['dataResponseTimeInSecondTelkomsel'] = Carbon::parse($telkomsel->response_time)->secondsSinceMidnight();
                $data['countResponseTimeTelkomsel'] += $data['dataResponseTimeInSecondTelkomsel'];
            }

            if ($data['countResponseTimeTelkomsel'] != 0) {
                $data['dataResponseTimeInSecondTelkomselAvg'] = $data['countResponseTimeTelkomsel'] / $data['dataAduanTelkomselCount'];
                $data['avgResponseTimeTelkomselHisFormat'] = Carbon::createFromTime(0, 0, 0)->addSeconds($data['dataResponseTimeInSecondTelkomselAvg'])->format('H:i:s');
            }

            // Internet
            foreach ($data['dataAduanInternet'] as $internet) {
                $data['dataResponseTimeInSecondInternet'] = Carbon::parse($internet->response_time)->secondsSinceMidnight();
                $data['countResponseTimeInternet'] += $data['dataResponseTimeInSecondInternet'];
            }

            if ($data['countResponseTimeInternet'] != 0) {
                $data['dataResponseTimeInSecondInternetAvg'] = $data['countResponseTimeInternet'] / $data['dataAduanInternetCount'];
                $data['avgResponseTimeInternetHisFormat'] = Carbon::createFromTime(0, 0, 0)->addSeconds($data['dataResponseTimeInSecondInternetAvg'])->format('H:i:s');
            }

            // Network Mt
            foreach ($data['dataAduanNetworkMt'] as $networkMt) {
                $data['dataResponseTimeInSecondNetworkMt'] = Carbon::parse($networkMt->response_time)->secondsSinceMidnight();
                $data['countResponseTimeNetworkMt'] += $data['dataResponseTimeInSecondNetworkMt'];
            }

            if ($data['countResponseTimeNetworkMt'] != 0) {
                $data['dataResponseTimeInSecondNetworkMtAvg'] = $data['countResponseTimeNetworkMt'] / $data['dataAduanNetworkMtCount'];
                $data['avgResponseTimeNetworkMtHisFormat'] = Carbon::createFromTime(0, 0, 0)->addSeconds($data['dataResponseTimeInSecondNetworkMtAvg'])->format('H:i:s');
            }

            // Other
            foreach ($data['dataAduanOther'] as $other) {
                $data['dataResponseTimeInSecondOther'] = Carbon::parse($other->response_time)->secondsSinceMidnight();
                $data['countResponseTimeOther'] += $data['dataResponseTimeInSecondOther'];
            }

            if ($data['countResponseTimeOther'] != 0) {
                $data['dataResponseTimeInSecondOtherAvg'] = $data['countResponseTimeOther'] / $data['dataAduanOtherCount'];
                $data['avgResponseTimeOtherHisFormat'] = Carbon::createFromTime(0, 0, 0)->addSeconds($data['dataResponseTimeInSecondOtherAvg'])->format('H:i:s');
            }

            // Radio Ht
            foreach ($data['dataAduanRadioHt'] as $radioHt) {
                $data['dataResponseTimeInSecondRadioHt'] = Carbon::parse($radioHt->response_time)->secondsSinceMidnight();
                $data['countResponseTimeRadioHt'] += $data['dataResponseTimeInSecondRadioHt'];
            }

            if ($data['countResponseTimeRadioHt'] != 0) {
                $data['dataResponseTimeInSecondRadioHtAvg'] = $data['countResponseTimeRadioHt'] / $data['dataAduanRadioHtCount'];
                $data['avgResponseTimeRadioHtHisFormat'] = Carbon::createFromTime(0, 0, 0)->addSeconds($data['dataResponseTimeInSecondRadioHtAvg'])->format('H:i:s');
            }

            // Support
            foreach ($data['dataAduanSupport'] as $support) {
                $data['dataResponseTimeInSecondSupport'] = Carbon::parse($support->response_time)->secondsSinceMidnight();
                $data['countResponseTimeSupport'] += $data['dataResponseTimeInSecondSupport'];
            }

            if ($data['countResponseTimeSupport'] != 0) {
                $data['dataResponseTimeInSecondSupportAvg'] = $data['countResponseTimeSupport'] / $data['dataAduanSupportCount'];
                $data['avgResponseTimeSupportHisFormat'] = Carbon::createFromTime(0, 0, 0)->addSeconds($data['dataResponseTimeInSecondSupportAvg'])->format('H:i:s');
            }

            // Administrasi
            foreach ($data['dataAduanAdministrasi'] as $administrasi) {
                $data['dataResponseTimeInSecondAdministrasi'] = Carbon::parse($administrasi->response_time)->secondsSinceMidnight();
                $data['countResponseTimeAdministrasi'] += $data['dataResponseTimeInSecondAdministrasi'];
            }

            if ($data['countResponseTimeAdministrasi'] != 0) {
                $data['dataResponseTimeInSecondAdministrasiAvg'] = $data['countResponseTimeAdministrasi'] / $data['dataAduanAdministrasiCount'];
                $data['avgResponseTimeAdministrasiHisFormat'] = Carbon::createFromTime(0, 0, 0)->addSeconds($data['dataResponseTimeInSecondAdministrasiAvg'])->format('H:i:s');
            }

            // Gps
            foreach ($data['dataAduanGps'] as $gps) {
                $data['dataResponseTimeInSecondGps'] = Carbon::parse($gps->response_time)->secondsSinceMidnight();
                $data['countResponseTimeGps'] += $data['dataResponseTimeInSecondGps'];
            }

            if ($data['countResponseTimeGps'] != 0) {
                $data['dataResponseTimeInSecondGpsAvg'] = $data['countResponseTimeGps'] / $data['dataAduanGpsCount'];
                $data['avgResponseTimeGpsHisFormat'] = Carbon::createFromTime(0, 0, 0)->addSeconds($data['dataResponseTimeInSecondGpsAvg'])->format('H:i:s');
            }

            //  AVG Per Categoty

            return response()->json($data);
        } else {
            return response()->json('select range date or yaer kpi');
        }
        // $sortingDateStart = $request->start_date;
        // $sortingDateStartLength = strtotime($sortingDateStart);
        // $sortingDateEnd = $request->end_date;
        // $sortingDateEndLength = strtotime($sortingDateEnd);

        // $date_diff = $sortingDateEndLength - $sortingDateStartLength;
        // $count['daysLength'] = floor($date_diff / (60 * 60 * 24)) + 1;

        // $count['serverBreakdown'] = DB::table('perangkat_breakdowns')
        //     ->where('device_category', 'Server')
        //     ->where('created_date', '>=', $request->start_date)
        //     ->where('created_date', '<=', $request->end_date)
        //     ->count(DB::raw('DISTINCT inventory_number'));

        // $count['countInvServer'] = DB::table('inv_servers')
        //     ->count(DB::raw('DISTINCT server_name'));

        // return response()->json($count);
    }

    public function index()
    {
        $auth = auth()->user();

        return Inertia::render(
            'Inventory/Kpi/KpiResponseTime/KpiResponseTimeIndex',
            [
                // 'chartData' => [
                //     'labels' => [],
                //     'sudah_inspeksi' => [],
                //     'belum_inspeksi' => [],
                // ],
                'site' => $auth->site,
            ]
        );
    }

    public function countKpi(Request $request)
    {
        $auth = auth()->user();
        $site = $auth->site;
        $typeKategori = $site === 'HO' ? 'HO' : 'SITE';

        // Ambil semua kategori sesuai type (HO / SITE)
        $categories = DB::table('root_cause_categories')->where('site_type', $typeKategori)->get();

        // Query dasar aduan
        $query = DB::table('aduans')->where('site', $site)->whereNull('deleted_at');

        // Filter waktu
        if ($request->year) {
            $query->whereYear('created_date', $request->year);
        } elseif ($request->startDate && $request->endDate) {
            $query->whereBetween('created_date', [$request->startDate, $request->endDate]);
        }

        $baseQuery = clone $query;

        // KPI umum
        $data['countAduan'] = $baseQuery->count();
        $data['countAduanClosed'] = (clone $query)->where('status', 'CLOSED')->count();
        $data['countAduanOpen'] = (clone $query)->where('status', 'OPEN')->count();
        $data['countAduanProcess'] = (clone $query)->where('status', 'PROCESS')->count();
        $data['countAduanOutstanding'] = (clone $query)->where('status', 'OUTSTANDING')->count();

        // Hitung response time total
        $responseTimeData = (clone $query)
            ->whereNotNull('response_time')
            ->selectRaw('
            COUNT(*) as total_data,
            SUM(TIME_TO_SEC(response_time)) as total_seconds,
            AVG(TIME_TO_SEC(response_time)) as avg_seconds,
            SUM(CASE WHEN TIME_TO_SEC(response_time) <= 1800 THEN 1 ELSE 0 END) as achievement_count
        ')
            ->first();

        // Format rata-rata ke HH:MM:SS
        $avgInSeconds = $responseTimeData->avg_seconds ?? 0;
        $data['avgResponseTime'] = gmdate('H:i:s', (int) $avgInSeconds);

        // Hitung achievement 30 menit (%)
        $data['achievement30minPercent'] = $avgInSeconds > 0
            ? round((1800 / $avgInSeconds) * 100, 2)
            : 0;

        // Hitung response time rata-rata per kategori
        $kategoriList = $categories->map(function ($category) use ($query) {
            $avgSeconds = (clone $query)
                ->where('category_name', $category->category_root_cause)
                ->whereNotNull('response_time')
                ->avg(DB::raw('TIME_TO_SEC(response_time)'));

            return [
                'kategori' => strtoupper($category->category_root_cause),
                'time' => $avgSeconds ? gmdate("H:i:s", (int) $avgSeconds) : '00:00:00',
            ];
        });

        $data['kategoriList'] = $kategoriList;
        $data['aduanDiAtas30Menit'] = (clone $query)
            ->whereNotNull('response_time')
            ->whereRaw('TIME_TO_SEC(response_time) > 1800')
            ->get();

        return response()->json($data);
    }
}
