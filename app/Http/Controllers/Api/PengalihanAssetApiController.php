<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\InvComputer;
use App\Models\InvLaptop;
use App\Models\PengalihanAsset;
use App\Models\User;
use App\Models\UserAll;
use App\Support\Api\SiteContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PengalihanAssetApiController extends Controller
{
    public function index(Request $request)
    {
        $site = $this->resolveSite($request);

        $crew = $site !== 'HO'
            ? User::whereIn('role', ['ict_technician', 'ict_group_leader'])->where('site', $site)
            : User::where('role', 'ict_ho')->where('site', 'HO');

        return response()->json([
            'site' => $site,
            'crew' => $crew->orderBy('name')->get()->map(fn ($item) => ['name' => $item->name])->values(),
        ]);
    }

    public function data(Request $request)
    {
        $this->ensurePengalihanTableExists();

        $validated = $request->validate([
            'device_type' => ['required', 'string', 'in:laptop,computer,Laptop,Computer'],
            'site' => ['required', 'string'],
            'startDate' => ['nullable', 'date'],
            'endDate' => ['nullable', 'date'],
        ]);

        $deviceType = ucfirst(strtolower($validated['device_type']));
        $site = $this->resolveSite($request);
        $startDate = ! empty($validated['startDate']) ? Carbon::parse($validated['startDate'])->startOfDay() : null;
        $endDate = ! empty($validated['endDate']) ? Carbon::parse($validated['endDate'])->endOfDay() : null;

        $model = $deviceType === 'Laptop' ? InvLaptop::class : InvComputer::class;
        $codeColumn = $deviceType === 'Laptop' ? 'laptop_code' : 'computer_code';

        $query = PengalihanAsset::where('site', $site)->where('device', $deviceType);
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->whereBetween('created_at', [$startDate, now()]);
        } elseif ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $pengalihans = $query->get();
        $invPrevIds = $pengalihans->pluck('id_inv_prev')->filter()->unique()->values();
        $invPrevData = $model::onlyTrashed()->whereIn('id', $invPrevIds)->get();
        $userPrev = UserAll::whereIn('nrp', $pengalihans->pluck('nrp_user_prev')->filter()->unique())->get();
        $userNext = UserAll::whereIn('nrp', $pengalihans->pluck('nrp_user_new')->filter()->unique())->get();

        return response()->json([
            'data' => $pengalihans->map(function ($item) use ($invPrevData, $userPrev, $userNext, $codeColumn) {
                $inventory = $invPrevData->firstWhere('id', $item->id_inv_prev);
                $prev = $userPrev->firstWhere('nrp', $item->nrp_user_prev);
                $next = $userNext->firstWhere('nrp', $item->nrp_user_new);

                return [
                    'pengalihan' => $item,
                    'inventory_prev' => optional($inventory)->{$codeColumn},
                    'user_prev_name' => $prev->username ?? null,
                    'user_prev_position' => $prev->position ?? null,
                    'user_prev_dept' => $prev->department ?? null,
                    'user_next_name' => $next->username ?? null,
                    'user_next_position' => $next->position ?? null,
                    'user_next_dept' => $next->department ?? null,
                ];
            })->values(),
        ]);
    }

    public function meta(Request $request)
    {
        $site = $this->resolveSite($request);

        return response()->json([
            'site' => $site,
            'departments' => Department::orderBy('department_name')
                ->where('is_site', 'Y')
                ->pluck('department_name', 'code')
                ->map(fn ($name, $code) => ['name' => $name, 'code' => $code])
                ->values(),
            'users' => UserAll::where('site', $site)->get(['username', 'nrp'])->map(fn ($user) => [
                'name' => $user->username,
                'nrp' => $user->nrp,
            ])->values(),
        ]);
    }

    public function inventories(Request $request)
    {
        $validated = $request->validate([
            'device_type' => ['required', 'string'],
            'department' => ['required', 'string'],
            'site' => ['required', 'string'],
        ]);

        $site = $this->resolveSite($request);

        $inventory = strtolower($validated['device_type']) === 'laptop'
            ? InvLaptop::withTrashed()->where('site', $site)->where('dept', $validated['department'])->get(['laptop_code as code', 'id'])
            : InvComputer::withTrashed()->where('site', $site)->where('dept', $validated['department'])->get(['computer_code as code', 'id']);

        return response()->json(['inventoryData' => $inventory]);
    }

    public function inventoryDetail(Request $request)
    {
        $validated = $request->validate([
            'device_type' => ['required', 'string'],
            'idInv' => ['required'],
        ]);

        $site = $this->resolveSite($request);

        $inventory = strtolower($validated['device_type']) === 'laptop'
            ? InvLaptop::withTrashed()->where('id', $validated['idInv'])->with('pengguna')->first()
            : InvComputer::withTrashed()->where('id', $validated['idInv'])->with('pengguna')->first();

        abort_if(! $inventory || (! SiteContext::canAccessAnySite($request) && strtoupper((string) $inventory->site) !== $site), 404);

        return response()->json(['inventoryData' => $inventory]);
    }

    public function userByNrp(Request $request)
    {
        $validated = $request->validate([
            'nrp' => ['required', 'string'],
        ]);

        return response()->json([
            'userData' => $this->authorizedUserAllQuery($request)->where('nrp', $validated['nrp'])->first(),
        ]);
    }

    public function generateCode(Request $request)
    {
        $validated = $request->validate([
            'device_type' => ['required', 'string'],
            'dept' => ['required', 'string'],
            'site' => ['required', 'string'],
        ]);

        $site = $this->resolveSite($request);

        if (strtolower($validated['device_type']) === 'laptop') {
            $maxId = InvLaptop::where('site', $site)->where('dept', $validated['dept'])->orderByDesc('max_id')->first();
            $last = $maxId ? (int) last(explode('-', $maxId->laptop_code)) : 0;
            $code = $site . '-NB-' . $validated['dept'] . '-' . str_pad(($last % 10000) + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $maxId = InvComputer::where('site', $site)->where('dept', $validated['dept'])->orderByDesc('max_id')->first();
            $last = $maxId ? (int) last(explode('-', $maxId->computer_code)) : 0;
            $code = $site . '-PC-' . $validated['dept'] . '-' . str_pad(($last % 10000) + 1, 3, '0', STR_PAD_LEFT);
        }

        return response()->json(['inventoryData' => $code]);
    }

    public function store(Request $request)
    {
        $this->ensurePengalihanTableExists();

        $validated = $request->validate([
            'id' => ['nullable'],
            'deviceType' => ['required', 'string', 'in:Laptop,Computer'],
            'idInvPrev' => ['required'],
            'invNumberNext' => ['required', 'string'],
            'prevNrp' => ['required', 'string'],
            'userNext' => ['required', 'string'],
            'spek' => ['nullable', 'string'],
            'deptNext' => ['required', 'string'],
            'deptPrev' => ['nullable', 'string'],
            'remark' => ['nullable', 'string'],
            'site' => ['required', 'string'],
            'image' => ['nullable', 'file', 'image'],
        ]);

        $site = $this->resolveSite($request);
        SiteContext::authorizeWrite($request, $site);

        if (! SiteContext::canAccessAnySite($request)) {
            abort_if(! UserAll::where('site', $site)->where('nrp', $validated['prevNrp'])->exists(), 404);
        }

        $imagePath = $request->hasFile('image')
            ? url('storage/' . $request->file('image')->store('images', 'public'))
            : null;

        $inventoryModel = $validated['deviceType'] === 'Laptop' ? InvLaptop::class : InvComputer::class;
        $prevInventory = $inventoryModel::where('id', $validated['idInvPrev'])->where('site', $site)->first();
        abort_if(! $prevInventory, 404, 'Previous inventory not found.');

        $newUser = UserAll::where('nrp', $validated['userNext'])
            ->when(! SiteContext::canAccessAnySite($request), fn ($q) => $q->where('site', $site))
            ->first();
        abort_if(! $newUser, 404, 'New user not found.');

        PengalihanAsset::create([
            'id_inventory' => $validated['id'] ?? null,
            'device' => $validated['deviceType'],
            'id_inv_prev' => $validated['idInvPrev'],
            'inv_number_next' => $validated['invNumberNext'],
            'nrp_user_prev' => $validated['prevNrp'],
            'nrp_user_new' => $validated['userNext'],
            'spek' => $validated['spek'] ?? null,
            'dept' => $validated['deptNext'],
            'dept_prev' => $validated['deptPrev'] ?? null,
            'remark' => $validated['remark'] ?? null,
            'foto_pengalihan' => $imagePath,
            'site' => $site,
        ]);

        // Mirror web behavior: create new inventory assignment, then soft-delete previous
        if ($validated['deviceType'] === 'Laptop') {
            InvLaptop::create([
                'max_id'                       => $prevInventory->max_id,
                'laptop_name'                  => $prevInventory->laptop_name,
                'laptop_code'                  => $validated['invNumberNext'],
                'number_asset_ho'              => $prevInventory->number_asset_ho,
                'assets_category'              => $prevInventory->assets_category,
                'serial_number'                => $prevInventory->serial_number,
                'aplikasi'                     => $prevInventory->aplikasi,
                'spesifikasi'                  => $prevInventory->spesifikasi,
                'license'                      => $prevInventory->license,
                'ip_address'                   => $prevInventory->ip_address,
                'date_of_inventory'            => $prevInventory->date_of_inventory,
                'date_of_deploy'               => $prevInventory->date_of_deploy,
                'location'                     => $prevInventory->location,
                'status'                       => $prevInventory->status,
                'condition'                    => $prevInventory->condition,
                'note'                         => $prevInventory->note,
                'link_documentation_asset_image' => $imagePath ?? $prevInventory->link_documentation_asset_image,
                'user_alls_id'                 => $newUser->id,
                'site'                         => $site,
                'dept'                         => $validated['deptNext'],
            ]);
        } else {
            InvComputer::create([
                'max_id'                       => $prevInventory->max_id,
                'computer_name'                => $prevInventory->computer_name,
                'computer_code'                => $validated['invNumberNext'],
                'number_asset_ho'              => $prevInventory->number_asset_ho,
                'assets_category'              => $prevInventory->assets_category,
                'serial_number'                => $prevInventory->serial_number,
                'aplikasi'                     => $prevInventory->aplikasi,
                'spesifikasi'                  => $prevInventory->spesifikasi,
                'license'                      => $prevInventory->license,
                'ip_address'                   => $prevInventory->ip_address,
                'date_of_inventory'            => $prevInventory->date_of_inventory,
                'date_of_deploy'               => $prevInventory->date_of_deploy,
                'location'                     => $prevInventory->location,
                'status'                       => $prevInventory->status,
                'condition'                    => $prevInventory->condition,
                'note'                         => $prevInventory->note,
                'link_documentation_asset_image' => $imagePath ?? $prevInventory->link_documentation_asset_image,
                'user_alls_id'                 => $newUser->id,
                'site'                         => $site,
                'dept'                         => $validated['deptNext'],
            ]);
        }

        $prevInventory->delete();

        return response()->json([
            'message' => 'Pengalihan asset created successfully.',
        ], 201);
    }

    private function ensurePengalihanTableExists(): void
    {
        if (Schema::hasTable('pengalihan_asset') || Schema::hasTable('pengalihan_assets')) {
            return;
        }

        abort(response()->json([
            'message' => 'Table pengalihan asset belum tersedia di database.',
            'details' => 'Dibutuhkan tabel `pengalihan_asset` atau `pengalihan_assets` agar endpoint ini bisa digunakan.',
        ], 503));
    }

    private function resolveSite(Request $request): string
    {
        return SiteContext::resolve($request) ?? 'HO';
    }

    private function authorizedUserAllQuery(Request $request)
    {
        $query = UserAll::query();

        if (! SiteContext::canAccessAnySite($request)) {
            $query->where('site', $this->resolveSite($request));
        }

        return $query;
    }
}
