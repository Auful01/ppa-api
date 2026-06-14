<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aduan;
use App\Models\UserAll;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Pengaduan HO — mobile parity with the Inertia source of truth
 * App\Http\Controllers\AduanHoController.
 *
 * Pengaduan HO is NOT the normal Aduan module. Special rules taken VERBATIM from
 * AduanHoController:
 *   - index list  : where nrp = auth user nrp        (only the user's OWN reports)
 *                   AND site = 'HO'                   (fixed site, ignores any selected site)
 *                   AND site_pelapor = auth user site (reporter's own site)
 *                   order by date_of_complaint desc
 *   - summary     : site = 'HO' AND site_pelapor = auth user site, NO nrp filter
 *                   (intentional asymmetry — counts span the whole reporting site,
 *                    the list only the current user; mirrors the web exactly).
 *   - create/store: site = 'HO', site_pelapor = auth user site, status = 'OPEN',
 *                   complaint_position resolved from UserAll by nrp, categories
 *                   scoped to root_cause_categories.site_type = 'HO'.
 *   - ticket code : 'ADUAN-' . yy . unpadded-month . unpadded-day . '-' . seq,
 *                   seq scoped to today's last code for site = 'HO'.
 *   - NO progress / accept / edit / urgency / export — the web HO page does not
 *     expose those actions, so neither do we.
 *
 * Kept as a SEPARATE controller (not folded into AduanApiController) so the normal
 * Aduan flow is untouched.
 */
class AduanHoApiController extends Controller
{
    /**
     * Pengaduan HO is restricted to Group Leader + ICT Developer (client
     * requirement). Enforced on every endpoint so the API matches the menu
     * visibility and can't be reached by other roles directly.
     */
    private function authorizeHo(Request $request): void
    {
        $role = $request->user()?->role;
        if (! in_array($role, ['ict_group_leader', 'ict_developer'], true)) {
            abort(403, 'You dont have permission to access this page.');
        }
    }

    public function index(Request $request)
    {
        $this->authorizeHo($request);
        $user = $request->user();
        $nrp  = $user->nrp;
        $site = strtoupper((string) $user->site); // site_pelapor scope = reporter's own site

        // VERBATIM AduanHoController@index list query.
        $aduan = Aduan::query()
            ->with('rootCause')
            ->where('nrp', $nrp)
            ->where('site', 'HO')
            ->where('site_pelapor', $site)
            ->orderByDesc('date_of_complaint')
            ->paginate((int) $request->integer('per_page', 25));

        // VERBATIM AduanHoController@index counts (site = HO + site_pelapor, NO nrp).
        $statsQuery = fn () => Aduan::query()
            ->where('site', 'HO')
            ->where('site_pelapor', $site);

        return response()->json([
            'data' => $aduan,
            'meta' => [
                'site'         => 'HO',
                'site_pelapor' => $site,
                'nrp'          => $nrp,
                'summary' => [
                    'open'     => $statsQuery()->where('status', 'OPEN')->count(),
                    'closed'   => $statsQuery()->where('status', 'CLOSED')->count(),
                    'progress' => $statsQuery()->where('status', 'PROGRESS')->count(),
                    'cancel'   => $statsQuery()->where('status', 'CANCEL')->count(),
                ],
            ],
        ]);
    }

    /**
     * Create-form support — mirrors AduanHoController@create:
     * HO-scoped categories, auto ticket, and the auth user's own nrp/name as the
     * pre-filled reporter (the web create page does NOT offer crew assignment).
     */
    public function meta(Request $request)
    {
        $this->authorizeHo($request);
        $user = $request->user();

        $categories = DB::table('root_cause_categories')
            ->select('id', 'category_root_cause')
            ->where('site_type', 'HO')
            ->get();

        return response()->json([
            'site'         => 'HO',
            'site_pelapor' => strtoupper((string) $user->site),
            'nrp'          => $user->nrp,
            'nama'         => $user->name,
            'ticket'       => $this->generateTicket(),
            'categories'   => $categories,
        ]);
    }

    public function show(Request $request, string $id)
    {
        $this->authorizeHo($request);
        $aduan = Aduan::with('rootCause')->findOrFail($id);

        return response()->json(['data' => $aduan]);
    }

    /**
     * Store — VERBATIM AduanHoController@store: site forced to 'HO', site_pelapor =
     * auth user site, status OPEN, complaint_position from UserAll. No crew column.
     */
    public function store(Request $request)
    {
        $this->authorizeHo($request);
        $user = $request->user();

        $validated = $request->validate([
            'nrp'               => ['required', 'string'],
            'complaint_name'    => ['required', 'string'],
            'complaint_note'    => ['required', 'string'],
            'phone_number'      => ['nullable', 'string'],
            'date_of_complaint' => ['nullable', 'date'],
            'location'          => ['nullable', 'string'],
            'location_detail'   => ['nullable', 'string'],
            'category_name'     => ['nullable', 'string'],
            'inventory_number'  => ['nullable', 'string'],
            'complaint_code'    => ['nullable', 'string'],
            'image'             => ['nullable', 'file', 'image'],
        ]);

        $maxId   = ((int) Aduan::max('max_id')) + 1;
        $userAll = UserAll::where('nrp', $validated['nrp'])->first();
        // Normalise to the MySQL datetime format the column expects. The web HO
        // store uses now()->toDateTimeString(); we accept the client's instant but
        // store it in the same 'Y-m-d H:i:s' shape (raw ISO-8601 with 'Z' would be
        // rejected by the datetime column).
        $dateOfComplaint = isset($validated['date_of_complaint'])
            ? Carbon::parse($validated['date_of_complaint'])->format('Y-m-d H:i:s')
            : now()->utc()->toDateTimeString();

        $payload = [
            'max_id'             => $maxId,
            'nrp'                => $validated['nrp'],
            'complaint_name'     => $validated['complaint_name'],
            'complaint_note'     => $validated['complaint_note'],
            'phone_number'       => $validated['phone_number'] ?? null,
            'date_of_complaint'  => $dateOfComplaint,
            'created_date'       => Carbon::parse($dateOfComplaint)->toDateString(),
            'location'           => $validated['location'] ?? null,
            'detail_location'    => $validated['location_detail'] ?? null,
            'category_name'      => $validated['category_name'] ?? null,
            'inventory_number'   => $validated['inventory_number'] ?? null,
            'complaint_code'     => $validated['complaint_code'] ?? $this->generateTicket(),
            'complaint_position' => $userAll?->position ?? 'User Belum Terdaftar Pada Sistem (NRP Not Detect!)',
            'status'             => 'OPEN',
            'site'               => 'HO',
            'site_pelapor'       => strtoupper((string) $user->site),
        ];

        if ($request->hasFile('image')) {
            $payload['complaint_image'] = url('storage/' . $request->file('image')->store('images', 'public'));
        }

        $aduan = Aduan::create($payload);

        return response()->json([
            'message' => 'Pengaduan HO created successfully.',
            'data'    => $aduan,
        ], 201);
    }

    public function destroy(Request $request, string $id)
    {
        $this->authorizeHo($request);
        $aduan = Aduan::findOrFail($id);
        $aduan->delete();

        return response()->json(['message' => 'Pengaduan HO deleted.']);
    }

    /**
     * Ticket generator — mirrors AduanHoController@create byte-for-byte: 2-digit
     * year + UNPADDED month + UNPADDED day, sequence from segment [2] of today's
     * last complaint_code scoped to site = 'HO'.
     */
    private function generateTicket(): string
    {
        $currentDate = Carbon::now();
        $year  = $currentDate->format('y');
        $month = $currentDate->month;
        $day   = $currentDate->day;

        $lastTicket = Aduan::whereDate('created_at', $currentDate->format('Y-m-d'))
            ->where('site', 'HO')
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
}
