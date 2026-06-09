<?php

namespace App\Http\Controllers;

use App\Models\InspeksiMobileTower;
use App\Models\InvMobileTower;
use App\Models\KategoriInspeksi;
use App\Models\PicaInspeksi;
use App\Models\ScheduleMobileTower;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class InspeksiMobileTowerSksController extends Controller
{
    public function index(Request $request)
    {
        $site = 'SKS';
        $bulanNow = $request->input('month', now()->month);
        $yearNow = $request->input('year', now()->year);

        $inspeksi_mobile_tower = InspeksiMobileTower::with('mt')->where('site', $site)->where('month', $bulanNow)
            ->where('year', $yearNow)->get();
        // dd($inspeksiMobileTower);

        $role = auth()->user()->role;

        $bulan_sekarang = now()->month;
        $tahun_sekarang = now()->year;

        return Inertia::render(
            'Inspeksi/SiteSks/MobileTower/InspeksiMobileTowerView',
            [
                'inspeksiMobileTowerx' => $inspeksi_mobile_tower,
                'site' => $site,
                'role' => $role,
                'monthNow' => (int) $bulanNow,
                'yearNow' => (int) $yearNow,
                'bulan_sekarang' => (int) $bulan_sekarang,
                'tahun_sekarang' => (int) $tahun_sekarang,
            ]
        );
    }

    public function process($id)
    {
        $dataInspeksix = InspeksiMobileTower::find($id);
        if (empty($dataInspeksix)) {
            abort(404, 'Data not found');
        }
        $site = $dataInspeksix->site;

        $mobileTowerx = InvMobileTower::where('id', $dataInspeksix->inv_mt_id)->first();
        if (empty($mobileTowerx)) {
            abort(404, 'Data not found');
        }

        $penggunax = User::whereIn('role', ['ict_technician', 'ict_group_leader'])->where('site', $site)->pluck('name')->map(function ($name) {
            return ['name' => $name];
        })->toArray();

        $dataKategori = KategoriInspeksi::where('kategori_inspeksi', 'MT')->where('parent', 0)->orderBy('urutan', 'ASC')->get();
        $subDataKategori = KategoriInspeksi::where('kategori_inspeksi', 'MT')->where('parent', '!=', 0)->orderBy('urutan', 'ASC')->get();

        $totalItems = $subDataKategori->count();



        return Inertia::render('Inspeksi/SiteSks/MobileTower/InspeksiMobileTowerCreate', ['dataInspeksi' => $dataInspeksix, 'pengguna' => $penggunax, 'mobileTower' => $mobileTowerx, 'dataKategori' => $dataKategori, 'subDataKategori' => $subDataKategori, 'totalItems' => $totalItems,]);
    }

    public function store(Request $request)
    {
        // find existing record
        $inspeksi = InspeksiMobileTower::findOrFail($request->input('id'));
        $id_mt = $inspeksi->inv_mt_id;
        $site = $inspeksi->site;

        // checklist + remarks
        $checklistResults = $request->input('checklist_results_list', []);
        $remarksInput = $request->input('list_results_remark', []);

        $finalRemarks = [];
        foreach ($checklistResults as $key => $value) {
            $finalRemarks[$key] = $remarksInput[$key] ?? "-";
        }

        // handle lampiran foto inspeksi
        $lampiranPath = null;
        if ($request->hasFile('lampiran')) {

            $lampiran = $request->file('lampiran');
            $destinationPath = 'images/';
            $path_document_image = $lampiran->store('images', 'public');
            $new_path_document_image = $path_document_image;
            $lampiran->move($destinationPath, $new_path_document_image);

            $lampiranPath = url($new_path_document_image);
        }

        // handle temuan (findings)
        $temuanList = $request->input('temuan', []);
        // dd($temuanList);
        $findings = [];
        $findingsStatus = [];
        $findingsAction = [];
        $findingsImage = [];
        $actionImage = [];

        foreach ($temuanList as $index => $temuan) {

            $isTemuanEmpty = empty($temuan['temuan']);
            $isTindakEmpty = empty($temuan['tindak_lanjut']);

            if ($isTemuanEmpty && $isTindakEmpty) {
                continue;
            }

            $findings[] = $temuan['temuan'] ?? null;
            $findingsStatus[] = $temuan['status'] ?? null;
            $findingsAction[] = $temuan['tindak_lanjut'] ?? null;

            // Finding Image
            if ($request->hasFile("temuan.$index.temuan_image")) {

                $file = $request->file("temuan.$index.temuan_image");
                $destinationPath = 'images/';
                $path_document_image = $file->store('images', 'public');
                $new_path_document_image = $path_document_image;
                $file->move($destinationPath, $new_path_document_image);

                $findingsImage[] = url($new_path_document_image);
            } else {
                $findingsImage[] = null;
            }

            // Action Image
            if ($request->hasFile("temuan.$index.tindak_image")) {

                $file = $request->file("temuan.$index.tindak_image");
                $destinationPath = 'images/';
                $path_document_image = $file->store('images', 'public');
                $new_path_document_image = $path_document_image;
                $file->move($destinationPath, $new_path_document_image);

                $actionImage[] = url($new_path_document_image);
            } else {
                $actionImage[] = null;
            }
        }

        // prepare update data
        $updateData = [
            'condition' => $request->input('condition'),
            'worthiness' => $request->input('kelayakan'),
            'device_status' => $request->input('inventory_status'),
            'remarks' => $request->input('remark'),
            'pic' => $request->input('pic'),
            'crew' => json_encode($request->input('crew', [])),
            'lokasi' => $request->input('lokasi'),
            'detail_lokasi' => $request->input('detail_lokasi'),
            'due_date' => $request->input('due_date') ? Carbon::parse($request->input('due_date'))->toDateString() : null,
            'checklist_results_list' => json_encode($checklistResults),
            'list_results_remark' => json_encode($finalRemarks),
            'inspection_status' => 'Y',
            'inspector' => Auth::user()->name,
            'site' => $site,
            'created_date' => Carbon::today(),
            'inspection_at' => Carbon::now(),
            'findings' => !empty($findings) ? json_encode($findings, JSON_UNESCAPED_SLASHES) : null,
            'findings_image' => !empty($findingsImage) ? json_encode($findingsImage, JSON_UNESCAPED_SLASHES) : null,
            'findings_status' => !empty($findingsStatus) ? json_encode($findingsStatus, JSON_UNESCAPED_SLASHES) : null,
            'findings_action' => !empty($findingsAction) ? json_encode($findingsAction, JSON_UNESCAPED_SLASHES) : null,
            'action_image' => !empty($actionImage) ? json_encode($actionImage, JSON_UNESCAPED_SLASHES) : null,
        ];

        if ($lampiranPath) {
            $updateData['inspection_image'] = $lampiranPath;
        }

        // dd($updateData);

        $inspeksi->update($updateData);


        $updateDataInvMt = [
            'location' => $request->input('lokasi'),
            'detail_location' => $request->input('detail_lokasi'),
            'status' => $request->input('inventory_status'),
            'inspection_remark' => $request->input('remark'),
        ];

        $mt = InvMobileTower::findOrFail($id_mt);
        $mt->update($updateDataInvMt);

        // =======================================================
        // ADD PICA ENTRY WHEN FINDING STATUS != 'CLOSED'
        // =======================================================
        $currentDate = Carbon::now();
        $year = $currentDate->format('Y');

        $maxId = InspeksiMobileTower::where('site', $site)->where('year', $year)->max('pica_number');

        if (is_null($maxId)) {
            $maxId = 0;
        }
        $no_pica = $maxId + 1;

        foreach ($temuanList as $i => $temuan) {
            if (!empty($temuan['temuan']) && strtoupper($temuan['status']) != 'CLOSED') {

                $dataPica = [
                    'pica_number' => $no_pica,
                    'inspeksi_id' => $inspeksi->id,
                    'temuan' => $temuan['temuan'],
                    'tindakan' => $temuan['tindak_lanjut'] ?? '',
                    'due_date' => $request->input('due_date') ? Carbon::parse($request->input('due_date'))->toDateString() : null,
                    'remark' => $request->input('remark'),
                    'status_pica' => $temuan['status'],
                    'site' => $site,
                    'close_by' => auth()->user()->name,
                    'foto_temuan' => $findingsImage[$i] ?? null,
                    'foto_tindakan' => $actionImage[$i] ?? null,
                ];

                PicaInspeksi::create($dataPica);

                $no_pica++;
            }
        }
        // =======================================================

        $currentDate = Carbon::now();
        $year = $currentDate->format('Y');
        $month = $currentDate->format('m');

        $dataSchedule = ['actual_inspection' => Carbon::now()->format('Y-m-d H:i:s')];

        $dataInspeksix = InspeksiMobileTower::find($request->id);

        if (empty($dataInspeksix)) {
        } else {
            ScheduleMobileTower::where('id_mobile_tower', $dataInspeksix->inv_mt_id)->where('tahun', $year)->where('bulan', $month)->first()->update($dataSchedule);
        }

        return redirect()->route('inspeksiMobileTowerSks.page');
    }

    public function approval(Request $request)
    {
        $bulanNow = $request->input('month', now()->month);
        $yearNow = $request->input('year', now()->year);
        $user = Auth::user();

        // Cek apakah user memiliki role 'ict_group_leader'
        if ($user->role !== 'ict_group_leader') {
            return response()->json([
                'success' => false,
                'message' => 'Maaf, Anda tidak dapat melakukan approval dikarenakan role Anda bukan GROUP LEADER!',
            ], 403);
        }

        $site = 'SKS';
        if (!$site) {
            return response()->json([
                'success' => false,
                'message' => 'Site user tidak ditemukan.',
            ], 400);
        }

        $dataApproval = [
            'approved_by' => $user->name,
            'status_approval' => 'approved',
        ];

        $updateCount = InspeksiMobileTower::where('site', $site)
            ->where('month', $bulanNow)
            ->where('year', $yearNow)
            ->where('inspection_status', 'Y')
            ->update($dataApproval);

        return response()->json([
            'success' => true,
            'message' => "$updateCount data inspeksi MT untuk site $site telah di-approve.",
        ]);
    }


    public function detail($id)
    {
        $inspeksi_mt = InspeksiMobileTower::with('mt')->where('inspeksi_mobile_towers.id', $id)->first();

        if (empty($inspeksi_mt)) {
            abort(404, 'Data not found');
        }

        $dataKategori = KategoriInspeksi::where('kategori_inspeksi', 'MT')->where('parent', 0)->orderBy('urutan', 'ASC')->get();
        $subDataKategori = KategoriInspeksi::where('kategori_inspeksi', 'MT')->where('parent', '!=', 0)->orderBy('urutan', 'ASC')->get();

        return Inertia::render('Inspeksi/SiteSks/MobileTower/InspeksiMobileTowerDetail', [
            'inspeksi' => $inspeksi_mt,
            'dataKategori' => $dataKategori,
            'subDataKategori' => $subDataKategori,
        ]);
    }

    public function edit($id)
    {
        $dataInspeksix = InspeksiMobileTower::find($id);
        if (empty($dataInspeksix)) {
            abort(404, 'Data not found');
        }
        $site = $dataInspeksix->site;

        $mobileTowerx = InvMobileTower::where('id', $dataInspeksix->inv_mt_id)->first();
        if (empty($mobileTowerx)) {
            abort(404, 'Data not found');
        }

        $penggunax = User::whereIn('role', ['ict_technician', 'ict_group_leader'])->where('site', $site)->pluck('name')->map(function ($name) {
            return ['name' => $name];
        })->toArray();

        $dataKategori = KategoriInspeksi::where('kategori_inspeksi', 'MT')->where('parent', 0)->orderBy('urutan', 'ASC')->get();
        $subDataKategori = KategoriInspeksi::where('kategori_inspeksi', 'MT')->where('parent', '!=', 0)->orderBy('urutan', 'ASC')->get();

        $totalItems = $subDataKategori->count();


        return Inertia::render('Inspeksi/SiteSks/MobileTower/InspeksiMobileTowerEdit', ['dataInspeksi' => $dataInspeksix, 'pengguna' => $penggunax, 'mobileTower' => $mobileTowerx, 'dataKategori' => $dataKategori, 'subDataKategori' => $subDataKategori, 'totalItems' => $totalItems,]);
    }

    public function update(Request $request)
    {
        // dd($request->all(), $request->file());
        $inspeksi = InspeksiMobileTower::findOrFail($request->input('id'));
        $id_mt = $inspeksi->inv_mt_id;
        $site = $inspeksi->site;

        // checklist + remarks
        $checklistResults = $request->input('checklist_results_list', []);
        $remarksInput = $request->input('list_results_remark', []);
        $finalRemarks = [];
        foreach ($checklistResults as $key => $value) {
            $finalRemarks[$key] = $remarksInput[$key] ?? "-";
        }

        $lampiranPath = null;
        $lampiranFiles = $request->file('lampiran', []);

        if ($request->hasFile('lampiran')) {
            $destinationPath = 'images/';
            $path_document_image = $lampiranFiles->store('images', 'public');
            $new_path_document_image = $path_document_image;
            $lampiranFiles->move($destinationPath, $new_path_document_image);
            $lampiranPath = url($new_path_document_image);
        }

        $temuanList = $request->input('temuan', []);
        $temuanFiles = $request->file('temuan', []);

        // dd($temuanList);

        $findings = [];
        $findingsStatus = [];
        $findingsAction = [];
        $findingsImage = [];
        $actionImage = [];

        foreach ($temuanList as $index => $temuan) {

            $isTemuanEmpty = empty($temuan['temuan']);
            $isTindakEmpty = empty($temuan['tindak_lanjut']);

            if ($isTemuanEmpty && $isTindakEmpty) {
                continue;
            }

            $findings[] = $temuan['temuan'] ?? null;
            $findingsStatus[] = $temuan['status'] ?? null;
            $findingsAction[] = $temuan['tindak_lanjut'] ?? null;

            $temuanFile = $temuanFiles[$index]['temuan_image'] ?? null;
            $tindakFile = $temuanFiles[$index]['tindak_image'] ?? null;

            // Finding Image
            if ($request->hasFile("temuan.$index.temuan_image")) {

                $destinationPath = 'images/';
                $path_document_image = $temuanFile->store('images', 'public');
                $new_path_document_image = $path_document_image;
                $temuanFile->move($destinationPath, $new_path_document_image);

                $findingsImage[] = url($new_path_document_image);
            } else {
                $findingsImage[] = $temuan['temuan_image']['preview'] ?? null;
            }

            // Action Image
            if ($request->hasFile("temuan.$index.tindak_image")) {

                $destinationPath = 'images/';
                $path_document_image = $tindakFile->store('images', 'public');
                $new_path_document_image = $path_document_image;
                $tindakFile->move($destinationPath, $new_path_document_image);

                $actionImage[] = url($new_path_document_image);
            } else {
                $actionImage[] = $temuan['tindak_image']['preview'] ?? null;
            }
        }

        // ======================================================
        // 🔹 Prepare Update Data
        // ======================================================
        $updateData = [
            'condition' => $request->input('condition'),
            'worthiness' => $request->input('kelayakan'),
            'device_status' => $request->input('inventory_status'),
            'remarks' => $request->input('remark'),
            'pic' => $request->input('pic'),
            'crew' => json_encode($request->input('crew', [])),
            'lokasi' => $request->input('lokasi'),
            'detail_lokasi' => $request->input('detail_lokasi'),
            'due_date' => $request->input('due_date') ? Carbon::parse($request->input('due_date'))->toDateString() : null,
            'checklist_results_list' => json_encode($checklistResults),
            'list_results_remark' => json_encode($finalRemarks),
            'inspection_status' => 'Y',
            'inspector' => Auth::user()->name,
            'site' => $site,
            'created_date' => Carbon::today(),
            'inspection_at' => Carbon::now(),
            'findings' => !empty($findings) ? json_encode($findings, JSON_UNESCAPED_SLASHES) : null,
            'findings_image' => !empty($findingsImage) ? json_encode($findingsImage, JSON_UNESCAPED_SLASHES) : null,
            'findings_status' => !empty($findingsStatus) ? json_encode($findingsStatus, JSON_UNESCAPED_SLASHES) : null,
            'findings_action' => !empty($findingsAction) ? json_encode($findingsAction, JSON_UNESCAPED_SLASHES) : null,
            'action_image' => !empty($actionImage) ? json_encode($actionImage, JSON_UNESCAPED_SLASHES) : null,
        ];

        if ($lampiranPath !== null) {
            $updateData['inspection_image'] = $lampiranPath;
        }

        // dd($updateData);

        $inspeksi->update($updateData);

        // update InvMobileTower
        $updateDataInvMt = [
            'location' => $request->input('lokasi'),
            'detail_location' => $request->input('detail_lokasi'),
            'status' => $request->input('inventory_status'),
            'inspection_remark' => $request->input('remark'),
        ];

        $mt = InvMobileTower::findOrFail($id_mt);
        $mt->update($updateDataInvMt);

        // ======================================================
        // Add PICA Entry When Finding Status != 'CLOSED'
        // ======================================================
        $currentDate = Carbon::now();
        $year = $currentDate->format('Y');
        $maxId = InspeksiMobileTower::where('site', $site)
            ->where('year', $year)
            ->max('pica_number');
        $no_pica = ($maxId ?? 0) + 1;

        foreach ($temuanList as $i => $temuan) {
            if (!empty($temuan['temuan']) && strtoupper($temuan['status']) != 'CLOSED') {
                $dataPica = [
                    'pica_number' => $no_pica,
                    'inspeksi_id' => $inspeksi->id,
                    'temuan' => $temuan['temuan'],
                    'tindakan' => $temuan['tindak_lanjut'] ?? '',
                    'due_date' => $request->input('due_date')
                        ? Carbon::parse($request->input('due_date'))->toDateString()
                        : null,
                    'remark' => $request->input('remark'),
                    'status_pica' => $temuan['status'],
                    'site' => $site,
                    'close_by' => auth()->user()->name,
                    'foto_temuan' => $findingsImage[$i] ?? null,
                    'foto_tindakan' => $actionImage[$i] ?? null,
                ];

                PicaInspeksi::create($dataPica);
                $no_pica++;
            }
        }

        return redirect()->route('inspeksiMobileTowerSks.page');
    }


    public function show($id)
    {
        $inspeksiMobileTower = InspeksiMobileTower::find($id);
        if (is_null($inspeksiMobileTower)) {
            return response()->json(['message' => 'Data not found'], 404);
        }
        return response()->json($inspeksiMobileTower);
    }

}
