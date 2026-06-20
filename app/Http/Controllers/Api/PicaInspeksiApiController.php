<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InspeksiComputer;
use App\Models\InspeksiLaptop;
use App\Models\InspeksiMobileTower;
use App\Models\InspeksiPrinter;
use App\Models\PicaInspeksi;
use App\Models\User;
use App\Services\ImageOptimizerService;
use App\Support\Api\SiteContext;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PicaInspeksiApiController extends Controller
{
    public function meta(Request $request)
    {
        $site = SiteContext::resolve($request);
        $crew = $site !== 'HO'
            ? User::whereIn('role', ['ict_technician', 'ict_group_leader'])->where('site', $site)
            : User::where('role', 'ict_ho')->where('site', 'HO');

        return response()->json([
            'site' => $site,
            'crew' => $crew->pluck('name')->map(fn ($name) => ['name' => $name])->values(),
        ]);
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'device_type' => ['required', 'string', 'in:Laptop,Computer,Mobile Tower,Printer'],
            'site' => ['required', 'string'],
            'status_pica' => ['nullable', 'string'],
            'startDate' => ['nullable', 'date'],
            'endDate' => ['nullable', 'date'],
        ]);

        $deviceType = $validated['device_type'];
        $site = SiteContext::resolve($request);
        $statusPica = $validated['status_pica'] ?? null;
        $startDate = $validated['startDate'] ?? null;
        $endDate = $validated['endDate'] ?? null;

        $query = match ($deviceType) {
            'Laptop' => InspeksiLaptop::where('site', $site)->whereNotNull('findings_image')->whereHas('inventory')->whereHas('inventory.pengguna')->with('inventory.pengguna'),
            'Computer' => InspeksiComputer::where('site', $site)->whereNotNull('findings_image')->whereHas('computer')->whereHas('computer.pengguna')->with('computer.pengguna'),
            'Printer' => InspeksiPrinter::where('site', $site)->whereNotNull('findings_image')->whereHas('printer')->with('printer'),
            'Mobile Tower' => PicaInspeksi::where('site', $site)->whereHas('inspeksiMt')->whereHas('inspeksiMt.mt')->with(['inspeksiMt', 'inspeksiMt.mt']),
        };

        if ($startDate && $endDate) {
            if ($deviceType === 'Mobile Tower') {
                $query->whereBetween('created_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()]);
            } else {
                $query->whereBetween('created_date', [$startDate, $endDate]);
            }
        }

        if ($statusPica) {
            $query->where($deviceType === 'Mobile Tower' ? 'status_pica' : 'findings_status', $statusPica);
        }

        return response()->json([
            'data' => $query->get(),
        ]);
    }

    public function show(Request $request, string $id)
    {
        $data = $this->resolveInspection($id);

        abort_if(! $data, 404, 'Data inspeksi tidak ditemukan');
        $this->authorizeRecordSite($request, $data);

        return response()->json([
            'data' => $data,
        ]);
    }

    public function update(Request $request, string $id)
    {
        SiteContext::authorizeWrite($request, SiteContext::resolve($request));

        $params = $request->all();
        $data = [
            'findings' => $params['temuan'] ?? null,
            'findings_action' => $params['tindakan'] ?? null,
            'due_date' => $params['due_date'] ?? null,
            'findings_status' => $params['findings_status'] ?? null,
            'remarks' => $params['remark'] ?? null,
            'inspector' => $params['inspector'] ?? null,
            'last_edited_by' => $request->user()->nrp,
        ];

        $dataPica = [
            'temuan' => $params['temuan'] ?? null,
            'tindakan' => $params['tindakan'] ?? null,
            'due_date' => $params['due_date'] ?? null,
            'remark' => $params['remark'] ?? null,
            'status_pica' => $params['findings_status'] ?? null,
            'close_by' => $request->user()->name,
            'site' => $request->user()->site,
        ];

        $existing = $this->resolveInspection($id);
        abort_if(! $existing, 404, 'Data inspeksi tidak ditemukan');
        $this->authorizeRecordSite($request, $existing);

        if ($request->hasFile('image_temuan')) {
            $data['findings_image'] = url('storage/' . ImageOptimizerService::storeAndOptimize($request->file('image_temuan'), 'images'));
            $dataPica['foto_temuan'] = $data['findings_image'];
        }

        if ($request->hasFile('image_tindakan')) {
            $data['action_image'] = url('storage/' . ImageOptimizerService::storeAndOptimize($request->file('image_tindakan'), 'images'));
            $dataPica['foto_tindakan'] = $data['action_image'];
        }

        if ($request->hasFile('image_inspeksi')) {
            $data['inspection_image'] = url('storage/' . ImageOptimizerService::storeAndOptimize($request->file('image_inspeksi'), 'images'));
        }

        if (($params['device_type'] ?? null) === 'Mobile Tower') {
            PicaInspeksi::updateOrCreate(['id' => $id], $dataPica);
        } else {
            PicaInspeksi::updateOrCreate(['inspeksi_id' => $id], $dataPica);
        }

        if ($record = InspeksiLaptop::find($id)) {
            $this->authorizeRecordSite($request, $record);
            $record->update($data);
        } elseif ($record = InspeksiPrinter::find($id)) {
            $this->authorizeRecordSite($request, $record);
            $record->update($data);
        } elseif ($record = InspeksiComputer::find($id)) {
            $this->authorizeRecordSite($request, $record);
            $record->update($data);
        } elseif ($record = PicaInspeksi::with('inspeksiMt.mt')->find($id)) {
            $this->authorizeRecordSite($request, $record);
            $record->inspeksiMt?->mt?->update([
                'inspection_remark' => $params['remark'] ?? null,
            ]);
        } else {
            abort(404, 'Data inspeksi tidak ditemukan');
        }

        return response()->json([
            'message' => 'PICA updated successfully.',
        ]);
    }

    private function resolveInspection(string $id): mixed
    {
        if ($data = InspeksiLaptop::with('inventory.pengguna')->find($id)) {
            $inv = $data->inventory;
            $data->device_code = $inv->laptop_code ?? '-';
            $data->device_name = strtoupper($inv->laptop_name ?? '-');
            $data->device_sn = $inv->serial_number ?? '-';
            $data->device_condition = $inv->condition ?? '-';
            $data->device_asset_ho = $inv->number_asset_ho ?? '-';
            $data->device_spesifikasi = $inv->spesifikasi ?? '-';
            $data->device_status_inventory = $inv->status ?? '-';
            $data->device_note = $inv->note ?? '-';
            $data->device_pengguna = $inv->pengguna->username ?? '-';
            $data->device_dept = $inv->pengguna->department ?? '-';
            $data->device_ip_address = $inv->ip_address ?? '-';
            $data->device_location = $inv->location ?? '-';
            $data->device_type = 'Laptop';
            return $data;
        }

        if ($data = InspeksiPrinter::with('printer')->find($id)) {
            $prt = $data->printer;
            $data->device_code = $prt->printer_code ?? '-';
            $data->device_name = strtoupper($prt->item_name ?? '-');
            $data->device_sn = $prt->serial_number ?? '-';
            $data->device_condition = $data->condition ?? '-';
            $data->device_asset_ho = $prt->asset_ho_number ?? '-';
            $data->device_spesifikasi = '-';
            $data->device_status_inventory = $prt->status ?? '-';
            $data->device_note = $prt->note ?? '-';
            $data->device_pengguna = $prt->division ?? '-';
            $data->device_dept = $prt->pengguna->department ?? '-';
            $data->device_ip_address = $prt->ip_address ?? '-';
            $data->device_location = $prt->location ?? '-';
            $data->device_type = 'Printer';
            return $data;
        }

        if ($data = PicaInspeksi::with('inspeksiMt.mt')->find($id)) {
            $mt = $data->inspeksiMt;
            $data->device_code = $mt->mt->inventory_number ?? '-';
            $data->device_name = strtoupper($mt->mt->mt_code ?? '-');
            $data->device_sn = '-';
            $data->device_condition = $mt->condition ?? '-';
            $data->device_asset_ho = '-';
            $data->device_spesifikasi = '-';
            $data->device_status_inventory = $mt->mt->status ?? '-';
            $data->device_note = $mt->mt->note ?? '-';
            $data->device_pengguna = '-';
            $data->device_dept = '-';
            $data->device_ip_address = '-';
            $data->device_location = $mt->mt->location ?? '-';
            $data->device_type = 'Mobile Tower';
            $data->findings = $data->temuan;
            $data->findings_image = $data->foto_temuan;
            $data->findings_action = $data->tindakan;
            $data->action_image = $data->foto_tindakan;
            $data->findings_status = $data->status_pica;
            $data->remarks = $data->remark;
            $data->inspector = $mt->pic;
            $data->inspection_image = $mt->inspection_image;
            return $data;
        }

        if ($data = InspeksiComputer::with('computer.pengguna')->find($id)) {
            $cmp = $data->computer;
            $data->device_code = $cmp->computer_code ?? '-';
            $data->device_name = strtoupper($cmp->computer_name ?? '-');
            $data->device_sn = $cmp->serial_number ?? '-';
            $data->device_condition = $cmp->condition ?? '-';
            $data->device_asset_ho = $cmp->number_asset_ho ?? '-';
            $data->device_spesifikasi = $cmp->spesifikasi ?? '-';
            $data->device_status_inventory = $cmp->status ?? '-';
            $data->device_note = $cmp->note ?? '-';
            $data->device_pengguna = $cmp->pengguna->username ?? '-';
            $data->device_dept = $cmp->pengguna->department ?? '-';
            $data->device_ip_address = $cmp->ip_address ?? '-';
            $data->device_location = $cmp->location ?? '-';
            $data->device_type = 'Computer';
            return $data;
        }

        return null;
    }

    private function authorizeRecordSite(Request $request, mixed $record): void
    {
        if (SiteContext::canAccessAnySite($request)) {
            return;
        }

        $recordSite = strtoupper((string) ($record->site ?? $record->inspeksiMt?->site ?? null));
        abort_if($recordSite !== SiteContext::resolve($request), 404);
    }
}
