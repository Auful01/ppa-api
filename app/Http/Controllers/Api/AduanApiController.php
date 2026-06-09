<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aduan;
use App\Models\InvCctv;
use App\Models\InvComputer;
use App\Models\InvLaptop;
use App\Models\InvPrinter;
use App\Models\PerangkatBreakdown;
use App\Models\RootCauseProblem;
use App\Models\User;
use App\Models\UserAll;
use App\Support\Api\SiteContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AduanApiController extends Controller
{
    public function index(Request $request)
    {
        $site = SiteContext::resolve($request);
        $query = Aduan::query()->with('rootCause')->whereNull('deleted_at');

        if ($site) {
            SiteContext::apply($query, 'site', $site);
        }

        if ($request->filled('status')) {
            $query->where('status', strtoupper((string) $request->string('status')));
        }

        if ($request->filled('category_name')) {
            $query->where('category_name', $request->string('category_name'));
        }

        $aduan = $query
            ->orderByRaw("
                CASE
                    WHEN urgency = 'URGENT' AND status IN ('OPEN', 'PROGRESS', 'CLOSED') THEN 0
                    ELSE 1
                END
            ")
            ->orderByDesc('date_of_complaint')
            ->paginate((int) $request->integer('per_page', 25));

        $statsQuery = Aduan::query()->whereNull('deleted_at');
        if ($site) {
            SiteContext::apply($statsQuery, 'site', $site);
        }

        return response()->json([
            'data' => $aduan,
            'meta' => [
                'site' => $site,
                'summary' => [
                    'open' => (clone $statsQuery)->where('status', 'OPEN')->count(),
                    'closed' => (clone $statsQuery)->where('status', 'CLOSED')->count(),
                    'progress' => (clone $statsQuery)->where('status', 'PROGRESS')->count(),
                    'cancel' => (clone $statsQuery)->where('status', 'CANCEL')->count(),
                ],
            ],
        ]);
    }

    public function meta(Request $request)
    {
        $site = SiteContext::resolve($request);
        $siteType = SiteContext::isHo($site) ? 'HO' : 'SITE';

        $categories = DB::table('root_cause_categories')
            ->select('id', 'category_root_cause')
            ->where('site_type', $siteType)
            ->get();

        $crewQuery = User::query()->select('id', 'name', 'role', 'site');
        if (SiteContext::isHo($site)) {
            $crewQuery->where('site', 'HO');
        } else {
            $crewQuery->where('site', $site);
        }

        return response()->json([
            'site' => $site,
            'ticket' => $this->generateTicket($site),
            'categories' => $categories,
            'crew' => $crewQuery->orderBy('name')->get(),
        ]);
    }

    public function show(Request $request, string $id)
    {
        $aduan = $this->authorizedAduanQuery($request)->with('rootCause')->findOrFail($id);
        $rootCause = RootCauseProblem::where('kategori_name', $aduan->category_name)
            ->get(['id_cause as id', 'root_cause_problem as name']);

        return response()->json([
            'data' => $aduan,
            'root_cause_options' => $rootCause,
        ]);
    }

    public function store(Request $request)
    {
        SiteContext::authorizeWrite($request, SiteContext::resolve($request));

        $validated = $request->validate([
            'nrp' => ['required', 'string'],
            'complaint_name' => ['required', 'string'],
            'complaint_note' => ['required', 'string'],
            'phone_number' => ['nullable', 'string'],
            'date_of_complaint' => ['required', 'date'],
            'location' => ['nullable', 'string'],
            'location_detail' => ['nullable', 'string'],
            'category_name' => ['nullable', 'string'],
            'crew' => ['nullable', 'string'],
            'inventory_number' => ['nullable', 'string'],
            'complaint_code' => ['nullable', 'string'],
            'site' => ['nullable', 'string'],
            'image' => ['nullable', 'file', 'image'],
        ]);

        $site = SiteContext::resolve($request) ?? 'HO';
        $maxId = ((int) Aduan::max('max_id')) + 1;
        $userAll = UserAll::where('nrp', $validated['nrp'])->first();

        $payload = [
            'max_id' => $maxId,
            'nrp' => $validated['nrp'],
            'complaint_name' => $validated['complaint_name'],
            'complaint_note' => $validated['complaint_note'],
            'phone_number' => $validated['phone_number'] ?? null,
            'date_of_complaint' => $validated['date_of_complaint'],
            'created_date' => Carbon::parse($validated['date_of_complaint'])->toDateString(),
            'location' => $validated['location'] ?? null,
            'detail_location' => $validated['location_detail'] ?? null,
            'category_name' => $validated['category_name'] ?? null,
            'crew' => $validated['crew'] ?? null,
            'inventory_number' => $validated['inventory_number'] ?? null,
            'complaint_code' => $validated['complaint_code'] ?? $this->generateTicket($site),
            'complaint_position' => $userAll?->position ?? 'User Belum Terdaftar Pada Sistem (NRP Not Detect!)',
            'status' => 'OPEN',
            'urgency' => 'NORMAL',
            'site' => $site,
            'site_pelapor' => $site,
        ];

        if ($request->hasFile('image')) {
            $payload['complaint_image'] = url('storage/' . $request->file('image')->store('images', 'public'));
        }

        $aduan = Aduan::create($payload);

        return response()->json([
            'message' => 'Aduan created successfully.',
            'data' => $aduan,
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        SiteContext::authorizeWrite($request);

        $aduan = $this->authorizedAduanQuery($request)->findOrFail($id);

        $validated = $request->validate([
            'complaint_name'    => ['nullable', 'string'],
            'complaint_note'    => ['nullable', 'string'],
            'phone_number'      => ['nullable', 'string'],
            'date_of_complaint' => ['nullable', 'date'],
            'location'          => ['nullable', 'string'],
            'location_detail'   => ['nullable', 'string'],
            'category_name'     => ['nullable', 'string'],
            'crew'              => ['nullable', 'string'],
            'inventory_number'  => ['nullable', 'string'],
        ]);

        // Map Flutter field name → DB column name
        if (array_key_exists('location_detail', $validated)) {
            $validated['detail_location'] = $validated['location_detail'];
            unset($validated['location_detail']);
        }

        $aduan->update($validated);

        return response()->json([
            'message' => 'Aduan updated successfully.',
            'data'    => $aduan->fresh(),
        ]);
    }

    public function accept(Request $request, string $id)
    {
        SiteContext::authorizeWrite($request);

        $aduan = $this->authorizedAduanQuery($request)->findOrFail($id);
        $aduan->start_response = now();
        $aduan->status = 'PROGRESS';

        $awal = Carbon::parse($aduan->date_of_complaint);
        $akhir = Carbon::parse($aduan->start_response);
        $diffInSeconds = $awal->diffInSeconds($akhir);

        $aduan->response_time = sprintf(
            '%02d:%02d:%02d',
            floor($diffInSeconds / 3600),
            floor(($diffInSeconds % 3600) / 60),
            $diffInSeconds % 60
        );
        $aduan->save();

        return response()->json([
            'message' => 'Aduan accepted.',
            'data' => $aduan,
        ]);
    }

    public function updateProgress(Request $request, string $id)
    {
        $validated = $request->validate([
            'repair_note' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:OPEN,PROGRESS,CLOSED,CANCEL'],
            'location' => ['nullable', 'string'],
            'detail_location' => ['nullable', 'string'],
            'complaint_note' => ['nullable', 'string'],
            'action_repair' => ['nullable', 'string'],
            'date_of_complaint' => ['nullable', 'date'],
            'start_response' => ['nullable', 'date'],
            'start_progress' => ['nullable', 'date'],
            'end_progress' => ['nullable', 'date'],
            'root_cause_id' => ['nullable'],
            'crew' => ['nullable', 'string'],
            'urgency' => ['nullable', 'string', 'in:NORMAL,URGENT'],
            'image' => ['nullable', 'file', 'image'],
        ]);

        SiteContext::authorizeWrite($request);

        $aduan = $this->authorizedAduanQuery($request)->findOrFail($id);
        $aduan->fill($validated);

        if ($request->hasFile('image')) {
            $aduan->repair_image = url('storage/' . $request->file('image')->store('images', 'public'));
        }

        if (! empty($validated['date_of_complaint']) && ! empty($validated['start_response'])) {
            $awal = Carbon::parse($validated['date_of_complaint']);
            $akhir = Carbon::parse($validated['start_response']);
            $diffInSeconds = $awal->diffInSeconds($akhir);

            $aduan->response_time = sprintf(
                '%02d:%02d:%02d',
                floor($diffInSeconds / 3600),
                floor(($diffInSeconds % 3600) / 60),
                $diffInSeconds % 60
            );
        }

        $aduan->save();

        // Mirror web behavior: update PerangkatBreakdown when status → CLOSED
        if ($validated['status'] === 'CLOSED' && ! empty($aduan->complaint_code)) {
            $this->handlePerangkatBreakdown($aduan, $validated);
        }

        return response()->json([
            'message' => 'Aduan updated successfully.',
            'data' => $aduan,
        ]);
    }

    public function updateUrgency(Request $request, string $id)
    {
        $validated = $request->validate([
            'urgency' => ['required', 'string', 'in:NORMAL,URGENT'],
        ]);

        SiteContext::authorizeWrite($request);

        $aduan = $this->authorizedAduanQuery($request)->findOrFail($id);
        $aduan->update($validated);

        return response()->json([
            'message' => 'Urgency updated.',
            'data' => $aduan,
        ]);
    }

    public function destroy(Request $request, string $id)
    {
        SiteContext::authorizeWrite($request);

        $aduan = $this->authorizedAduanQuery($request)->findOrFail($id);
        $aduan->delete();

        return response()->json([
            'message' => 'Aduan deleted.',
        ]);
    }

    private function handlePerangkatBreakdown(Aduan $aduan, array $validated): void
    {
        $validRootCauses = [
            'PC/NB'      => ['RAM', 'MONITOR', 'KABEL', 'OS', 'DRIVER', 'HARDISK', 'SOFTWARE', 'LAIN-LAIN'],
            'TELKOMSEL'  => ['LINK METRO', 'LINK BTS', 'POWER BTS', 'RECTIFIER', 'SECTORAL'],
            'NETWORK'    => ['LINK BACKBONE', 'ISP', 'POWER', 'KONFIGURASI', 'ACCESS POINT'],
            'SERVER'     => ['POWER', 'RAM', 'STORAGE', 'CPU', 'UPS', 'OS'],
            'CCTV'       => ['LINK/BACKBONE', 'CAMERA', 'SHORT CABLE', 'POWER', 'NVR'],
            'PRINTER'    => ['TINTA HABIS', 'TINTA BOCOR', 'PERLU RESET', 'SENSOR', 'KOMPONEN', 'LAIN-LAIN'],
            'NETWORK MT' => ['BATRAI', 'SOLAR PANEL', 'MPPT', 'BACKBONE', 'ACCESS POINT', 'KABEL'],
            'GPS'        => ['POWER', 'KARTU', 'KUOTA', 'BATRAI GPS', 'UNIT GPS'],
        ];

        if (! array_key_exists($aduan->category_name, $validRootCauses)) {
            return;
        }

        if (empty($validated['root_cause_id'])) {
            return;
        }

        $rootCause = RootCauseProblem::find($validated['root_cause_id']);
        if (! $rootCause || ! in_array($rootCause->root_cause_problem, $validRootCauses[$aduan->category_name], true)) {
            return;
        }

        $updateData = [
            'pic'      => $validated['crew'] ?? null,
            'end_time' => $validated['end_progress'] ?? Carbon::now(),
            'status'   => 'CLOSED',
        ];

        $perangkat = PerangkatBreakdown::where('id_report', $aduan->complaint_code)
            ->where('status', 'OPEN')
            ->latest('start_time')
            ->first();

        if ($perangkat) {
            $perangkat->update($updateData);

            return;
        }

        $categoryInput = strtolower((string) $aduan->category_name);
        $invNumber = strtoupper((string) $aduan->inventory_number);
        $deviceName = 'Unknown Device';
        $idPb = null;

        if ($categoryInput === 'pc/nb') {
            if (str_contains($invNumber, '-NB-')) {
                $inv = InvLaptop::where('laptop_code', $aduan->inventory_number)->first();
                $deviceName = $inv?->laptop_name ?? $deviceName;
                $idPb = $inv?->id;
            } elseif (str_contains($invNumber, '-PC-')) {
                $inv = InvComputer::where('computer_code', $aduan->inventory_number)->first();
                $deviceName = $inv?->computer_name ?? $deviceName;
                $idPb = $inv?->id;
            }
        } elseif ($categoryInput === 'printer') {
            $inv = InvPrinter::where('printer_code', $aduan->inventory_number)->first();
            $deviceName = $inv?->printer_brand ?? $deviceName;
            $idPb = $inv?->id;
        } elseif ($categoryInput === 'cctv') {
            $inv = InvCctv::where('cctv_code', $aduan->inventory_number)->first();
            $deviceName = $inv?->cctv_brand ?? $deviceName;
            $idPb = $inv?->id;
        }

        $now = Carbon::now();

        PerangkatBreakdown::create(array_merge($updateData, [
            'id_report'           => $aduan->complaint_code,
            'inventory_number'    => $aduan->inventory_number,
            'id_perangkat'        => $idPb,
            'device_name'         => $deviceName,
            'device_category'     => $aduan->category_name,
            'start_time'          => $aduan->date_of_complaint ?? $now,
            'created_date'        => $now->toDateString(),
            'month'               => $now->month,
            'year'                => $now->year,
            'root_cause'          => $rootCause->root_cause_problem,
            'root_cause_category' => $aduan->category_name,
            'location'            => $validated['location'] ?? null,
            'status'              => $validated['status'],
            'site'                => $aduan->site,
        ]));
    }

    private function generateTicket(?string $site): string
    {
        $currentDate = now();
        $scopeSite = SiteContext::isHo($site) ? 'HO' : $site;

        $lastTicket = Aduan::whereDate('created_at', $currentDate->toDateString())
            ->where('site', $scopeSite)
            ->orderByDesc('max_id')
            ->first();

        $sequence = 1;
        if ($lastTicket?->complaint_code) {
            $parts = explode('-', $lastTicket->complaint_code);
            $sequence = ((int) end($parts)) + 1;
        }

        return 'ADUAN-' . $currentDate->format('ymd') . '-' . str_pad((string) $sequence, 2, '0', STR_PAD_LEFT);
    }

    private function authorizedAduanQuery(Request $request)
    {
        $query = Aduan::query()->whereNull('deleted_at');

        if (! SiteContext::canAccessAnySite($request)) {
            SiteContext::apply($query, 'site', SiteContext::resolve($request));
        }

        return $query;
    }
}
