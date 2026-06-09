<?php

namespace App\Http\Controllers;

use App\Models\ComputerLoan;
use App\Models\HistoryComputerLoan;
use Illuminate\Http\Request;

class ComputerLoanController extends Controller
{
    public function index()
    {
        $computerLoan = ComputerLoan::all();
        return response()->json($computerLoan);
    }

    public function store(Request $request)
    {
        $computerLoan_get_data = ComputerLoan::find($request->id);
        if (empty($computerLoan_get_data)) {
            $computerLoan = ComputerLoan::create($request->all());
            return response()->json($computerLoan, 201);
        } else {
            $computerLoan = ComputerLoan::firstWhere('id', $request->id)->update($request->all());
            return response()->json($computerLoan, 201);
        }
    }

    public function get_history_loan()
    {
        $historyComputerLoan = HistoryComputerLoan::all();
        return response()->json($historyComputerLoan);
    }

    public function store_history_loan(Request $request)
    {
        // Validasi data input
        $request->validate([
            'inventory_number' => 'required|string|max:255',
            'asset_number' => 'nullable|string',
            'nik' => 'nullable|string',
            'name' => 'nullable|string',
            'position' => 'nullable|string',
            'department' => 'nullable|string',
            'date_of_loan' => 'nullable|string',
            'link' => 'nullable|string',
            'pic' => 'nullable|string',
        ]);

        $historyComputerLoan = HistoryComputerLoan::create($request->all());
        return response()->json($historyComputerLoan, 201);
        
        // Masukkan data satu per satu ke dalam model
        // $item = new Item();
        // $item->name = $request->name;
        // $item->description = $request->description;
        // $item->save();

        // Kembalikan respon JSON dengan pesan sukses
        // return response()->json(['message' => 'Item berhasil ditambahkan!'], 201);
    }

    public function update_history_loan(Request $request)
    {
        // Validasi data input
        $request->validate([
            'date_of_return' => 'nullable|string',
            'remark' => 'nullable|string',
        ]);

        $history_laptop_loan_get_data = HistoryComputerLoan::find($request->id);
        $historyComputerLoan = HistoryComputerLoan::firstWhere('id', $request->id)->update($request->all());
        return response()->json($historyComputerLoan, 201);
    }

    public function show($id)
    {
        $computerLoan = ComputerLoan::find($id);
        if (is_null($computerLoan)) {
            return response()->json(['message' => 'Computers Data not found'], 404);
        }
        return response()->json($computerLoan);
    }

    public function destroy($id)
    {
        $computerLoan = ComputerLoan::find($id);
        if (is_null($computerLoan)) {
            return response()->json(['message' => 'Computers Data not found'], 404);
        }
        $computerLoan->delete();
        return response()->json(null, 204);
    }
}
