<?php
namespace App\Exports;

use App\Models\ActivityLog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LogsExport implements FromCollection, WithHeadings
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $logs = ActivityLog::query();

        if (isset($this->filters['start_date'])) {
            $logs = $logs->where('file_created_at', '>=', $this->filters['start_date']);
        }

        if (isset($this->filters['end_date'])) {
            $logs = $logs->where('file_created_at', '<=', $this->filters['end_date']);
        }

        if (isset($this->filters['model_type'])) {
            $logs = $logs->where('model_type', $this->filters['model_type']);
        }

        if (isset($this->filters['company_name'])) {
            $logs = $logs->where('company_name', 'LIKE', '%' . $this->filters['company_name'] . '%');
        }

        if (isset($this->filters['action_type'])) {
            $logs = $logs->where('action_type', $this->filters['action_type']);
        }

        return $logs->get([
            'company_name',
            'action_type',
            'model_type',
            'description',
            'file_created_at',
            'approved_at'
        ]);
    }

    public function headings(): array
    {
        return [
            'Firma Adı',
            'Aksiyon Türü',
            'Dosya Türü',
            'Açıklama',
            'Dosya Eklenme Tarihi',
            'Dosya Onaylanma Tarihi'
        ];
    }
}
