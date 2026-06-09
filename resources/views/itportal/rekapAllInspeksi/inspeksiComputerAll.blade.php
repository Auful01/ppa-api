<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Form Laporan Inspeksi PC Periode Triwulan {{ $thisTriwulan }} - {{ $thisYear }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 20px;
            /* Biar ada jarak */
        }

        /* @page {
            margin: 20mm 10mm 20mm 10mm;
        } */

        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 30px;
            text-align: center;
            font-size: 10px;
            color: #555;
        }

        img.logo {
            display: block;
            width: 100%;
            height: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }

        .container {
            padding: 10px;
            border: 1px solid #ccc;
        }


        .title {
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 5px;
        }

        .subtitle {
            text-align: left;
            margin-bottom: 10px;
            font-size: 12px;
        }


        th,
        td {
            border: 1px solid black;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
            white-space: normal;
            font-size: 8px;
        }

        th {
            background-color: #0c0c64;
            color: white;
        }

        .text-left {
            text-align: left;
        }


        .footer-content {
            display: flex;
            justify-content: space-between;
            padding: 0 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
            <tr>
                <td style="border: none; padding: 0;" colspan="2">
                    <img src="{{ public_path('images/header_ppa.jpg') }}" alt="Logo"
                        style="width: 100%; height: auto;">
                </td>
            </tr>
        </table>

        <div class="title">FORM LAPORAN INSPEKSI PC</div>
        <table style="width: 100%; margin-bottom: 10px; border-collapse: collapse;">
            <tr>
                <td style="text-align: left; font-size: 9px !important; font-weight: bold; border: none;">
                    Periode Inspeksi: <span style="font-weight: normal;">Triwulan {{ $thisTriwulan }} -
                        {{ $thisYear }}</span>
                </td>
                <td style="text-align: right; font-weight: bold; border: none;">
                    {{-- PPA-{{ $site }}-F-COE-02D --}}
                </td>
            </tr>
        </table>
        <table>
            <thead>
                <tr>
                    <th rowspan="2" style="border: 1px solid #000;">No</th>
                    <th rowspan="2" style="border: 1px solid #000;">Nomor Unit</th>
                    <th rowspan="2" style="border: 1px solid #000;">Nomor Asset HO</th>
                    <th rowspan="2" style="border: 1px solid #000;">Tanggal Inspeksi</th>
                    <th rowspan="2" style="border: 1px solid #000;">Merek - Tipe</th>
                    <th rowspan="2" style="border: 1px solid #000;">Lokasi</th>
                    <th colspan="3" style="border: 1px solid #000;">Hardware</th>
                    <th colspan="8" style="border: 1px solid #000;">Software</th>
                    <th colspan="3" style="border: 1px solid #000;">Security</th>
                    <th rowspan="2" style="border: 1px solid #000;">Kondisi</th>
                    <th rowspan="2" style="border: 1px solid #000;">Status PC</th>
                    <th rowspan="2" style="border: 1px solid #000;">Remark</th>
                    <th rowspan="2" style="border: 1px solid #000;">Inspektor</th>
                </tr>
                <tr>
                    <th style="border: 1px solid #000;">CPU</th>
                    <th style="border: 1px solid #000;">Int CPU</th>
                    <th style="border: 1px solid #000;">Monitor</th>
                    <th style="border: 1px solid #000;">Soft Lic</th>
                    <th style="border: 1px solid #000;">Std Software</th>
                    <th style="border: 1px solid #000;">Dev Name</th>
                    <th style="border: 1px solid #000;">Clear Cache</th>
                    <th style="border: 1px solid #000;">System Restore</th>
                    <th style="border: 1px solid #000;">Win Update</th>
                    <th style="border: 1px solid #000;">Defrag</th>
                    <th style="border: 1px solid #000;">Health %</th>
                    <th style="border: 1px solid #000;">Uname</th>
                    <th style="border: 1px solid #000;">Auto Lck</th>
                    <th style="border: 1px solid #000;">Inp Pwd</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($inspeksiComputerAll as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->computer->computer_code ?? '-' }}</td>
                        <td>{{ $item->computer->number_asset_ho ?? '-'  }}</td>
                        <td>{{ $item->created_date }}</td>
                        <td>{{ $item->computer->computer_name ?? '-'  }} - {{ $item->computer->spesifikasi ?? '-'  }}</td>
                        <td class="text-left">{{ $item->computer->location ?? '-'  }}</td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">
                            {{ $item->physique_condition_cpu == 'Y' ? '✔' : '✖' }}</td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">
                            {{ $item->physique_condition_internal_cpu == 'Y' ? '✔' : '✖' }}</td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">
                            {{ $item->physique_condition_monitor == 'Y' ? '✔' : '✖' }}</td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">
                            {{ $item->software_license == 'Y' ? '✔' : '✖' }}</td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">
                            {{ $item->software_standaritation == 'Y' ? '✔' : '✖' }}</td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">
                            {{ $item->software_device_name_standaritation == 'Y' ? '✔' : '✖' }}</td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">
                            {{ $item->software_clear_cache == 'Y' ? '✔' : '✖' }}</td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">
                            {{ $item->software_system_restore == 'Y' ? '✔' : '✖' }}</td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">{{ $item->software_windows_update }}</td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">{{ $item->defrag == 'Y' ? '✔' : '✖' }}</td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">{{ $item->software_storage_health }}</td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">
                            {{ $item->security_change_password == 'Y' ? '✔' : '✖' }}</td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">
                            {{ $item->security_auto_lock == 'Y' ? '✔' : '✖' }}</td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">
                            {{ $item->security_input_password == 'Y' ? '✔' : '✖' }}</td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">{{ $item->conditions }}</td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">{{ $item->inventory_status }}</td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">{{ $item->remarks }}</td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">{{ $item->inspector }}</td>
                    </tr>
                @endforeach

            </tbody>
        </table>
        <br><br><br>

        <div style="page-break-before: always;"></div> <!-- Ini supaya pindah ke halaman baru -->
        <div style="font-size: 12px; font-weight: bold; margin-bottom: 10px;">CATATAN</div>

        <ul style="font-size: 11px; margin-bottom: 20px;">
            <li>Checklist ini dilakukan secara berkala setiap 3 bulan.</li>
            <li>Unit utilize adalah total keseluruhan unit yang tidak termasuk unit scrap.</li>
        </ul>

        <!-- Tambahan total -->
        <div style="font-size: 11px; margin-bottom: 40px;">
            <strong>Total Barang Scrap:</strong> {{ $unitScrap }} <br>
            <strong>Total Utilize:</strong> {{ $unitUtilize }}
        </div>

        <!-- Tanda tangan -->
        <table style="width: 100%; font-size: 11px; margin-top: 50px; border: none;">
            <tr>
                <td style="text-align: center; border: none;">
                    Mengetahui,<br>
                    Group Leader<br><br>
                    @if ($qr_base64Approved)
                        <img src="{{ $qr_base64Approved }}" alt="QR Code" style="width: 100px; height: 100px;"><br>
                    @else
                        <p><i>Perlu Approval</i></p>
                    @endif
                    <br>
                    ( {{ $picApproved }} )
                </td>
                <td style="text-align: center; border: none;">
                    Inspektor,<br>
                    IT Support<br><br>
                    @if ($qr_base64Pic)
                        <img src="{{ $qr_base64Pic }}" alt="QR Code" style="width: 100px; height: 100px;"><br>
                    @else
                        <p><i>Perlu Inspector</i></p>
                    @endif
                    <br>
                    ( {{ $pic }} )
                </td>
            </tr>
        </table>
    </div>

    <footer>
        <div class="footer-content">
            <div>Generated by ITPORTAL | {{ date('d-m-Y') }}</div>
        </div>
    </footer>
</body>

</html>
