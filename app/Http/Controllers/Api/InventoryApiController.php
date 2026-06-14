<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aduan;
use App\Models\Department;
use App\Models\UserAll;
use App\Support\Api\InventoryRegistry;
use App\Support\Api\SiteContext;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class InventoryApiController extends Controller
{
    public function index(Request $request, string $type)
    {
        $config = InventoryRegistry::get($type);
        $site = SiteContext::resolve($request);
        $model = $config['model'];

        $query = $model::query()->with($config['relations'] ?? []);
        if (! empty($config['site_column'])) {
            SiteContext::apply($query, $config['site_column'], $site);
        }

        // Global search like the web DataTable: match the term in ANY searchable
        // column (not just code + name). Columns are intersected with the model's
        // fillable so a type that lacks a column never breaks the query.
        if ($request->filled('search')) {
            $term = '%' . $request->string('search') . '%';
            $fillable = (new $model())->getFillable();
            $candidates = array_unique(array_merge(
                [$config['code_column'], $config['name_column']],
                array_intersect([
                    'inventory_number', 'laptop_code', 'computer_code', 'printer_code',
                    'scanner_code', 'cctv_code', 'mt_code', 'device_name', 'laptop_name',
                    'computer_name', 'item_name', 'cctv_name', 'type_mt', 'serial_number',
                    'number_asset_ho', 'assets_category', 'merk', 'model', 'ip_address',
                    'location', 'status', 'condition', 'dept', 'note', 'spesifikasi', 'aplikasi',
                ], $fillable)
            ));
            $query->where(function ($builder) use ($candidates, $term) {
                foreach (array_values($candidates) as $i => $col) {
                    $i === 0
                        ? $builder->where($col, 'like', $term)
                        : $builder->orWhere($col, 'like', $term);
                }
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return response()->json([
            'type' => $type,
            'site' => $site,
            'data' => $query->orderBy($config['code_column'])->paginate((int) $request->integer('per_page', 25)),
        ]);
    }

    public function meta(Request $request, string $type)
    {
        $config = InventoryRegistry::get($type);
        $site = SiteContext::resolve($request);

        $departmentQuery = Department::query()->orderBy('department_name');
        $departmentQuery->where('is_site', SiteContext::isHo($site) ? null : 'Y');

        $userAllQuery = UserAll::query()->orderBy('username');
        if (! SiteContext::isHo($site)) {
            $userAllQuery->where('site', $site);
        } else {
            $userAllQuery->where(function ($query) {
                $query->whereNull('site')->orWhere('site', 'HO');
            });
        }

        // CCTV "Switch" dropdown (web CctvController@create passes InvSwitch HO
        // list; value = id, label = inventory_number). Only relevant for cctv but
        // harmless to include for every type.
        $switches = $type === 'cctv'
            ? \App\Models\InvSwitch::query()
                ->where(function ($q) use ($site) {
                    SiteContext::isHo($site)
                        ? $q->whereNull('site')->orWhere('site', 'HO')
                        : $q->where('site', strtoupper((string) $site));
                })
                ->orderBy('inventory_number')
                ->get(['id', 'inventory_number'])
            : collect();

        return response()->json([
            'type' => $type,
            'site' => $site,
            // Drives the create form: 'dept' → show Department selector (laptop/
            // computer); 'company' → show Company selector (AP/switch/printer/
            // wireless/cctv/scanner); 'none' → no auto-code (mobile-tower).
            'code_strategy' => $config['code_strategy'] ?? 'none',
            // Company options are hardcoded PPA/AMM on the web (VueMultiselect).
            'companies' => [['name' => 'PPA'], ['name' => 'AMM']],
            'departments' => $departmentQuery->get(),
            'users' => $userAllQuery->get(['id', 'nrp', 'username', 'department', 'site']),
            // CCTV switch dropdown source.
            'switches' => $switches,
        ]);
    }

    public function show(Request $request, string $type, string $id)
    {
        $config = InventoryRegistry::get($type);
        $model = $config['model'];

        $record = $this->authorizedInventoryQuery($request, $config)
            ->with($config['relations'] ?? [])
            ->findOrFail($id);
        $code = $record->{$config['complaint_column']};

        $response = [
            'type' => $type,
            'data' => $record,
            'complaints' => Aduan::where('inventory_number', $code)->latest()->get(),
        ];

        if (! empty($config['inspection_model'])) {
            $inspectionModel = $config['inspection_model'];
            $response['inspections'] = $inspectionModel::where($config['inspection_foreign_key'], $record->getKey())
                ->latest('created_at')
                ->get();
        }

        return response()->json($response);
    }

    public function store(Request $request, string $type)
    {
        $config = InventoryRegistry::get($type);
        $site = SiteContext::resolve($request) ?? 'HO';
        SiteContext::authorizeWrite($request, $site);

        if (! empty($config['required_fields'])) {
            $request->validate(
                array_fill_keys($config['required_fields'], ['required', 'string'])
            );
        }

        $modelClass = $config['model'];
        /** @var Model $record */
        $record = new $modelClass();

        $payload = $this->extractPayload($request, $record);
        $payload = $this->normalizePayload($payload, $site, $record);
        if (! empty($config['site_column']) && in_array($config['site_column'], $record->getFillable(), true)) {
            $payload[$config['site_column']] = SiteContext::isHo($site) ? 'HO' : $site;
        }

        if (in_array('max_id', $record->getFillable(), true)) {
            $payload['max_id'] = ((int) $modelClass::max('max_id')) + 1;
        }

        if ($request->hasFile('image') && in_array('link_documentation_asset_image', $record->getFillable(), true)) {
            $payload['link_documentation_asset_image'] = $request->file('image')->store('images', 'public');
        }

        $created = $modelClass::create($payload);

        return response()->json([
            'message' => 'Inventory created successfully.',
            'data' => $created,
        ], 201);
    }

    public function update(Request $request, string $type, string $id)
    {
        $config = InventoryRegistry::get($type);
        $record = $this->authorizedInventoryQuery($request, $config)->findOrFail($id);
        $payload = $this->extractPayload($request, $record);
        $recordSite = ! empty($config['site_column'])
            ? $record->getAttribute($config['site_column'])
            : SiteContext::resolve($request);
        SiteContext::authorizeWrite($request, $recordSite);

        $payload = $this->normalizePayload($payload, $recordSite, $record);

        if ($request->hasFile('image') && in_array('link_documentation_asset_image', $record->getFillable(), true)) {
            $payload['link_documentation_asset_image'] = $request->file('image')->store('images', 'public');
        }

        $record->update($payload);

        return response()->json([
            'message' => 'Inventory updated successfully.',
            'data' => $record->fresh(),
        ]);
    }

    public function destroy(Request $request, string $type, string $id)
    {
        $config = InventoryRegistry::get($type);
        $record = $this->authorizedInventoryQuery($request, $config)->findOrFail($id);
        $recordSite = ! empty($config['site_column'])
            ? $record->getAttribute($config['site_column'])
            : SiteContext::resolve($request);
        SiteContext::authorizeWrite($request, $recordSite);

        $record->delete();

        return response()->json([
            'message' => 'Inventory deleted.',
        ]);
    }

    public function generateCode(Request $request, string $type): \Illuminate\Http\JsonResponse
    {
        $config = InventoryRegistry::get($type);
        $model = $config['model'];
        $codeColumn = $config['code_column'];
        $siteColumn = $config['site_column'] ?? 'site';
        $site = SiteContext::resolve($request) ?? 'HO';
        $sitePrefix = SiteContext::isHo($site) ? 'HO' : strtoupper((string) $site);
        $strategy = $config['code_strategy'] ?? 'none';

        // Scope a query to the active site (HO = null/HO), mirroring the web
        // per-site controllers that hardcode where('site', '<SITE>').
        $scopeSite = function ($query) use ($siteColumn, $site, $sitePrefix) {
            if (SiteContext::isHo($site)) {
                $query->where(fn ($w) => $w->whereNull($siteColumn)->orWhere($siteColumn, 'HO'));
            } else {
                $query->where($siteColumn, $sitePrefix);
            }
            return $query;
        };

        // DEPARTMENT-SCOPED (laptop/computer) — web InvLaptop/InvComputer::
        // generateCode: {SITE}-{NB|PC}-{deptCode}-{seq:3}, sequence per site+dept.
        if ($strategy === 'dept') {
            if (! $request->filled('dept')) {
                return response()->json(
                    ['code' => '', 'message' => 'Pilih department terlebih dahulu.'],
                    422
                );
            }
            $deptInput = trim((string) $request->string('dept'));
            $department = Department::where('department_name', $deptInput)
                ->orWhere('code', $deptInput)
                ->first();
            $deptCode = $department->code ?? $deptInput;

            $latest = $scopeSite($model::query())
                ->where('dept', $deptCode)
                ->orderByDesc('max_id')
                ->first();

            $seq = 0;
            if ($latest && $latest->{$codeColumn}) {
                $parts = explode('-', (string) $latest->{$codeColumn});
                $seq = (int) end($parts);
            }

            $code = $sitePrefix . '-' . ($config['dept_infix'] ?? '') . '-' . $deptCode . '-'
                . str_pad((string) (($seq % 10000) + 1), 3, '0', STR_PAD_LEFT);

            return response()->json(['code' => $code, 'dept' => $deptCode]);
        }

        // COMPANY-SCOPED (AP/switch/printer/wireless/cctv/scanner) — web Inv*::
        // generateCode: {COMPANY}{SITE}{company_code}{seq:3}, sequence per
        // site + code LIKE 'COMPANY%'. Company ∈ {PPA, AMM} (hardcoded in web).
        if ($strategy === 'company') {
            $companyInput = strtoupper(trim(
                (string) ($request->input('company.name') ?? $request->string('company'))
            ));
            $company = in_array($companyInput, ['PPA', 'AMM'], true) ? $companyInput : null;
            if ($company === null) {
                return response()->json(
                    ['code' => '', 'message' => 'Pilih company (PPA / AMM) terlebih dahulu.'],
                    422
                );
            }
            $prefix = $company . $sitePrefix . ($config['company_code'] ?? '');

            $latest = $scopeSite($model::query())
                ->where($codeColumn, 'like', $company . '%')
                ->orderByDesc('max_id')
                ->first();

            $seq = 0;
            if ($latest && $latest->{$codeColumn}
                && preg_match('/(\d+)$/', (string) $latest->{$codeColumn}, $m)) {
                $seq = (int) $m[1];
            }

            $code = $prefix . str_pad((string) (($seq % 10000) + 1), 3, '0', STR_PAD_LEFT);

            return response()->json(['code' => $code, 'company' => $company]);
        }

        // strategy 'none' (mobile-tower) — no company/dept auto-code on the web.
        return response()->json(['code' => '']);
    }

    private function extractPayload(Request $request, Model $record): array
    {
        $fillable = collect($record->getFillable())
            ->reject(fn (string $field) => in_array($field, ['max_id', 'site', 'link_documentation_asset_image'], true))
            ->values()
            ->all();

        return $request->only($fillable);
    }

    private function normalizePayload(array $payload, ?string $site, Model $record): array
    {
        if (in_array('user_alls_id', $record->getFillable(), true) && array_key_exists('user_alls_id', $payload)) {
            $payload['user_alls_id'] = $this->resolveUserAllId($payload['user_alls_id'], $site);
        }

        foreach (['date_of_inventory', 'date_of_deploy', 'inventory_date'] as $dateField) {
            if (! empty($payload[$dateField])) {
                try {
                    $payload[$dateField] = Carbon::parse($payload[$dateField])->toDateString();
                } catch (\Throwable) {
                    // keep as-is if unparseable
                }
            }
        }

        return $payload;
    }

    private function resolveUserAllId(mixed $value, ?string $site): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalizedSite = SiteContext::isHo($site) ? 'HO' : strtoupper((string) $site);

        $query = UserAll::query();
        if ($normalizedSite === 'HO') {
            $query->where(function ($builder) {
                $builder->whereNull('site')->orWhere('site', 'HO');
            });
        } else {
            $query->where('site', $normalizedSite);
        }

        $user = $query
            ->where(function ($builder) use ($value) {
                $builder->where('id', $value)->orWhere('nrp', $value);
            })
            ->first();

        if ($user) {
            return (int) $user->id;
        }

        throw new HttpResponseException(response()->json([
            'message' => 'Selected user_alls_id is invalid for this site.',
            'errors' => [
                'user_alls_id' => ['User tidak ditemukan pada site yang dipilih. Gunakan nilai `id` atau `nrp` dari endpoint meta.'],
            ],
        ], 422));
    }

    private function authorizedInventoryQuery(Request $request, array $config)
    {
        $model = $config['model'];
        $query = $model::query();

        if (! empty($config['site_column']) && ! SiteContext::canAccessAnySite($request)) {
            SiteContext::apply($query, $config['site_column'], SiteContext::resolve($request));
        }

        return $query;
    }
}
