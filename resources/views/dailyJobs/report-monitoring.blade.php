<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Daily Report Monitoring Job</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            margin: 0;
            padding: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid black;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
        }

        th {
            background-color: #d32f2f;
            color: white;
        }

        .header-img {
            width: 100%;
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin: 10px 0;
        }

        .subtitle {
            font-size: 11px;
            margin-bottom: 5px;
        }

        .status-open {
            color: sandybrown;
            font-weight: bold;
        }

        .status-continue {
            color: cornflowerblue;
            font-weight: bold;
        }

        .status-close {
            color: green;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <img src="{{ public_path('images/header_ppa.jpg') }}" class="header-img" alt="Header Logo">

    <!-- First Table -->
    <div class="title">DAILY REPORT MONITORING JOB ASSIGNMENT</div>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Job</th>
                <th>Action</th>
                <th>Remark</th>
                <th>Team</th>
                <th>Category Job</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($assignments as $i => $job)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $job['description'] }}</td>
                    <td>{{ $job['action_taken'] }}</td>
                    <td>{{ $job['remark'] }}</td>
                    <td>{{ implode(', ', $job->crew_names) }}</td>
                    <td>{{ $job['category_job'] }}</td>
                    <td class="
                            @if($job['status'] === 'open')
                                status-open
                            @elseif($job['status'] === 'continue')
                                status-continue
                            @else
                                status-close
                            @endif
                        ">
                        {{ $job['status'] }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>


    <!-- This breaks to new PDF page -->
    <div style="page-break-before: always;"></div>

    <img src="{{ public_path('images/header_ppa.jpg') }}" class="header-img" alt="Header Logo">
    <!-- Second Table -->
    <div class="title">DAILY REPORT MONITORING JOB UN-SCHEDULE</div>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Start Progress</th>
                <th>Action</th>
                <th>Remark</th>
                <th>Issue</th>
                <th>Job</th>
                <th>Team</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($unscheduled as $i => $job)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $job['start_progress'] }}</td>
                    <td>{{ $job['action_taken'] }}</td>
                    <td>{{ $job['remark'] }}</td>
                    <td>{{ $job['issue'] }}</td>
                    <td>{{ $job['description'] }}</td>
                    <td>{{ implode(', ', $job->crew_names) }}</td>
                    <td class="
                            @if($job['status'] === 'open')
                                status-open
                            @elseif($job['status'] === 'continue')
                                status-continue
                            @else
                                status-close
                            @endif
                        ">
                        {{ $job['status'] }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Page Break -->
    <div style="page-break-before: always;"></div>

    <!-- DIAGRAM DAILY JOB -->
    <div style="text-align: center;margin-bottom:100px">
        <div class="title">DIAGRAM DAILY JOB</div>
    </div>

    <br><br>

    @php
        $categories = [
            [
                'label' => 'Job Assignment',
                'percentage' => $job_assignment_percentage,
                'count' => $job_assignment_count,
                'color' => '#FF7070'
            ],
            [
                'label' => 'Job Unschedule',
                'percentage' => $unschedule_percentage,
                'count' => $unschedule_count,
                'color' => '#965F8A'
            ],
        ];
    @endphp

    <div style="width: 100%; max-width: 650px;margin-top: 2em;">
        @foreach ($categories as $cat)
            <table style="border: 0 !important; margin: 0 0 5px 0 !important; width: 100%; table-layout: fixed;">
                <tr style="border: 0 !important; margin: 0 !important;">
                    <td
                        style="border: 0 !important; margin: 0 !important; width: 120px !important; font-size: 12px; font-weight: bold; text-align: left;">
                        {{ $cat['label'] }}
                    </td>
                    <td style="border: 0 !important; margin: 0 !important; padding: 0 !important;">
                        <div style="background-color: {{ $cat['color'] }}; 
                                    color: white; 
                                    width: {{ $cat['percentage'] }}%; 
                                    padding: 8px 0 8px 15px; 
                                    font-size: 12px;
                                    min-width: 20px;">
                            {{ $cat['percentage'] }}%
                        </div>
                    </td>
                    <td
                        style="border: 0 !important; margin: 0 !important; width: 50px !important; font-size: 12px; font-weight: bold; text-align: right;">
                        {{ $cat['count'] }} item
                    </td>
                </tr>
            </table>
        @endforeach
    </div>

    <br><br>

    <!-- Tanda tangan -->
    <table style="width: 100%; font-size: 11px; margin-top: 50px; border: none;">
        <tr>
            <td style="text-align: center; border: none;">
                Dilaporkan Oleh,<br>
                Admin ICT<br><br><br><br><br>
                ( ________________ )
            </td>
            <td style="text-align: center; border: none;">
                Mengetahui,<br>
                GROUP LEADER ICT<br><br><br><br><br>
                ( ________________ )
            </td>
        </tr>
    </table>



</body>

</html>