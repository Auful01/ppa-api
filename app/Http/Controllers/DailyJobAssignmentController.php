<?php

namespace App\Http\Controllers;

use App\Models\DailyJobAssignment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DailyJobAssignmentController extends Controller
{
    public function index()
    {
        $dailyJobAssignment = DailyJobAssignment::all();
        return response()->json($dailyJobAssignment);
    }

    public function store(Request $request)
    {
        // start generate code
        $currentDate = Carbon::now();
        $year = $currentDate->format('Y');
        $month = $currentDate->month;
        $day = $currentDate->day;

        $maxId = DailyJobAssignment::max('id');

        if (is_null($maxId)) {
            $maxId = 0;
        }

        $uniqueString = 'PPA/DJA/' . $year . '/' . str_pad(($maxId % 10000) + 1, 2, '0', STR_PAD_LEFT);
        // end generate code

        $crew_part = implode(', ', $request->crew);
        $data = [
            'assignment_code' => $uniqueString,
            'job_assignment' => $request->job_assignment,
            'remark' => $request->remark,
            'job' => $request->job,
            'crew_partner' => $crew_part,
            'sarana' => $request->sarana,
            'due_date' => $request->due_date,
            'status' => $request->status,
            'action' => $request->action,
            'shift' => $request->shift,
            'job_category' => $request->job_category,
            // 'pica_number' => $uniqueString,
            // 'created_date' => Carbon::now()->format('Y-m-d'),
        ];

        if (empty($request->id)) {
            DailyJobAssignment::create($data);
        } else {
            DailyJobAssignment::where('id', $request->id)->update($data);
        }
        return response()->json($data, 201);
    }

    public function showDataGlPage(Request $request)
    {
        $getDataDailyJob = DailyJobAssignment::where('status', '!=', 'CLOSED')->get();
        return response()->json($getDataDailyJob);
    }

    public function showDataGlPageSortingDate(Request $request)
    {
        $start_date = Carbon::parse($request->start_date)->startOfDay();
        $end_date = Carbon::parse($request->end_date)->endOfDay();

        $results =  DailyJobAssignment::whereBetween('created_at', [$start_date, $end_date])->get();
        return response()->json($results);
    }

    public function showDataTechnicianPage(Request $request)
    {
        $name = Auth::user()->name;
        $results = DailyJobAssignment::where('crew_partner', 'LIKE', "%{$name}%")->where('status', '!=', 'CLOSED')->get();
        return response()->json($results);
    }

    public function closingJobAssigmentTechnician(Request $request)
    {
        $getData = DailyJobAssignment::where('id', $request->id)->first();

        if (!empty($request->remark)) {
            $remark_technician = ', from technician: ' . $request->remark . ', ';
        } else {
            $remark_technician = $request->remark;
        }

        $crew_part = implode(', ', $request->crew);
        $data = [
            'status' => $request->status,
            'crew_partner' => $crew_part,
            'remark' => $getData->remark . $remark_technician,
        ];

        if ($request->status = 'CLOSED') {
            $data_unschedule = [
                'assignment_id' => $request->id,
                'category_report' => 'Assignment',
                'start_progress' => $request->start_progress,
                'end_progress' => $request->end_progress,
                'issue' => $request->job_assignment,
                'category' => $request->category,
                'root_cause' => $request->root_cause,
                'action' => $request->action,
                'crew' => $crew_part,
                'remark' => $request->remark,
                'status' => $request->status,
            ];

            $dataStore['store_unschedule'] = DB::table('unschedule_jobs')->insert($data_unschedule);
        }

        $dataStore['closing_job_assignment'] = DailyJobAssignment::where('id', $request->id)->update($data);

        return response()->json($dataStore);
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

    public function destroy($id)
    {
        $dailyJobAssignment = DailyJobAssignment::find($id);
        if (is_null($dailyJobAssignment)) {
            return response()->json(['message' => 'Data not found'], 404);
        }
        $dailyJobAssignment->delete();
        return response()->json(['message' => 'Data has deleted'], 204);
    }
}
