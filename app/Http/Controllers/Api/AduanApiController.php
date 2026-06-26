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
use App\Services\ImageOptimizerService;
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

        // Global server-side search (web DataTable parity) so a keyword matches a
        // ticket on ANY page, not just the rows already loaded client-side.
        if ($request->filled('search')) {
            $term = '%' . addcslashes((string) $request->string('search'), '%_\\') . '%';
            $cols = ['complaint_code', 'complaint_name', 'complaint_note', 'category_name',
                'nrp', 'location', 'detail_location', 'status', 'urgency', 'crew',
                'inventory_number', 'action_repair', 'repair_note'];
            $query->where(function ($q) use ($cols, $term) {
                foreach ($cols as $i => $col) {
                    $i === 0 ? $q->where($col, 'like', $term) : $q->orWhere($col, 'like', $term);
                }
            });
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

    /**
     * Columns actually rendered by the rekapAduan Blade template (plus the
     * order-by column). Selecting only these avoids loading wide/unused columns
     * such as complaint_image / repair_image and ~15 others — the previous
     * SELECT * hydrated the full row for every record.
     */
    private const REKAP_PDF_COLUMNS = [
        'complaint_code',
        'category_name',
        'nrp',
        'complaint_name',
        'complaint_position',
        'phone_number',
        'complaint_note',
        'location',
        'detail_location',
        'action_repair',
        'repair_note',
        'status',
        'date_of_complaint',
    ];

    public function exportPdf(Request $request)
    {
        $validated = $request->validate([
            'site'      => 'nullable|string',
            'startDate' => 'nullable|date',
            'endDate'   => 'nullable|date',
            'pic'       => 'nullable|string',
        ]);

        $site      = SiteContext::resolve($request);
        $startDate = $validated['startDate'] ?? null;
        $endDate   = $validated['endDate'] ?? null;
        // NOTE: `pic` is the signatory name for the QR/signature block, NOT a row
        // filter. This mirrors the Inertia source of truth
        // (ExportAduanAllSiteController) byte-for-byte; the recap always lists
        // every complaint in the period, signed by the GL + selected PIC.
        $picName   = $validated['pic'] ?? null;

        $memBeforeQuery = memory_get_usage(true);

        // Build the query but DO NOT ->get(). We stream rows with a cursor and
        // strip Eloquent hydration via toBase(), so the whole dataset is never
        // materialised in memory at the same time as DomPDF's render tree.
        $query = Aduan::query()
            ->select(self::REKAP_PDF_COLUMNS)
            ->whereNull('deleted_at');
        if ($site) {
            SiteContext::apply($query, 'site', $site);
        }
        if ($startDate && $endDate) {
            $query->whereBetween('created_date', [$startDate, $endDate]);
        }
        $query->orderByDesc('date_of_complaint');

        // toBase()->cursor() yields lightweight stdClass rows one at a time. The
        // Blade @foreach consumes them lazily; each row is freed after its <tr>
        // is rendered, so peak memory is O(1) in the dataset instead of O(n).
        $dataAduan = $query->toBase()->cursor();
        $recordCount = (clone $query)->toBase()->count();

        \Illuminate\Support\Facades\Log::info('[ADUAN EXPORT] query prepared', [
            'site'            => $site,
            'records'         => $recordCount,
            'columns'         => count(self::REKAP_PDF_COLUMNS),
            'startDate'       => $startDate,
            'endDate'         => $endDate,
            'mem_before_query_mb' => round($memBeforeQuery / 1048576, 2),
        ]);

        $startDateConv = $startDate ? Carbon::parse($startDate)->translatedFormat('d F Y') : null;
        $endDateConv   = $endDate   ? Carbon::parse($endDate)->translatedFormat('d F Y')   : null;

        $user = auth()->user();
        if ($site === 'HO') {
            $picApproved = 'EDI NUGROHO';
        } elseif ($user && $user->role === 'ict_group_leader') {
            $picApproved = $user->name;
        } else {
            $picApproved = $user?->name ?? '-';
        }

        $qr_base64Approved = $this->tryGenerateQr($picApproved);
        $qr_base64Pic      = $picName ? $this->tryGenerateQr($picName) : null;

        \Barryvdh\DomPDF\Facade\Pdf::setOptions(['isRemoteEnabled' => true]);

        if ($startDate && $endDate) {
            $viewData = compact('dataAduan', 'site', 'picName', 'picApproved', 'qr_base64Approved', 'qr_base64Pic', 'startDateConv', 'endDateConv');
            $filename = 'rekap-aduan-' . $startDate . '-' . $endDate . '.pdf';
        } else {
            $year = Carbon::now()->year;
            $viewData = compact('dataAduan', 'site', 'picName', 'picApproved', 'qr_base64Approved', 'qr_base64Pic', 'year');
            $filename = 'rekap-aduan-' . $year . '.pdf';
        }

        $memBeforeRender = memory_get_usage(true);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('itportal.rekapAllInspeksi.rekapAduan', $viewData)
            ->setPaper('A4', 'landscape');
        $content = $pdf->output();
        $memPeak = memory_get_peak_usage(true);

        \Illuminate\Support\Facades\Log::info('[ADUAN EXPORT] pdf rendered', [
            'records'              => $recordCount,
            'pdf_bytes'            => strlen($content),
            'mem_before_render_mb' => round($memBeforeRender / 1048576, 2),
            'mem_peak_mb'          => round($memPeak / 1048576, 2),
        ]);

        return response($content, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function tryGenerateQr(?string $name): ?string
    {
        if (!$name) return null;
        try {
            $user = User::where('name', $name)->first();
            if (!$user) return null;
            $qrString = "NRP: {$user->nrp}, Nama: {$user->name}, Jabatan: {$user->position}";
            $barcode  = new \Milon\Barcode\DNS2D();
            $barcode->setStorPath(storage_path('framework/barcodes/'));
            return 'data:image/png;base64,' . $barcode->getBarcodePNG($qrString, 'QRCODE');
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function meta(Request $request)
    {
        $site = SiteContext::resolve($request);
        $siteType = SiteContext::isHo($site) ? 'HO' : 'SITE';

        $categories = DB::table('root_cause_categories')
            ->select('id', 'category_root_cause')
            ->where('site_type', $siteType)
            ->get();

        // PIC / crew list — parity with the web Aduan controllers:
        //   per-site (AduanBibController etc.): where('site', X)->where('ict_group','Y')
        //   HO (AduanController@index): ict_ho @ HO (HO users carry no ict_group flag,
        //     so filtering them by 'Y' would wrongly empty the list).
        $crewQuery = User::query()->select('id', 'name', 'role', 'site');
        if (SiteContext::isHo($site)) {
            $crewQuery->where('site', 'HO')->where('role', 'ict_ho');
        } else {
            $crewQuery->where('site', $site)->where('ict_group', 'Y');
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
            $payload['complaint_image'] = url('storage/' . ImageOptimizerService::storeAndOptimize($request->file('image'), 'images'));
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
            'nrp'               => ['nullable', 'string'],
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
            $aduan->repair_image = url('storage/' . ImageOptimizerService::storeAndOptimize($request->file('image'), 'images'));
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
        // NOTE: This MUST mirror AduanController::create() (the Inertia source of
        // truth) byte-for-byte. The web builds the date segment from the 2-digit
        // year concatenated with the UNPADDED month and day (e.g. 2026-06-11 =>
        // "26611"), and the sequence from segment [2] of today's last
        // complaint_code for the same site. Do not "fix" the padding here — the
        // unified web+mobile database must produce identical codes from both apps.
        $currentDate = now();
        $year  = $currentDate->format('y'); // 2-digit year, e.g. "26"
        $month = $currentDate->month;       // unpadded month, e.g. 6
        $day   = $currentDate->day;         // unpadded day, e.g. 11

        $scopeSite = SiteContext::isHo($site) ? 'HO' : $site;

        $lastTicket = Aduan::whereDate('created_at', $currentDate->toDateString())
            ->where('site', $scopeSite)
            ->orderByDesc('max_id')
            ->first();

        $maxId = 0;
        if ($lastTicket?->complaint_code) {
            $parts = explode('-', $lastTicket->complaint_code);
            $maxId = (int) ($parts[2] ?? 0);
        }

        return 'ADUAN-' . $year . $month . $day . '-'
            . str_pad((string) (($maxId % 10000) + 1), 2, '0', STR_PAD_LEFT);
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
