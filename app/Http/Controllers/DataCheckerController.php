<?php

namespace App\Http\Controllers;

use App\Models\InvComputer;
use App\Models\UserAll;
use Illuminate\Http\Request;

class DataCheckerController extends Controller
{
    public function checkDuplicateSN()
    {

        $serialNumbers = [
            'PC-27Y8N5',
            'PC-27Y8NA',
            'PC-27Y8PC',
            'PC-27Y8N8',
            'PC-27Y8R5',
            'PC-27Y8Q2',
            'PC-27Y8NP',
            'PC-27Y8ME',
            'PC-27Y8MZ',
            'PC-27Y8MW',
            'PC-27Y8M4',
            'PC-27Y8MX',
            'PC-27X8MY',
            'PC-27Y812',
            'PC-27Y8Q6',
            'PC-27Y8M5',
            'PC-27Y8MT',
            'PC-27Y8MV',
            'PC-27Y8QZ',
            'JPF4SH3',
            'PC-27VADX',
            'PC-200ANG',
            'PC200APJ',
            'PC200ANK',
            'PC200AQ5',
            'PC27VAEG',
            'PC2KYCJ3',
            'PC2KYCG6',
            'PC2KYCGC',
            'PC2KYCJ7',
            'GM00RZ76',
            'GM00RZ6P',
            'GM024BTD',
            'GM024BTX',
            'GM02TX45',
            'GM02TX26',
            'GM02TX4C',
            'GM02TXAA',
            '2Q4ZRH3',
            'GM02TX2C',
            'GM02TX22',
            'GM098E6Y',
            'GM02TX3H',
            'GM02TX3G',
            'GM02TX3P',
            'GM02TX2L',

        ];

        $duplicateSNs = InvComputer::whereIn('serial_number', $serialNumbers)->pluck('serial_number')->toArray();

        // Ambil NRP yang tidak ada di database
        $missingSNs = array_diff($serialNumbers, $duplicateSNs);

        return response()->json([
            'total_existing' => count($duplicateSNs), // Jumlah NRP yang ditemukan
            'existing_SN' => $duplicateSNs, // Daftar NRP yang ditemukan
            'total_missing' => count($missingSNs), // Jumlah NRP yang tidak ditemukan
            'missing_SN' => array_values($missingSNs) // Daftar NRP yang tidak ditemukan
        ]);
    }

    public function checkMissingNRP()
    {
        $nrps = [
            '24005549',
            '220577',
            '220588',
            '23002769',
            '23002779',
            '220601',
            '220678',
            '220651',
            '230256',
            '220533',
            '220618',
            '20000702',
            '220576',
            '220653',
            '230250',

        ];

        $existingNRPs = UserAll::whereIn('nrp', $nrps)->pluck('nrp')->toArray();

        // Ambil NRP yang tidak ada di database
        $missingNRPs = array_diff($nrps, $existingNRPs);

        return response()->json([
            'total_existing' => count($existingNRPs), // Jumlah NRP yang ditemukan
            'existing_NRP' => $existingNRPs, // Daftar NRP yang ditemukan
            'total_missing' => count($missingNRPs), // Jumlah NRP yang tidak ditemukan
            'missing_NRP' => array_values($missingNRPs) // Daftar NRP yang tidak ditemukan
        ]);
    }
}
