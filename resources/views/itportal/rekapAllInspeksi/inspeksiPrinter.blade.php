<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inspection Printer Report Periode {{ $thisMonthTeks }} {{ $thisYear }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            height: auto;
            flex-direction: row;
            flex-wrap: wrap;
        }

        .form-container {
            display: table;
            width: 100%;
            /* page-break-after: always; */
        }

        .section {
            display: table-cell;
            width: 50%;
            padding: 10px;
            /* border: 1px solid black; */
            vertical-align: top;
        }

        .section:first-child {
            border-left: none;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid black;
            padding: 2px;
            text-align: left;
            font-size: 9px;
            vertical-align: middle;
            word-break: break-word;
        }

        th {
            background-color: #f2f2f2;
            width: 35%;
        }

        td {
            width: 65%;
        }

        .checkbox {
            display: inline-block;
            width: 10px;
            height: 10px;
            border: 1px solid black;
            margin-right: 5px;
        }

        .footer {
            display: table;
            width: 100%;
            page-break-inside: avoid;
            /* Agar tidak terpotong saat PDF di-render */
        }

        .footer-item {
            display: table-cell;
            width: 50%;
            text-align: center;
        }

        .header {
            background-color: #d9d9d9;
            border-radius: 5px;
            padding: 8px;
            text-align: center;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .header-content {
            display: table;
            width: 100%;
        }

        .left-logo,
        .right-logo {
            display: table-cell;
            width: 60px;
            /* Sesuaikan ukuran */
            vertical-align: middle;
        }

        .title {
            display: table-cell;
            text-align: center;
            font-size: 14px;
            font-weight: bold;
        }

        .sub-header {
            font-size: 10px;
            font-weight: bold;
        }

        .company-name {
            font-size: 10px;
            font-weight: bold;
        }

        .logo-ppa {
            width: 40px;
            height: auto;
            object-fit: contain;
            margin-top: 10px;
        }

        .logo-police {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }

        .checklist-container {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .checklist {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 10px;
        }


        ul {
            list-style: none;
            padding: 0;
        }

        .checkbox {
            display: inline-block;
            width: 12px;
            /* Pastikan cukup besar */
            height: 12px;
            /* Sama dengan width */
            border: 1px solid black;
            text-align: center;
            vertical-align: middle;
            line-height: 12px;
            /* Pastikan tanda centang sejajar */
            font-size: 10px;
            /* Sesuaikan ukuran centang */
            font-weight: bold;
        }

        .checkbox.checked::after {
            content: "\2714";
            /* Unicode untuk tanda centang */
            font-family: "DejaVu Sans", sans-serif;
            /* Font yang didukung Dompdf */
            display: inline-block;
            font-size: 15px;
            /* Sesuaikan ukuran */
            text-align: center;
            width: 100%;
            /* Agar tetap dalam kotak */
            height: 100%;
        }

        .checkbox.unchecked::after {
            content: "\2716";
            /* ✗ */
            font-family: "DejaVu Sans", sans-serif;
            display: inline-block;
            font-size: 13px;
            color: red;
        }

        .signature {
            width: 40px;
            height: 40px;
            display: block;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="form-container">
            {{-- @foreach ($chunk as $inspection) --}}
            <div class="section">
                <div class="header">
                    <div class="header-content">
                        <img src="images/logoppa.png" class="logo-ppa left-logo">
                        <div class="title">
                            FORM DETAIL INSPEKSI PRINTER <br>
                            <span class="sub-header">PREVENTIVE MAINTENANCE - PRINTER</span><br>
                            <span class="company-name">PT. PUTRA PERKASA ABADI</span>
                        </div>
                        <img src="images/POLICE_LOGO2.png" class="logo-police right-logo">
                    </div>
                </div>
                <table>
                    <tr>
                        <th>No Inventory</th>
                        <td>{{ $inspection->printer->printer_code ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>No Asset HO</th>
                        <td>{{ $inspection->printer->asset_ho_number ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Merk - Type</th>
                        <td>{{ $inspection->printer->printer_brand ?? '-'  }} -
                            {{ $inspection->printer->printer_type ?? '-' }}
                        </td>
                    </tr>
                    <tr>
                        <th>Departemen</th>
                        <td>{{ $inspection->printer->department ?? '-'  }}</td>
                    </tr>
                    <tr>
                        <th>Divisi</th>
                        <td>{{ $inspection->printer->division ?? '-'  }}</td>
                    </tr>
                    <tr>
                        <th>Lokasi</th>
                        <td>{{ $inspection->printer->location ?? '-'  }}</td>
                    </tr>

                    <tr>
                        <th>Periode Inspeksi</th>
                        <td>{{ $thisMonthTeks }} {{ $thisYear }}</td>
                    </tr>
                    <tr>
                        <th>Tanggal Inspeksi</th>
                        <td>{{ \Carbon\Carbon::parse($inspection->inspection_at)->translatedFormat('d F Y H:i') ?? '-' }}
                        </td>
                    </tr>

                </table>

                <div class="checklist-container">
                    <div class="checklist">
                        <h4>HARDWARE DETAIL & CONDITION</h4>
                        <ul>
                            <li>
                                <span
                                    class="checkbox {{ $inspection->tinta_cyan == 'Y' ? 'checked' : 'unchecked' }}"></span>
                                Tinta Cyan
                            </li>
                            <li>
                                <span
                                    class="checkbox {{ $inspection->tinta_magenta == 'Y' ? 'checked' : 'unchecked' }}"></span>
                                Tinta Magenta
                            </li>
                            <li>
                                <span
                                    class="checkbox {{ $inspection->tinta_yellow == 'Y' ? 'checked' : 'unchecked' }}"></span>
                                Tinta Yellow
                            </li>
                            <li>
                                <span
                                    class="checkbox {{ $inspection->tinta_black == 'Y' ? 'checked' : 'unchecked' }}"></span>
                                Tinta Black
                            </li>
                            <li>
                                <span
                                    class="checkbox {{ $inspection->body_condition == 'Y' ? 'checked' : 'unchecked' }}"></span>
                                Kondisi Body / Cover
                            </li>
                            <li>
                                <span
                                    class="checkbox {{ $inspection->usb_cable_condition == 'Y' ? 'checked' : 'unchecked' }}"></span>
                                Kondisi Kabel USB
                            </li>
                            <li>
                                <span
                                    class="checkbox {{ $inspection->power_cable_condition == 'Y' ? 'checked' : 'unchecked' }}"></span>
                                Kondisi Kabel Power
                            </li>
                        </ul>
                    </div>

                    <div class="checklist">
                        <h4 style="margin-top: 5px">MAINTENANCE ACTIVITY</h4>
                        <ul>
                            <li>
                                <span
                                    class="checkbox {{ $inspection->performing_physical_power_cleaning == 'Y' ? 'checked' : 'unchecked' }}"></span>
                                Melakukan Pembersihan Fisik Power
                            </li>
                            <li>
                                <span
                                    class="checkbox {{ $inspection->performing_cleaning_on_the_printer_waste_box == 'Y' ? 'checked' : 'unchecked' }}"></span>
                                Melakukan Pembersihan pada box pembuangan printer
                            </li>
                            <li>
                                <span
                                    class="checkbox {{ $inspection->performing_cleaning_head == 'Y' ? 'checked' : 'unchecked' }}"></span>
                                Melakukan Cleaning Head / Deep Cleaning
                            </li>
                            <li>
                                <span
                                    class="checkbox {{ $inspection->performing_print_quality_test == 'Y' ? 'checked' : 'unchecked' }}"></span>
                                Melakukan uji kualitas hasil print dengan print Test Page
                            </li>
                            <li>
                                <span
                                    class="checkbox {{ $inspection->do_replacing_cable == 'Y' ? 'checked' : 'unchecked' }}"></span>
                                Memberikan / mengganti Label
                            </li>
                        </ul>
                    </div>

                </div>

                <h4>HASIL INSPEKSI</h4>
                <table>
                    <tr>
                        <th>Catatan Temuan</th>
                        <td>{{ $inspection->findings ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Tindakan</th>
                        <td>{{ $inspection->findings_action ?? '-' }}</td>
                    </tr>

                    <tr>
                        <th>Due Date</th>
                        <td>
                            @php
                                if ($inspection->due_date != null) {
                                    $dueDate = \Carbon\Carbon::parse($inspection->due_date);
                                    $daysRemaining = now()->diffInDays($dueDate, false);
                                } else {
                                    $daysRemaining = null;
                                }
                            @endphp

                            @if ($daysRemaining > 0)
                                {{ floor($daysRemaining) }} Hari Lagi - {{ $inspection->due_date }}
                                {{-- @elseif($daysRemaining == 0)
                                Sekarang Tenggat Waktunya --}}
                            @elseif($daysRemaining < 0)
                                Sudah Lewat! - {{ floor(abs($daysRemaining)) }} Hari
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Kondisi</th>
                        <td><b>{{ $inspection->condition ?? '-' }}</b></td>
                    </tr>
                </table>

                <div class="footer">
                    <div class="footer-item">
                        <p>Dilaksanakan Oleh</p>
                        @if ($inspection->qr_base64Inspector)
                            <img src="{{ $inspection->qr_base64Inspector }}" alt="QR Code"
                                style="width: 100px; height: 100px;">
                        @else
                            <p><i>Belum Inspeksi</i></p>
                        @endif
                        <p>__________________</p>
                        <p style="margin-bottom: 0px;">{{ $inspection->inspector }}</p>
                        <p>IT Support</p>
                        </p>
                    </div>
                    <div class="footer-item">
                        <p>Diketahui Oleh</p>

                        @if ($inspection->qr_base64Approved)
                            <img src="{{ $inspection->qr_base64Approved }}" alt="QR Code"
                                style="width: 100px; height: 100px;">
                        @else
                            <p><i>Perlu Approval</i></p>
                        @endif
                        <p>__________________</p>

                        <p style="margin-bottom: 0px;">{{ $inspection->approved_by }}</p>
                        <p>IT Group Leader</p>
                    </div>

                </div>
            </div>
            {{-- @endforeach --}}
            {{-- @if ($chunk->count() == 1)
            <div class="section"></div>
            @endif --}}
        </div>
    </div>
</body>

</html>