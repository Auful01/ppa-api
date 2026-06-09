<?php

namespace App\Http\Controllers;

use App\Models\Aduan;
use App\Models\User;
use App\Models\UserAll;
use Carbon\Carbon;
use Dedoc\Scramble\Scramble;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use League\Csv\Reader;
use Maatwebsite\Excel\Facades\Excel;

class AduanHoController extends Controller
{
    public function index()
    {
        $aduan = Aduan::orderBy('date_of_complaint', 'desc')->where('nrp', auth()->user()->nrp)->where('site', 'HO')->where('site_pelapor', auth()->user()->site)->get();
        $countOpen = Aduan::where('status', 'OPEN')->where('site', 'HO')->where('site_pelapor', auth()->user()->site)->count();
        $countClosed = Aduan::where('status', 'CLOSED')->where('site', 'HO')->where('site_pelapor', auth()->user()->site)->count();
        $countProgress = Aduan::where('status', 'PROGRESS')->where('site', 'HO')->where('site_pelapor', auth()->user()->site)->count();
        $countCancel = Aduan::where('status', 'CANCEL')->where('site', 'HO')->where('site_pelapor', auth()->user()->site)->count();
        return Inertia::render(
            'Aduan/AduanHo',
            [
                'aduan' => $aduan,
                'open' => $countOpen,
                'closed' => $countClosed,
                'progress' => $countProgress,
                'cancel' => $countCancel,
            ]
        );
    }
    
    public function create()
    {
        $site = Auth::user()->site;
        $categories = DB::table('root_cause_categories')
            ->select('id', 'category_root_cause')
            ->where('site_type', 'HO')
            ->get();

        // start generate code
        $currentDate = Carbon::now();
        $year = $currentDate->format('y');
        $month = $currentDate->month;
        $day = $currentDate->day;

        $maxId = Aduan::whereDate('created_at', $currentDate->format('Y-m-d'))->where('site', 'HO')->orderBy('max_id', 'desc')->first();

        if (is_null($maxId)) {
            $maxId = 0;
        } else {
            $split = explode('-', $maxId->complaint_code);
            $noUrut = (int) $split[2];
            $maxId = $noUrut;
        }

        $uniqueString = 'ADUAN-' . $year . $month . $day . '-' . str_pad(($maxId % 10000) + 1, 2, '0', STR_PAD_LEFT);
        $request['ticket'] = $uniqueString;
        // return response()->json($crew);
        // end generate code

        $nrp = auth()->user()->nrp;
        $nama = auth()->user()->name;

        return Inertia::render('Aduan/AduanCreateHo', ['ticket' => $uniqueString, 'nrp' => $nrp, 'nama' => $nama, 'categories' => $categories]);
    }

    public function store(Request $request)
    {
        $currentDate = now()->utc()->toDateTimeString(); // Format: "Y-m-d H:i:s"

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
            'complaint_name' => $request['complaint_name'],
            'complaint_note' => $request['complaint_note'],
            'phone_number' => $request['phone_number'],
            'date_of_complaint' => $currentDate,
            'location' => $request['location'],
            'detail_location' => $request['location_detail'],
            'category_name' => $request['category_name'],
            'site' => 'HO',
            'site_pelapor' => auth()->user()->site
        ];

        if ($request->file('image') != null) {
            $documentation_image = $request->file('image');
            $destinationPath = 'images/';
            $path_documentation_image = $documentation_image->store('images', 'public');
            $new_path_documentation_image = $path_documentation_image;
            $documentation_image->move($destinationPath, $new_path_documentation_image);

            $data['complaint_image'] =  url($new_path_documentation_image);
        }

        $data['complaint_code'] = $request->complaint_code;
        $data['created_date'] = Carbon::parse($request->date_of_complaint)->toDateString();

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
        return redirect()->route('aduan-ho.page');
    }

    public function show($id)
    {
        $aduan = Aduan::find($id);
        if (empty($aduan)) {
            abort(404, 'Data not found');
        }
        if (is_null($aduan)) {
            return response()->json(['message' => 'Aduan Data not found'], 404);
        }
        return response()->json($aduan);
    }

    public function detail($id)
    {
        $aduan = Aduan::where('id', $id)->first();
        if (empty($aduan)) {
            abort(404, 'Data not found');
        }
        return Inertia::render('Aduan/AduanDetailHo', [
            'aduan' => $aduan,
        ]);
    }

    public function destroy($id)
    {
        $aduans = Aduan::findOrFail($id);
        $aduans->delete();
        return redirect()->back();
    }
}
