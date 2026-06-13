<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aduan;
use App\Support\Api\SiteContext;
use Carbon\Carbon;
use Illuminate\Http\Request;

class KpiResponseTimeApiController extends Controller
{
    private const THRESHOLD_SECONDS = 1800; // 30 minutes

    public function index(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date'],
            'site'       => ['nullable', 'string'],
        ]);

        $site = SiteContext::resolve($request);

        $now = Carbon::now();
        $startDate = isset($validated['start_date'])
            ? Carbon::parse($validated['start_date'])->startOfDay()
            : $now->copy()->startOfMonth();
        $endDate = isset($validated['end_date'])
            ? Carbon::parse($validated['end_date'])->endOfDay()
            : $now->copy()->endOfDay();

        // SOURCE OF TRUTH: KpiResponseTimeController::countKpi filters on the
        // `created_date` column (date the complaint was logged), not the
        // datetime `date_of_complaint`. Mirror it for identical row selection.
        $baseQuery = Aduan::query()
            ->whereBetween('created_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('site', $site)
            ->whereNull('deleted_at');

        $totalAduan   = (clone $baseQuery)->count();
        $countClosed  = (clone $baseQuery)->where('status', 'closed')->count();
        $countOpen    = (clone $baseQuery)->where('status', 'open')->count();
        $countProgress = (clone $baseQuery)->where('status', 'progress')->count();
        $countOutstanding = (clone $baseQuery)->where('status', 'outstanding')->count();
        $countCancel  = (clone $baseQuery)->where('status', 'cancel')->count();

        // Complaint-category breakdown for the "Analisis Aduan" donut. Mirrors
        // the web Dashboard "Analisis Aduan" chart: count complaints per
        // category over the SAME period + site (the donut centre "Total Aduan"
        // is the overall complaint count). Categories are dynamic — grouped by
        // category_name — so any site's categories render without a hardcoded
        // list. NULL/blank categories collapse into OTHER.
        $categories = (clone $baseQuery)
            ->selectRaw('UPPER(COALESCE(NULLIF(TRIM(category_name), ""), "OTHER")) as category, COUNT(*) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'category' => $row->category,
                'count'    => (int) $row->total,
            ])
            ->all();

        // Tickets that have a response recorded
        $respondedTickets = (clone $baseQuery)
            ->whereNotNull('start_response')
            ->get([
                'id', 'complaint_code', 'complaint_note', 'complaint_name',
                'date_of_complaint', 'start_response', 'response_time',
                'end_progress', 'location', 'category_name', 'crew',
                'root_cause_id', 'action_repair', 'status',
            ]);

        $totalResponseSeconds = 0;
        $respondedCount = 0;
        $slowTickets = [];

        foreach ($respondedTickets as $ticket) {
            $seconds = $this->resolveResponseSeconds($ticket);
            if ($seconds === null) {
                continue;
            }
            $totalResponseSeconds += $seconds;
            $respondedCount++;

            if ($seconds >= self::THRESHOLD_SECONDS) {
                $slowTickets[] = [
                    'code'            => $ticket->complaint_code,
                    'issue'           => $ticket->complaint_note,
                    'date_of_complaint' => $ticket->date_of_complaint,
                    'start_response'  => $ticket->start_response,
                    'response_time'   => $ticket->response_time,
                    'end_progress'    => $ticket->end_progress,
                    'name'            => $ticket->complaint_name,
                    'location'        => $ticket->location,
                    'category'        => $ticket->category_name,
                    'crew'            => $ticket->crew,
                    'action_repair'   => $ticket->action_repair,
                    'status'          => $ticket->status,
                    'response_seconds' => $seconds,
                ];
            }
        }

        $avgSeconds = $respondedCount > 0 ? ($totalResponseSeconds / $respondedCount) : 0;
        $avgFormatted = Carbon::createFromTime(0, 0, 0)->addSeconds((int) $avgSeconds)->format('H:i:s');
        // SOURCE OF TRUTH: web KpiResponseTimeController::countKpi computes
        // achievement as round((1800 / avg) * 100, 2) WITHOUT capping at 100.
        // A fast average legitimately yields > 100% — do not clamp it.
        $achievement = $avgSeconds > 0 ? round((self::THRESHOLD_SECONDS / $avgSeconds) * 100, 2) : 0;

        return response()->json([
            'data' => [
                'summary' => [
                    'total_aduan'     => $totalAduan,
                    'closed'          => $countClosed,
                    'open'            => $countOpen,
                    'progress'        => $countProgress,
                    'outstanding'     => $countOutstanding,
                    'cancel'          => $countCancel,
                    'responded'       => $respondedCount,
                    'avg_response'    => $avgFormatted,
                    'achievement'     => $achievement,
                ],
                'categories' => $categories,
                'tickets' => $slowTickets,
            ],
            'meta' => [
                'site'              => $site,
                'start_date'        => $startDate->toDateString(),
                'end_date'          => $endDate->toDateString(),
                'threshold_seconds' => self::THRESHOLD_SECONDS,
            ],
        ]);
    }

    private function resolveResponseSeconds(Aduan $ticket): ?int
    {
        // Prefer stored response_time field (HH:mm:ss duration string)
        if (! empty($ticket->response_time)) {
            try {
                $parts = explode(':', $ticket->response_time);
                if (count($parts) === 3) {
                    return ((int) $parts[0] * 3600) + ((int) $parts[1] * 60) + (int) $parts[2];
                }
            } catch (\Throwable) {
            }
        }

        // Fall back: compute from date_of_complaint → start_response
        if (! empty($ticket->start_response) && ! empty($ticket->date_of_complaint)) {
            try {
                $responded = Carbon::parse($ticket->start_response);
                $complained = Carbon::parse($ticket->date_of_complaint);
                $diff = $complained->diffInSeconds($responded);
                return $diff >= 0 ? (int) $diff : null;
            } catch (\Throwable) {
            }
        }

        return null;
    }

    private function resolveSite(Request $request): string
    {
        return SiteContext::resolve($request);
    }
}
