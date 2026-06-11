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

        if ($request->filled('search')) {
            $term = '%' . $request->string('search') . '%';
            $query->where(function ($builder) use ($config, $term) {
                $builder
                    ->where($config['code_column'], 'like', $term)
                    ->orWhere($config['name_column'], 'like', $term);
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

        return response()->json([
            'type' => $type,
            'site' => $site,
            'departments' => $departmentQuery->get(),
            'users' => $userAllQuery->get(['id', 'nrp', 'username', 'department', 'site']),
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

        // SOURCE OF TRUTH: laptop & computer use a DEPARTMENT-SCOPED code on the
        // web (InvLaptopController/InvComputerController::generateCode):
        //   {SITE}-NB-{deptCode}-{seq:3}  (laptop)
        //   {SITE}-PC-{deptCode}-{seq:3}  (computer)
        // The sequence resets per department, so we must scope by `dept`. The
        // generic trailing-number increment below is only correct for the
        // non-dept types (AP/switch/wireless/cctv/scanner/MT).
        $deptInfix = ['laptop' => 'NB', 'computer' => 'PC'];
        $normalizedType = strtolower($type);
        if (isset($deptInfix[$normalizedType]) && $request->filled('dept')) {
            $deptInput = trim((string) $request->string('dept'));
            $department = Department::where('department_name', $deptInput)
                ->orWhere('code', $deptInput)
                ->first();
            $deptCode = $department->code ?? $deptInput;
            $sitePrefix = SiteContext::isHo($site) ? 'HO' : strtoupper((string) $site);

            $latestDept = $model::query()
                ->when(
                    SiteContext::isHo($site),
                    fn ($q) => $q->where(fn ($w) => $w->whereNull($siteColumn)->orWhere($siteColumn, 'HO')),
                    fn ($q) => $q->where($siteColumn, $sitePrefix)
                )
                ->where('dept', $deptCode)
                ->orderByDesc('max_id')
                ->first();

            $seq = 0;
            if ($latestDept && $latestDept->{$codeColumn}) {
                $parts = explode('-', (string) $latestDept->{$codeColumn});
                $seq = (int) end($parts);
            }

            $code = $sitePrefix . '-' . $deptInfix[$normalizedType] . '-' . $deptCode . '-'
                . str_pad((string) (($seq % 10000) + 1), 3, '0', STR_PAD_LEFT);

            return response()->json(['code' => $code]);
        }

        $latest = $model::query()
            ->when(! empty($siteColumn), function ($query) use ($siteColumn, $site) {
                if (SiteContext::isHo($site)) {
                    $query->where(function ($q) use ($siteColumn) {
                        $q->whereNull($siteColumn)->orWhere($siteColumn, 'HO');
                    });
                } else {
                    $query->where($siteColumn, $site);
                }
            })
            ->orderByDesc('max_id')
            ->first();

        if (! $latest) {
            return response()->json(['code' => '']);
        }

        $existingCode = (string) $latest->{$codeColumn};

        if (preg_match('/^(.*?)(\d+)$/', $existingCode, $matches)) {
            $prefix = $matches[1];
            $lastNumber = (int) $matches[2];
            $width = max(strlen($matches[2]), 3);
            $nextCode = $prefix . str_pad($lastNumber + 1, $width, '0', STR_PAD_LEFT);

            return response()->json(['code' => $nextCode]);
        }

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
