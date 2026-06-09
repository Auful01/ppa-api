<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Checklist Inspeksi Printer Periode {{ $thisMonthTeks }} {{ $thisYear }}</title>
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
                    <img src="{{ public_path('images/header_ppa.jpg') }}" alt="Logo" style="width: 100%; height: auto;">
                </td>
            </tr>
        </table>

        <div class="title">FORM LAPORAN INSPEKSI PRINTER</div>
        <table style="width: 100%; margin-bottom: 10px; font-size: 12px; border-collapse: collapse;">
            <tr>
                <td style="text-align: left; font-weight: bold; border: none;">
                    Periode Inspeksi: <span style="font-weight: normal;">{{ $thisMonthTeks }} {{ $thisYear }}</span>
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
                    <th rowspan="2" style="border: 1px solid #000;">Nomor Inv</th>
                    <th rowspan="2" style="border: 1px solid #000;">Nomor Asset HO</th>
                    <th rowspan="2" style="border: 1px solid #000;">Tgl Inspeksi</th>
                    <th rowspan="2" style="border: 1px solid #000;">Tipe</th>
                    <th rowspan="2" style="border: 1px solid #000;">Dept</th>
                    <th colspan="7" style="border:1px solid #000; text-align:center;">
                        HARDWARE DETAIL & CONDITION
                    </th>

                    <th colspan="5" style="border:1px solid #000; text-align:center;">
                        MAINTENANCE ACTIVITY
                    </th>
                    <th rowspan="2" style="border: 1px solid #000;">Kondisi</th>
                    <th rowspan="2" style="border: 1px solid #000;">Status Printer</th>
                    <th rowspan="2" style="border: 1px solid #000;">Remark</th>
                    <th rowspan="2" style="border: 1px solid #000;">Inspektor</th>
                </tr>
                <tr>
                    <!-- HARDWARE DETAIL & CONDITION -->
                    <th style="border:1px solid #000;">Tinta Cyan</th>
                    <th style="border:1px solid #000;">Tinta Magenta</th>
                    <th style="border:1px solid #000;">Tinta Yellow</th>
                    <th style="border:1px solid #000;">Tinta Black</th>
                    <th style="border:1px solid #000;">Kondisi Body / Cover</th>
                    <th style="border:1px solid #000;">Kondisi Kabel USB</th>
                    <th style="border:1px solid #000;">Kondisi Kabel Power</th>

                    <!-- MAINTENANCE ACTIVITY -->
                    <th style="border:1px solid #000;">Pembersihan Fisik Power</th>
                    <th style="border:1px solid #000;">Pembersihan Box Pembuangan</th>
                    <th style="border:1px solid #000;">Cleaning Head / Deep Cleaning</th>
                    <th style="border:1px solid #000;">Test Page</th>
                    <th style="border:1px solid #000;">Ganti / Pasang Label</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($inspeksiPrinterAll as $item)
                    <tr class="dataContent">
                        <td>{{ $loop->iteration }}</td>
                        <td style="font-size: 5px">{{ $item->printer->printer_code ?? '-' }}</td>
                        <td style="font-size: 5px">{{ $item->printer->asset_ho_number ?? '-'}}</td>
                        <td style="font-size: 5px">
                            {{ $item->inspection_at ? \Carbon\Carbon::parse($item->inspection_at)->translatedFormat('d F Y H:i') : '-' }}
                        </td>
                        <td style="font-size: 5px">{{ $item->printer->printer_brand ?? '-' }}
                            {{ $item->printer->printer_type ?? '-' }}
                        </td>
                        <td style="font-size: 5px">{{ $item->printer->department ?? '-' }}</td>

                        <td style="font-family: 'DejaVu Sans', sans-serif;">
                            {{ $item->tinta_cyan == 'Y' ? '✔' : '✖' }}
                        </td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">
                            {{ $item->tinta_magenta == 'Y' ? '✔' : '✖' }}
                        </td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">
                            {{ $item->tinta_yellow == 'Y' ? '✔' : '✖' }}
                        </td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">
                            {{ $item->tinta_black == 'Y' ? '✔' : '✖' }}
                        </td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">
                            {{ $item->body_condition == 'Y' ? '✔' : '✖' }}
                        </td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">
                            {{ $item->usb_cable_condition == 'Y' ? '✔' : '✖' }}
                        </td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">
                            {{ $item->power_cable_condition == 'Y' ? '✔' : '✖' }}
                        </td>

                        <td style="font-family: 'DejaVu Sans', sans-serif;">
                            {{ $item->performing_physical_power_cleaning == 'Y' ? '✔' : '✖' }}
                        </td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">
                            {{ $item->performing_cleaning_on_the_printer_waste_box == 'Y' ? '✔' : '✖' }}
                        </td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">
                            {{ $item->performing_cleaning_head == 'Y' ? '✔' : '✖' }}
                        </td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">
                            {{ $item->performing_print_quality_test == 'Y' ? '✔' : '✖' }}
                        </td>
                        <td style="font-family: 'DejaVu Sans', sans-serif;">
                            {{ $item->do_replacing_cable == 'Y' ? '✔' : '✖' }}
                        </td>

                        <td style="font-family: 'DejaVu Sans', sans-serif; font-size: 5px">{{ $item->condition }}</td>
                        <td style="font-family: 'DejaVu Sans', sans-serif; font-size: 5px">
                            {{ $item->inventory_status }}
                        </td>
                        <td style="font-family: 'DejaVu Sans', sans-serif; font-size: 5px">{{ $item->remarks }}</td>
                        <td style="font-family: 'DejaVu Sans', sans-serif; font-size: 5px">{{ $item->inspector }}</td>
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