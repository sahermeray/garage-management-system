<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    /** @var array<int|string, mixed> */
    private array $data;

    private string $title;

    /**
     * @param  array<int|string, mixed>  $data
     */
    public function __construct(string $title, array $data)
    {
        $this->title = $title;
        $this->data = $data;
    }

    /**
     * @return Collection<int, mixed>
     */
    public function collection(): Collection
    {
        if (empty($this->data)) {
            return collect();
        }

        return collect($this->data)->map(fn ($row) => array_values($row));
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        if (empty($this->data)) {
            return [];
        }

        $firstRow = reset($this->data);

        return array_keys($firstRow);
    }

    public function styles(Worksheet $sheet): ?array
    {
        $sheet->getStyle('1')->getFont()->setBold(true);
        $sheet->setRightToLeft(true);

        return null;
    }

    public function title(): string
    {
        return mb_substr($this->title, 0, 31);
    }
}
