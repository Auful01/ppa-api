<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\pengajuanAksesUser;
use App\Models\User;
use Illuminate\Http\Request;

class PengajuanAksesApiController extends Controller
{
    public function index(Request $request)
    {
        $query = pengajuanAksesUser::query()->orderByDesc('created_at');

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('nrp_user', 'like', "%{$search}%")
                  ->orWhere('nama_user', 'like', "%{$search}%")
                  ->orWhere('dept', 'like', "%{$search}%")
                  ->orWhere('site', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', strtoupper($request->string('status')));
        }

        return response()->json(['data' => $query->get()]);
    }

    public function show(int $id)
    {
        return response()->json([
            'data' => pengajuanAksesUser::findOrFail($id),
        ]);
    }

    public function approve(Request $request, int $id)
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'role' => ['required', 'string'],
        ]);

        $pengajuan = pengajuanAksesUser::findOrFail($id);

        if ($pengajuan->status === 'DONE') {
            return response()->json(['message' => 'Request already approved.'], 422);
        }

        $pengajuan->update(['status' => 'DONE']);

        User::where('id', $pengajuan->id_user)->update([
            'role'      => $validated['role'],
            'ict_group' => 'Y',
        ]);

        return response()->json([
            'message' => 'Role access approved.',
            'data'    => $pengajuan->fresh(),
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        $this->authorizeAdmin($request);

        $pengajuan = pengajuanAksesUser::findOrFail($id);
        $pengajuan->delete();

        return response()->json(['message' => 'Record deleted.']);
    }

    private function authorizeAdmin(Request $request): void
    {
        $allowed = ['ict_developer', 'ict_ho', 'ict_bod'];

        if (! in_array($request->user()?->role, $allowed, true)) {
            abort(403, 'You do not have permission to perform this action.');
        }
    }
}
