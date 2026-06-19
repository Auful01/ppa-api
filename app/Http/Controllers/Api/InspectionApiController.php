<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PicaInspeksi;
use App\Support\Api\InspectionRegistry;
use App\Support\Api\SiteContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class InspectionApiController extends Controller
{
    public function index(Request $request, string $type)
    {
        $config = InspectionRegistry::get($type);
        $site = SiteContext::resolve($request);
        $model = $config['model'];

        $query = $model::query()->with($config['relations'] ?? []);
        SiteContext::apply($query, $config['site_column'], $site);

        if ($request->filled('year')) {
            $query->where('year', $request->integer('year'));
        }

        if ($request->filled('month')) {
            $query->where('month', $request->integer('month'));
        }

        if ($request->filled('triwulan')) {
            $query->where('triwulan', $request->integer('triwulan'));
        }

        if ($request->filled('inspection_status')) {
            $query->where('inspection_status', $request->string('inspection_status'));
        }

        return response()->json([
            'type' => $type,
            'site' => $site,
            'data' => $query->latest('created_at')->paginate((int) $request->integer('per_page', 25)),
        ]);
    }

    public function show(Request $request, string $type, string $id)
    {
        $config = InspectionRegistry::get($type);

        return response()->json([
            'type' => $type,
            'data' => $this->authorizedInspectionQuery($request, $config)
                ->with($config['relations'] ?? [])
                ->findOrFail($id),
        ]);
    }

    public function update(Request $request, string $type, string $id)
    {
        $config = InspectionRegistry::get($type);
        /** @var Model $inspection */
        $inspection = $this->authorizedInspectionQuery($request, $config)->findOrFail($id);
        SiteContext::authorizeWrite($request, $inspection->getAttribute($config['site_column']));

        $payload = collect($request->only($inspection->getFillable()))
            ->except(['site'])
            ->toArray();

        foreach (['findings_image', 'action_image', 'inspection_image', 'nozle_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $payload[$fileField] = $request->file($fileField)->store('images', 'public');
            }
        }

        if (in_array('last_edited_by', $inspection->getFillable(), true) && $request->user()) {
            $payload['last_edited_by'] = $request->user()->nrp;
        }

        $inspection->update($payload);

        // Mirror web behavior: propagate inventory_status back to parent inventory record
        if (
            isset($payload['inventory_status'])
            && isset($config['inventory_model'])
            && isset($config['inventory_fk'])
        ) {
            $inventoryId = $inspection->getAttribute($config['inventory_fk']);
            if ($inventoryId) {
                $config['inventory_model']::where('id', $inventoryId)
                    ->update(['status' => $payload['inventory_status']]);
            }
        }

        // Mirror web behavior (Inspeksi{Computer,Laptop,Printer,MobileTower}Controller@store):
        // whenever the inspection carries a finding, a PICA record is auto-created/updated
        // (keyed on inspeksi_id) so the finding shows up on the PICA Inspeksi page without a
        // separate manual entry. No finding submitted ⇒ no PICA, exactly like `if ($request->findings)`.
        if ($request->filled('findings')) {
            PicaInspeksi::updateOrCreate(
                ['inspeksi_id' => $inspection->id],
                [
                    'pica_number'  => $inspection->getAttribute('pica_number') ?? '0',
                    'temuan'       => $inspection->getAttribute('findings'),
                    'tindakan'     => $inspection->getAttribute('findings_action'),
                    'due_date'     => $inspection->getAttribute('due_date'),
                    'remark'       => $inspection->getAttribute('remarks'),
                    'status_pica'  => $inspection->getAttribute('findings_status'),
                    'foto_temuan'  => $inspection->getAttribute('findings_image'),
                    'foto_tindakan' => $inspection->getAttribute('action_image'),
                    'close_by'     => $request->user()?->name,
                    'site'         => $inspection->getAttribute($config['site_column']),
                ]
            );
        }

        return response()->json([
            'message' => 'Inspection updated successfully.',
            'data' => $inspection->fresh(),
        ]);
    }

    private function authorizedInspectionQuery(Request $request, array $config)
    {
        $model = $config['model'];
        $query = $model::query();

        if (! SiteContext::canAccessAnySite($request)) {
            SiteContext::apply($query, $config['site_column'], SiteContext::resolve($request));
        }

        return $query;
    }
}
