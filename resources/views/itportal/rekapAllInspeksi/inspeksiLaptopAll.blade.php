<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Checklist Inspeksi Laptop Periode Tahun {{ $thisYear }}</title>
    <style>
        @page {
            margin: 15mm 10mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            margin: 0;
            padding: 0;
        }

        .container {
            padding: 10px;
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin: 10px 0;
        }

        .subtitle {
            text-align: left;
            margin-bottom: 10px;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 8.5px;
            word-wrap: break-word;
        }

        th,
        td {
            border: 1px solid black;
            padding: 3px;
            text-align: center;
            vertical-align: middle;
        }

        th {
            background-color: #0c0c64;
            color: white;
            font-size: 7px;
        }

        .dataContent {
            font-size: 7px;
        }

        .text-left {
            text-align: left;
        }

        img.logo {
            width: 100%;
            height: auto;
        }

        footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 20px;
            text-align: center;
            font-size: 9px;
            color: #555;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            padding: 0 10px;
        }

        ul {
            padding-left: 15px;
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

        <div class="title">FORM LAPORAN INSPEKSI LAPTOP</div>
        <table style="width: 100%; margin-bottom: 10px; font-size: 12px; border-collapse: collapse;">
            <tr>
                <td style="text-align: left; font-weight: bold; border: none;">
                    Periode Inspeksi: <span style="font-weight: normal;">Tahun - {{ $thisYear }}</span>
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
                    <th colspan="7" style="border: 1px solid #000;">Software</th>
                    <th colspan="3" style="border: 1px solid #000;">Hardware</th>
                    <th colspan="3" style="border: 1px solid #000;">Security</th>
                    <th rowspan="2" style="border: 1px solid #000;">SSD(%)</th>
                    <th rowspan="2" style="border: 1px solid #000;">Kondisi</th>
                    <th rowspan="2" style="border: 1px solid #000;">Status Komputer</th>
                    <th rowspan="2" style="border: 1px solid #000;">Remark</th>
                    <th rowspan="2" style="border: 1px solid #000;">Inspektor</th>
                </tr>
                <tr>
                    <th style="border: 1px solid #000;">Dfrg</th>
                    <th style="border: 1px solid #000;">Sys Restore</th>
                    <th style="border: 1px solid #000;">Cache Data</th>
                    <th style="border: 1px solid #000;">Std Software</th>
                    <th style="border: 1px solid #000;">Soft Lic</th>
                    <th style="border: 1px solid #000;">Win Update</th>
                    <th style="border: 1px solid #000;">Dev Name</th>
                    <th style="border: 1px solid #000;">Fan</th>
                    <th style="border: 1px solid #000;">Pasta</th>
                    <th style="border: 1px solid #000;">Other</th>
                    <th style="border: 1px solid #000;">Uname</th>
                    <th style="border: 1px solid #000;">Auto Lck</th>
                    <th style="border: 1px solid #000;">Inp Pwd</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($inspeksiLaptopAll as $item)
                    <tr class="dataContent">
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->inventory->laptop_code ?? '-' }}</td>
                        <td>{{ $item->inventory->number_asset_ho ?? '-'}}</td>
                        <td>{{ $item->created_date }}</td>
                        <td>{{ $item->inventory->laptop_name ?? '-' }} {{ $item->spesifikasi_singkat ?? '-' }}</td>
                        <td class="text-left">{{ $item->inventory->location ?? '-' }}</td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">
                            {{ $item->software_defrag == 'Y' ? '✔' : '✖' }}</td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">
                            {{ $item->software_check_system_restore == 'Y' ? '✔' : '✖' }}</td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">
                            {{ $item->software_clean_cache_data == 'Y' ? '✔' : '✖' }}</td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">
                            {{ $item->software_standaritation_software == 'Y' ? '✔' : '✖' }}</td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">
                            {{ $item->software_office_license == 'Y' ? '✔' : '✖' }}</td>
                        <td>{{ $item->software_turn_off_windows_update }}</td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">
                            {{ $item->software_standaritation_device_name == 'Y' ? '✔' : '✖' }}</td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">
                            {{ $item->hardware_fan_cleaning == 'Y' ? '✔' : '✖' }}</td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">
                            {{ $item->hardware_change_pasta == 'Y' ? '✔' : '✖' }}</td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">
                            {{ $item->hardware_any_maintenance == 'Y' ? '✔' : '✖' }}</td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">
                            {{ $item->security_change_password == 'Y' ? '✔' : '✖' }}</td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">
                            {{ $item->security_auto_lock == 'Y' ? '✔' : '✖' }}</td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">
                            {{ $item->security_input_password == 'Y' ? '✔' : '✖' }}</td>
                        <td>{{ $item->software_percentage_ssd_health }}
                        </td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">{{ $item->condition }}</td>
                        <td style="font-family: 'DejaVu Sans', sans-serif; font-size: 5px">
                            {{ $item->inventory_status }}</td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">{{ $item->remarks }}</td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">{{ $item->inspector }}</td>
                        {{-- <td style="font-family: 'DejaVu Sans', sans-serif;">{{ $item->inspector }}</td> --}}
                    </tr>
                @endforeach

            </tbody>
        </table>
        <br><br><br>

        <div style="page-break-before: always;"></div> <!-- Ini supaya pindah ke halaman baru -->
        <div style="font-size: 12px; font-weight: bold; margin-bottom: 10px;">CATATAN</div>

        <ul style="font-size: 11px; margin-bottom: 20px;">
            <li>Checklist ini dilakukan secara berkala setiap 1 tahun sekali.</li>
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
                        <p><i>Perlu Approval</i></p>
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
