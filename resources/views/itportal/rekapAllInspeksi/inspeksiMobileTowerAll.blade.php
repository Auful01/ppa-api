<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inspection Mobile Tower Report Periode {{ $thisMonthTeks }} {{ $thisYear }}</title>
    <style>
        @page {
            margin: 20px;
        }
    
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
            display: block;
            width: 100%;
        }
    
        .section {
            display: block;
            width: 100%;
            padding: 10px;
            vertical-align: top;
            page-break-inside: avoid;
        }
    
        /* ✅ page break benar-benar aktif di DomPDF */
        .page-break {
            display: block !important;
            clear: both;
            page-break-before: always;
            page-break-after: always;
            break-before: page;
            break-after: page;
        }
    
        .page-break:last-child {
            page-break-after: avoid;
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
            width: 12px;
            height: 12px;
            border: 1px solid black;
            text-align: center;
            vertical-align: middle;
            line-height: 12px;
            font-size: 10px;
            font-weight: bold;
        }
    
        .checkbox.checked::after {
            content: "\2714";
            font-family: "DejaVu Sans", sans-serif;
            display: inline-block;
            font-size: 15px;
            text-align: center;
            width: 100%;
            height: 100%;
        }
    
        .checkbox.unchecked::after {
            content: "\2716";
            font-family: "DejaVu Sans", sans-serif;
            display: inline-block;
            font-size: 13px;
            color: red;
        }
    
        .header {
            background-color: #d9d9d9;
            border-radius: 5px;
            padding: 8px;
            text-align: center;
            display: block;
        }
    
        .header-content {
            display: table;
            width: 100%;
        }
    
        .left-logo,
        .right-logo {
            display: table-cell;
            width: 60px;
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
    
        .footer {
            display: table;
            width: 100%;
        }
    
        .footer-item {
            display: table-cell;
            width: 50%;
            text-align: center;
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
            @foreach ($inspections as $data_inspeksi)
                <div class="section">
                    <div class="header">
                        <div class="header-content">
                            <img src="images/logoppa.png" class="logo-ppa left-logo">
                            <div class="title">
                                FORM DETAIL INSPEKSI MOBILE TOWER <br>
                                <span class="sub-header">PREVENTIVE MAINTENANCE - MOBILE TOWER</span><br>
                                <span class="company-name">PT. PUTRA PERKASA ABADI</span>
                            </div>
                            <img src="images/POLICE_LOGO2.png" class="logo-police right-logo">
                        </div>
                    </div>
                    <table>
                        <tr>
                            <th>No Inventory</th>
                            <td>{{ $data_inspeksi->mt->inventory_number ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>No MT</th>
                            <td>{{ $data_inspeksi->mt->mt_code ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Type</th>
                            <td>{{ $data_inspeksi->mt->type_mt ?? '-'  }}</td>
                        </tr>
                        <tr>
                            <th>Lokasi</th>
                            <td>{{ $data_inspeksi->mt->location ?? '-'  }}</td>
                        </tr>
                        <tr>
                            <th>Detail Lokasi</th>
                            <td>{{ $data_inspeksi->mt->detail_location ?? '-'  }}</td>
                        </tr>
                        <tr>
                            <th>Periode Inspeksi</th>
                            <td>{{ $thisMonthTeks }} {{ $thisYear }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Inspeksi</th>
                            <td>{{ \Carbon\Carbon::parse($data_inspeksi->inspection_at)->translatedFormat('d F Y H:i') ?? '-' }}
                            </td>
                        </tr>

                    </table>
                    @php
    $checklistResults = json_decode($data_inspeksi->checklist_results_list ?? '{}', true);
                    @endphp

                    <div class="checklist-container">
                        @foreach($dataKategori->chunk(2) as $kategoriRow)
                            <div style="display: table-row;">
                                @foreach($kategoriRow as $kategori)
                                    @php
            $categoryNumber = (string) ($kategori->urutan ?? $kategori->id_kat_inspeksi ?? null);
                                    @endphp

                                    <div class="checklist">
                                        <h4>{{ strtoupper($kategori->nama_judul) }}</h4>
                                        <ul>
                                            @foreach($subDataKategori->where('parent', $kategori->id_kat_inspeksi) as $sub)
                                                @php
                $key = $categoryNumber . '_' . $sub->urutan;
                $rawValue = $checklistResults[$key] ?? null;
                $value = is_string($rawValue) ? trim($rawValue) : $rawValue;
                $isChecked = ($value === 'Y');

                if (is_numeric($value)) {
                    $num = floatval($value);

                    if ($key === '2_1') {
                        $isChecked = ($num >= 12);
                    }

                    if ($key === '3_1') {
                        $isChecked = ($num >= 24);
                    }
                }
                                                @endphp

                                                <li>
                                                    <span class="checkbox {{ $isChecked ? 'checked' : 'unchecked' }}"></span>
                                                    {{ $sub->nama_judul }}

                                                    {{-- Show numeric/text value if not Y/N/null --}}
                                                    @if(!in_array($value, ['Y', 'N', null], true))
                                                        <span>: {{ $value }}{{ is_numeric($value) ? 'V' : '' }}</span>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endforeach

                                {{-- Fill empty column if odd count --}}
                                @if($kategoriRow->count() < 2)
                                    <div class="checklist"></div>
                                @endif
                            </div>
                        @endforeach
                    </div>


                    <h4>HASIL INSPEKSI</h4>
                    <table>
                        @php
    // Decode JSON safely (returns [] if invalid)
    $findings = json_decode($data_inspeksi->findings ?? '[]', true) ?? [];
    $actions = json_decode($data_inspeksi->findings_action ?? '[]', true) ?? [];
                        @endphp

                        <tr>
                            <th>Catatan Temuan</th>
                            <td>
                                @if(!empty($findings))
                                    @foreach($findings as $index => $item)
                                        {{ $index + 1 }}. {{ $item }}<br>
                                    @endforeach
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Tindakan</th>
                            <td>
                                @if(!empty($actions))
                                    @foreach($actions as $index => $item)
                                        {{ $index + 1 }}. {{ $item }}<br>
                                    @endforeach
                                @else
                                    -
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <th>Kondisi</th>
                            <td><b>{{ $data_inspeksi->condition ?? '-'  }}</b></td>
                        </tr>
                        <tr>
                            <th>Kelayakan</th>
                            <td>{{ $data_inspeksi->worthiness ?? '-'  }}</td>
                        </tr>
                        <tr>
                            <th>PIC</th>
                            <td>{{ $data_inspeksi->pic ?? '-'  }}</td>
                        </tr>
                        <tr>
                            <th>Due Date</th>
                            <td>
                                @php
    if ($data_inspeksi->due_date != null) {
        $dueDate = \Carbon\Carbon::parse($data_inspeksi->due_date);
        $daysRemaining = now()->diffInDays($dueDate, false);
    } else {
        $daysRemaining = null;
    }
                                @endphp

                                @if ($daysRemaining > 0)
                                    {{ floor($daysRemaining) }} Hari Lagi - {{ $data_inspeksi->due_date }}
                                    {{-- @elseif($daysRemaining == 0)
                                    Sekarang Tenggat Waktunya --}}
                                @elseif($daysRemaining < 0)
                                    Sudah Lewat! - {{ floor(abs($daysRemaining)) }} Hari
                                @else
                                @endif
                            </td>
                        </tr>
                    </table>

                    <div class="footer">
                        <div class="footer-item">
                            <p>Dilaksanakan Oleh</p>
                            @if ($data_inspeksi->qr_base64Inspector)
                                <img src="{{ $data_inspeksi->qr_base64Inspector }}" alt="QR Code"
                                    style="width: 100px; height: 100px;">
                            @else
                                <p><i>Belum Inspeksi</i></p>
                            @endif
                            <p>__________________</p>
                            <p style="margin-bottom: 0px;">{{ $data_inspeksi->inspector }}</p>
                            <p>IT Support</p>
                            </p>
                        </div>
                        <div class="footer-item">
                            <p>Diketahui Oleh</p>

                            @if ($data_inspeksi->qr_base64Approved)
                                <img src="{{ $data_inspeksi->qr_base64Approved }}" alt="QR Code"
                                    style="width: 100px; height: 100px;">
                            @else
                                <p><i>Perlu Approval</i></p>
                            @endif
                            <p>__________________</p>

                            <p style="margin-bottom: 0px;">{{ $data_inspeksi->approved_by }}</p>
                            <p>IT Group Leader</p>
                        </div>

                    </div>
                </div>
            @endforeach
        </div>
    </div>
</body>

</html>