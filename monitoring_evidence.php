<?php

/*
 * Evidence harness for Monitoring Jobs web-vs-mobile parity.
 * Run: php artisan tinker monitoring_evidence.php
 *
 * It (1) seeds a deterministic BIB dataset that exercises every known
 * divergence axis, then (2) runs the WEB query logic (copied verbatim from
 * DailyJobMonitorController@index) and the MOBILE query logic (copied verbatim
 * from OperationsApiController::monitoring*Query) against the SAME rows and
 * prints first-10 of each so the mismatch is provable.
 */

use App\Models\DailyJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

$SITE = 'BIB';
$today = Carbon::today();              // 2026-06-13
$todayStr = $today->toDateString();
$oldStr = $today->copy()->subDays(12)->toDateString();   // old date
$futureDue = $today->copy()->addDays(7)->toDateString();

// --- reset only our seeded test rows -------------------------------------
DailyJob::where('site', $SITE)->where('code', 'like', 'EV%')->forceDelete();

function seed(array $o): void {
    DB::table('daily_jobs')->insert(array_merge([
        'crew' => '[]',
        'category' => '-',
        'created_by' => 4,
        'approval_status' => null,
    ], $o));
}

// R1: old open assignment, due in future -> WEB shows (open+due future), MOBILE hides (updated_at != today)
seed(['code'=>'EV01','category_job'=>'assignment','description'=>'R1 old-open-future-due','site'=>'BIB','shift'=>'SHIFT_1','date'=>$oldStr,'due_date'=>$futureDue,'status'=>'open','created_at'=>$oldStr.' 08:00:00','updated_at'=>$oldStr.' 08:00:00']);
// R2: today open assignment -> BOTH show
seed(['code'=>'EV02','category_job'=>'assignment','description'=>'R2 today-open','site'=>'BIB','shift'=>'SHIFT_1','date'=>$todayStr,'due_date'=>null,'status'=>'open','created_at'=>$todayStr.' 09:00:00','updated_at'=>$todayStr.' 09:00:00']);
// R3: today support, approved, shift2 -> WEB shows (!=unschedule), MOBILE shows (IN assignment,support)
seed(['code'=>'EV03','category_job'=>'support','description'=>'R3 today-support-approved','site'=>'BIB','shift'=>'SHIFT_2','date'=>$todayStr,'status'=>'open','approval_status'=>'approved','created_at'=>$todayStr.' 20:00:00','updated_at'=>$todayStr.' 20:00:00']);
// R4: closed assignment but updated today -> WEB hides (status=closed), MOBILE shows (updated today)
seed(['code'=>'EV04','category_job'=>'assignment','description'=>'R4 closed-updated-today','site'=>'BIB','shift'=>'SHIFT_1','date'=>$oldStr,'status'=>'closed','created_at'=>$oldStr.' 08:00:00','updated_at'=>$todayStr.' 10:00:00']);
// R6: unschedule today -> BOTH unscheduled sets
seed(['code'=>'EV06','category_job'=>'unschedule','description'=>'R6 unschedule-today','site'=>'BIB','shift'=>'SHIFT_1','date'=>$todayStr,'status'=>'open','created_at'=>$todayStr.' 11:00:00','updated_at'=>$todayStr.' 11:00:00']);
// R7: unschedule old date but updated today -> WEB unsched hides (date!=today), MOBILE shows (updated today)
seed(['code'=>'EV07','category_job'=>'unschedule','description'=>'R7 unschedule-old-updated-today','site'=>'BIB','shift'=>'SHIFT_1','date'=>$oldStr,'status'=>'open','created_at'=>$oldStr.' 08:00:00','updated_at'=>$todayStr.' 12:00:00']);

echo 'Seeded BIB rows: '.DailyJob::where('site','BIB')->where('code','like','EV%')->count().PHP_EOL;

// =========================================================================
// Operational date/shift (verbatim from web index(), no start/shift filter)
// =========================================================================
$now = now();
if ($now->hour >= 6 && $now->hour < 18) { $operationalDate = $now->toDateString(); $operationalShift = 'SHIFT_1'; }
else { $operationalShift = 'SHIFT_2'; $operationalDate = $now->hour < 6 ? $now->copy()->subDay()->toDateString() : $now->toDateString(); }
echo "operationalDate=$operationalDate operationalShift=$operationalShift now=".$now->toDateTimeString().PHP_EOL;

$startDate = null; $endDate = null; $shift = null; $status = null; $site = $SITE;

// ---- WEB scheduled (verbatim) -------------------------------------------
$webScheduled = DailyJob::query()
    ->when($startDate && $endDate, fn($q)=>$q->whereBetween('date',[$startDate,$endDate]),
        fn($q)=>$q->where('status','!=','closed')->where(fn($w)=>$w->whereNull('due_date')->orWhereDate('due_date','>=',$today)))
    ->when($shift, fn($q)=>$q->where('shift',$shift))
    ->when($status, fn($q)=>$q->where('status',$status))
    ->where('category_job','!=','unschedule')
    ->when($site, fn($q)=>$q->where('site',$site))
    ->orderBy('date','desc')->get();

// ---- MOBILE scheduled — call the REAL shipping controller method ---------
$controller = new \App\Http\Controllers\Api\OperationsApiController();
$ref = new \ReflectionClass($controller);
$callPrivate = function(string $method, $req, $shiftArg) use ($controller,$ref,$site){
    $m = $ref->getMethod($method); $m->setAccessible(true);
    return $m->invoke($controller, $req, $site, $shiftArg);
};
$req = \Illuminate\Http\Request::create('/api/operations/monitoring-jobs','GET',['site'=>$site]);
$mobScheduled = $callPrivate('monitoringScheduledQuery', $req, $shift)->get();

// ---- WEB unscheduled (verbatim) -----------------------------------------
$webUnsched = DailyJob::query()
    ->where('category_job','unschedule')
    ->when($startDate && $endDate, fn($q)=>$q->whereBetween('date',[$startDate,$endDate]),
        function($q) use ($operationalDate,$operationalShift,$shift){ $q->whereDate('date',$operationalDate); if(!$shift){$q->where('shift',$operationalShift);} })
    ->when($shift, fn($q)=>$q->where('shift',$shift))
    ->when($status, fn($q)=>$q->where('status',$status))
    ->when($site, fn($q)=>$q->where('site',$site))
    ->orderBy('date','desc')->get();

// ---- MOBILE unscheduled — call the REAL shipping controller method -------
$mobUnsched = $callPrivate('monitoringUnscheduledQuery', $req, $shift)->get();

$fmt = function($c){
    return $c->take(10)->map(fn($j)=>sprintf('%-5s %-11s %-9s %-8s date=%s upd=%s appr=%s',
        $j->code,$j->category_job,$j->status,$j->shift,$j->date instanceof \Carbon\Carbon?$j->date->toDateString():$j->date,
        Carbon::parse($j->updated_at)->toDateString(),$j->approval_status??'NULL'))->implode(PHP_EOL);
};

echo PHP_EOL.'=== SCHEDULED (Job Assignment) ==='.PHP_EOL;
echo '--- WEB ('.$webScheduled->count().') ---'.PHP_EOL.$fmt($webScheduled).PHP_EOL;
echo '--- MOBILE ('.$mobScheduled->count().') ---'.PHP_EOL.$fmt($mobScheduled).PHP_EOL;
echo 'WEB codes:    '.$webScheduled->pluck('code')->implode(',').PHP_EOL;
echo 'MOBILE codes: '.$mobScheduled->pluck('code')->implode(',').PHP_EOL;

echo PHP_EOL.'=== UNSCHEDULED (Job Un-Schedule) ==='.PHP_EOL;
echo '--- WEB ('.$webUnsched->count().') ---'.PHP_EOL.$fmt($webUnsched).PHP_EOL;
echo '--- MOBILE ('.$mobUnsched->count().') ---'.PHP_EOL.$fmt($mobUnsched).PHP_EOL;
echo 'WEB codes:    '.$webUnsched->pluck('code')->implode(',').PHP_EOL;
echo 'MOBILE codes: '.$mobUnsched->pluck('code')->implode(',').PHP_EOL;

echo PHP_EOL.'MATCH scheduled: '.($webScheduled->pluck('code')->values()->all()===$mobScheduled->pluck('code')->values()->all()?'YES':'NO').PHP_EOL;
echo 'MATCH unscheduled: '.($webUnsched->pluck('code')->values()->all()===$mobUnsched->pluck('code')->values()->all()?'YES':'NO').PHP_EOL;

// =========================================================================
// METADATA + APPROVE FLIP (canApprove / allApproved / primary_action)
// =========================================================================
$callApproved = function($req,$shiftArg) use ($controller,$ref,$site){
    $m=$ref->getMethod('allApprovedToday'); $m->setAccessible(true);
    return $m->invoke($controller,$req,$site,$shiftArg);
};
echo PHP_EOL.'=== BUTTON METADATA (web parity) ==='.PHP_EOL;
$canApprove = in_array('ict_group_leader', ['ict_group_leader','ict_developer'], true);
$allApproved = $callApproved($req, $shift);
$primary = $allApproved ? 'export' : ($canApprove ? 'approve' : 'none');
echo "BEFORE approve -> can_approve=".($canApprove?'true':'false')." all_approved=".($allApproved?'true':'false')." primary_action=$primary".PHP_EOL;

// Simulate web/mobile approveAll: approve all of today's NULL jobs for site.
DailyJob::whereDate('date', $today)->where('site',$site)->whereNull('approval_status')
    ->update(['approval_status'=>'approved','updated_by'=>4,'updated_at'=>now()]);

$allApproved2 = $callApproved($req, $shift);
$primary2 = $allApproved2 ? 'export' : ($canApprove ? 'approve' : 'none');
echo "AFTER approve  -> can_approve=".($canApprove?'true':'false')." all_approved=".($allApproved2?'true':'false')." primary_action=$primary2".PHP_EOL;
