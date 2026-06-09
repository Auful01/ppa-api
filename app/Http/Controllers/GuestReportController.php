<?php

namespace App\Http\Controllers;

use App\Models\Aduan;
use App\Models\InvCctv;
use App\Models\InvComputer;
use App\Models\InvLaptop;
use App\Models\InvPrinter;
use App\Models\PerangkatBreakdown;
use App\Models\RootCauseProblem;
use App\Models\User;
use App\Models\UserAll;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class GuestReportController extends Controller
{
    public function unratedClosed(Request $request)
    {
        return Aduan::where('nrp', auth()->user()->nrp)
            ->where('status', 'CLOSED')
            ->whereNull('user_rating')
            ->get();
    }

    public function index(Request $request)
    {
        if (empty($request->search) || $request->search == null) {
            $search = 'errordata';
        } else {
            $search = $request->search;
        }

        // return dd($search);
        $aduan = Aduan::query()
            ->when(!$request->search, function ($query) {
                return $query->where('nrp', auth()->user()->nrp)
                    ->orderBy('date_of_complaint', 'desc');
            })
            ->when($request->search, function ($query, $search) {
                return $query->where('complaint_code', 'like', '%' . $search . '%');
            })
            ->get();

        // $aduan = Aduan::whereDate('created_date', Carbon::today())->orderBy('date_of_complaint', 'desc')->get();
        return Inertia::render(
            'Guest/GuestAduan',
            [
                'aduan' => $aduan,
            ]
        );
    }

    public function create()
    {
        $site = Auth::user()->site;
        if ($site != 'HO') {
            $categories = DB::table('root_cause_categories')
                ->select('id', 'category_root_cause')
                ->where('site_type', 'SITE')
                ->get();
        } else {
            $categories = DB::table('root_cause_categories')
                ->select('id', 'category_root_cause')
                ->where('site_type', 'HO')
                ->get();
        }
        // return dd($categories);

        // start generate code
        $currentDate = Carbon::now();
        $year = $currentDate->format('y');
        $month = $currentDate->month;
        $day = $currentDate->day;

        $maxId = Aduan::whereDate('created_at', $currentDate->format('Y-m-d'))->where('site', auth()->user()->site)->orderBy('max_id', 'desc')->first();

        if (is_null($maxId)) {
            $maxId = 0;
        } else {
            $split = explode('-', $maxId->complaint_code);
            $noUrut = (int) $split[2];
            $maxId = $noUrut;
        }

        $uniqueString = 'ADUAN-' . $year . $month . $day . '-' . str_pad(($maxId % 10000) + 1, 2, '0', STR_PAD_LEFT);
        $request['ticket'] = $uniqueString;

        $complaintDataNrp = UserAll::select('nrp', 'username')->get()->toArray();

        // return response()->json($crew);
        // end generate code
        return Inertia::render('Guest/GuestAduanCreate', ['ticket' => $uniqueString, 'complaintDataNrp' => $complaintDataNrp, 'categories' => $categories]);
    }

    public function store(Request $request)
    {
        // dd($request);
        $maxId = Aduan::max('max_id');
        if (is_null($maxId)) {
            $maxId = 1;
        } else {
            $maxId = $maxId + 1;
        }

        $data = [
            'max_id' => $maxId,
            'nrp' => $request['nrp'],
            'complaint_note' => $request['complaint_note'],
            'phone_number' => $request['phone_number'],
            'date_of_complaint' => Carbon::now('Asia/Ujung_Pandang')->format('Y-m-d H:i:s'),
            'location' => $request['location'],
            'urgency' => 'NORMAL',
            'detail_location' => $request['location_detail'],
            'category_name' => $request['category_name'],
            'site' => auth()->user()->site,
            'site_pelapor' => auth()->user()->site
        ];

        if (empty($request['complaint_name'])) {
            $data['complaint_name'] = 'User Belum Terdaftar Pada Sistem (NRP Not Detect!)';
        } else {
            $data['complaint_name'] = $request['complaint_name'];
        }

        if ($request->file('image') != null) {
            $documentation_image = $request->file('image');
            $destinationPath = 'images/';
            $path_documentation_image = $documentation_image->store('images', 'public');
            $new_path_documentation_image = $path_documentation_image;
            $documentation_image->move($destinationPath, $new_path_documentation_image);

            $data['complaint_image'] =  url($new_path_documentation_image);
        }

        $data['complaint_code'] = $request->complaint_code;
        $data['created_date'] = Carbon::now()->format('Y-m-d');

        if (!empty($request->inventory_number)) {
            $aduan_get_data_user = UserAll::where('nrp', $request->nrp)->first();
            if (!empty($aduan_get_data_user)) {
                $data['complaint_position'] = $aduan_get_data_user['position'];
            } else {
                $data['complaint_position'] = 'User Belum Terdaftar Pada Sistem (NRP Not Detect!)';
            }
            $data['inventory_number'] = $request->inventory_number;
            $data['status'] = 'OPEN';
            // return response()->json($data);
            // return dd($data);
            $aduan = Aduan::create($data);
        } else {
            $aduan_get_data_user = UserAll::where('nrp', $request->nrp)->first();
            if (!empty($aduan_get_data_user)) {
                $data['complaint_position'] = $aduan_get_data_user['position'];
            } else {
                $data['complaint_position'] = 'User Belum Terdaftar Pada Sistem (NRP Not Detect!)';
            }
            $data['status'] = 'OPEN';

            $aduan = Aduan::create($data);
        }
        // return dd($data);
        // // --- Jika inventory_number ada, insert juga ke perangkat_breakdown ---
        // if (!empty($request->inventory_number)) {
        //     $deviceName = 'Unknown Device';
        //     $categoryInput = strtolower($request->category_name);
        //     $invNumber = strtoupper($request->inventory_number); // biar aman case insensitive

        //     if ($categoryInput === 'pc/nb') {
        //         // Cek dari inventory_number
        //         if (str_contains($invNumber, '-NB-')) {
        //             $inv = InvLaptop::where('laptop_code', $request->inventory_number)->first();
        //             $deviceName = $inv ? $inv->laptop_name : $deviceName;
        //         } elseif (str_contains($invNumber, '-PC-')) {
        //             $inv = InvComputer::where('computer_code', $request->inventory_number)->first();
        //             $deviceName = $inv ? $inv->computer_name : $deviceName;
        //         }
        //     } elseif ($categoryInput === 'printer') {
        //         $inv = InvPrinter::where('printer_code', $request->inventory_number)->first();
        //         $deviceName = $inv ? $inv->printer_brand : $deviceName;
        //     } elseif ($categoryInput === 'cctv') {
        //         $inv = InvCctv::where('cctv_code', $request->inventory_number)->first();
        //         $deviceName = $inv ? $inv->cctv_brand : $deviceName;
        //     }

        //     // Waktu sekarang
        //     $now = Carbon::now();

        //     // dd([
        //     //     'inventory_number' => $request->inventory_number,
        //     //     'id_report'        => $request->complaint_code,
        //     //     'device_category'  => $request->category_name,
        //     //     'device_name'      => $deviceName,
        //     //     'pic'              => $request->crew,
        //     //     'start_time'       => $request->date_of_complaint,
        //     //     'created_date'     => $now->toDateString(),
        //     //     'month'            => $now->month,
        //     //     'year'             => $now->year,
        //     //     'location'         => $request->location,
        //     //     'status'           => 'OPEN',
        //     //     'site'             => 'BA',
        //     // ]);

        //     PerangkatBreakdown::create([
        //         'inventory_number' => $request->inventory_number,
        //         'id_report'        => $request->complaint_code,
        //         'device_category'  => $request->category_name,
        //         'device_name'      => $deviceName,
        //         'pic'              => $request->crew,
        //         'start_time'       => $now,                  // datetime sekarang
        //         'created_date'     => $now->toDateString(),  // hanya tanggal
        //         'month'            => $now->month,           // angka 1-12
        //         'year'             => $now->year,            // angka tahun
        //         'location'         => $request->location,
        //         'status'           => 'OPEN',
        //         'site'             => Auth::user()->site,
        //     ]);
        // }

        return redirect()->route('guestAduan.page');
    }

    public function storeRating(Request $request)
    {
        $aduan = Aduan::findOrFail($request->complaint_id);
        $aduan->update(['user_rating' => $request->rating]);
    }

    public function destroy($id)
    {
        $aduan = Aduan::find($id);
        // return response()->json(['ap' => $aduan]);
        $aduan->delete();
        return redirect()->back();
    }
}
