<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MonitoringJobsExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(
        private readonly Collection $rows
    ) {}

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'job_type',
            'code',
            'site',
            'category_job',
            'description',
            'category',
            'shift',
            'status',
            'urgency',
            'date',
            'due_date',
            'start_progress',
            'end_progress',
            'issue',
            'root_cause',
            'action_taken',
            'remark',
            'sarana',
            'crew_ids',
            'crew_names',
            'approval_status',
            'creator_name',
            'created_at',
            'updated_at',
        ];
    }
}
