<?php

namespace App\Http\Controllers;

use App\Models\HistoryLaptopLoan;
use App\Models\LaptopLoan;
use Illuminate\Http\Request;

class LaptopLoanController extends Controller
{
    public function index()
    {
        $laptopLoan = LaptopLoan::all();
        return response()->json($laptopLoan);
    }

    public function store(Request $request)
    {
        $laptopLoan_get_data = LaptopLoan::find($request->id);
        if (empty($laptopLoan_get_data)) {
            $laptopLoan = LaptopLoan::create($request->all());
            return response()->json($laptopLoan, 201);
        } else {
            $laptopLoan = LaptopLoan::firstWhere('id', $request->id)->update($request->all());
            return response()->json($laptopLoan, 201);
        }
    }

    public function get_history_loan()
    {
        $historyLaptopLoan = HistoryLaptopLoan::all();
        return response()->json($historyLaptopLoan);
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

        $historyLaptopLoan = HistoryLaptopLoan::create($request->all());
        return response()->json($historyLaptopLoan, 201);

        // Masukkan data satu per satu ke dalam model
        // $item = new Item();
        // $item->name = $request->name;
        // $item->description = $request->description;
        // $item->save();

        // Kembalikan respon JSON dengan pesan sukses
        // return response()->json(['message' => 'Item berhasil ditambahkan!'], 201);
        return response()->json($historyLaptopLoan);
    }

    public function update_history_loan(Request $request)
    {
        // Validasi data input
        $request->validate([
            'date_of_return' => 'nullable|string',
            'remark' => 'nullable|string',
        ]);

        $history_laptop_loan_get_data = HistoryLaptopLoan::find($request->id);
        $historyLaptopLoan = HistoryLaptopLoan::firstWhere('id', $request->id)->update($request->all());
        return response()->json($historyLaptopLoan, 201);
    }

    public function show($id)
    {
        $laptopLoan = LaptopLoan::find($id);
        if (is_null($laptopLoan)) {
            return response()->json(['message' => 'Laptop Loan Data not found'], 404);
        }
        return response()->json($laptopLoan);
    }

    public function destroy($id)
    {
        $laptopLoan = LaptopLoan::find($id);
        if (is_null($laptopLoan)) {
            return response()->json(['message' => 'Laptop Loan Data not found'], 404);
        }
        $laptopLoan->delete();
        return response()->json(null, 204);
    }
}
