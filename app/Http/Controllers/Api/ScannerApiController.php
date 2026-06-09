<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\InvScanner;
use App\Support\Api\SiteContext;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ScannerApiController extends Controller
{
    public function index(Request $request)
    {
        $site = $this->resolveSite($request);

        $query = InvScanner::query();
        if ($site === 'HO') {
            $query->where(function ($builder) {
                $builder->whereNull('site')->orWhere('site', 'HO');
            });
        } else {
            $query->where('site', $site);
        }

        if ($request->filled('search')) {
            $term = '%' . $request->string('search') . '%';
            $query->where(function ($builder) use ($term) {
                $builder->where('scanner_code', 'like', $term)
                    ->orWhere('item_name', 'like', $term)
                    ->orWhere('serial_number', 'like', $term);
            });
        }

        return response()->json([
            'data' => $query->orderBy('scanner_code')->paginate((int) $request->integer('per_page', 25)),
            'meta' => [
                'site' => $site,
            ],
        ]);
    }

    public function meta(Request $request)
    {
        return response()->json([
            'site' => $this->resolveSite($request),
            'departments' => Department::orderBy('department_name')
                ->get()
                ->map(fn ($item) => ['name' => $item->department_name]),
            'companies' => [
                ['name' => 'PPA'],
                ['name' => 'AMM'],
            ],
        ]);
    }

    public function generateCode(Request $request)
    {
        $validated = $request->validate([
            'company.name' => ['nullable', 'string'],
            'company' => ['nullable', 'array'],
            'company_name' => ['nullable', 'string'],
            'site' => ['nullable', 'string'],
        ]);

        $company = data_get($validated, 'company.name')
            ?? ($validated['company_name'] ?? 'PPA');
        $site = $this->resolveSite($request);
        $prefix = strtoupper($company) === 'PPA' ? 'PPAHOSCN' : 'AMMHOSCN';

        $maxScanner = InvScanner::query()
            ->where('site', $site)
            ->where('scanner_code', 'like', $prefix . '%')
            ->orderByDesc('max_id')
            ->first();

        if (! $maxScanner) {
            $lastNumber = 0;
        } else {
            preg_match('/(\d+)$/', (string) $maxScanner->scanner_code, $matches);
            $lastNumber = isset($matches[1]) ? (int) $matches[1] : 0;
        }

        return response()->json([
            'code' => $prefix . str_pad(($lastNumber % 10000) + 1, 3, '0', STR_PAD_LEFT),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request, false);
        $site = $this->resolveSite($request);
        SiteContext::authorizeWrite($request, $site);

        $validated['max_id'] = ((int) InvScanner::max('max_id')) + 1;
        $validated['date_of_inventory'] = Carbon::parse($validated['date_of_inventory'])->toDateString();
        $validated['site'] = $site;

        $scanner = InvScanner::create($validated);

        return response()->json([
            'message' => 'Scanner created successfully.',
            'data' => $scanner,
        ], 201);
    }

    public function show(Request $request, string $id)
    {
        return response()->json([
            'data' => $this->authorizedScannerQuery($request)->findOrFail($id),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $scanner = $this->authorizedScannerQuery($request)->findOrFail($id);
        SiteContext::authorizeWrite($request, $scanner->site);

        $validated = $this->validatePayload($request, true);
        $validated['date_of_inventory'] = Carbon::parse($validated['date_of_inventory'])->toDateString();
        $validated['site'] = $this->resolveSite($request);

        $scanner->update($validated);

        return response()->json([
            'message' => 'Scanner updated successfully.',
            'data' => $scanner->fresh(),
        ]);
    }

    public function destroy(Request $request, string $id)
    {
        $scanner = $this->authorizedScannerQuery($request)->findOrFail($id);
        SiteContext::authorizeWrite($request, $scanner->site);

        $scanner->delete();

        return response()->json([
            'message' => 'Scanner deleted successfully.',
        ]);
    }

    private function validatePayload(Request $request, bool $isUpdate): array
    {
        $rules = [
            'item_name' => [$isUpdate ? 'sometimes' : 'required', 'string'],
            'scanner_code' => [$isUpdate ? 'sometimes' : 'required', 'string'],
            'asset_ho_number' => ['nullable', 'string'],
            'serial_number' => ['nullable', 'string'],
            'mac_address' => ['nullable', 'string'],
            'ip_address' => ['nullable', 'string'],
            'scanner_brand' => ['nullable', 'string'],
            'scanner_type' => ['nullable', 'string'],
            'division' => ['nullable', 'string'],
            'department' => ['nullable', 'string'],
            'location' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
            'note' => ['nullable', 'string'],
            'date_of_inventory' => [$isUpdate ? 'sometimes' : 'required', 'date'],
        ];

        return $request->validate($rules);
    }

    private function resolveSite(Request $request): string
    {
        $site = SiteContext::resolve($request) ?? 'HO';

        return $site === '' ? 'HO' : $site;
    }

    private function authorizedScannerQuery(Request $request)
    {
        $query = InvScanner::query();

        if (! SiteContext::canAccessAnySite($request)) {
            $site = $this->resolveSite($request);
            if ($site === 'HO') {
                $query->where(function ($builder) {
                    $builder->whereNull('site')->orWhere('site', 'HO');
                });
            } else {
                $query->where('site', $site);
            }
        }

        return $query;
    }
}
