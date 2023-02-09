<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\Exports;

use ArrayAccess;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

//use Maatwebsite\Excel\Concerns\WithStyles;

class ModelExport implements FromView, WithTitle, ShouldAutoSize, WithCustomCsvSettings //, WithStyles
{
    /**
     * @param array<int, mixed>|Collection|ArrayAccess $items
     * @param array<string, string> $export_columns
     * @param  string|bool  $page_title
     */
    public function __construct(
        protected array|Collection|ArrayAccess $items,
        protected array $export_columns,
        protected string|bool $page_title = false
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getCsvSettings(): array
    {
        return [
            'input_encoding' => config('lighty.export.csv.input_encoding'),
            'output_encoding' => config('lighty.export.csv.output_encoding'),
        ];
    }

    public function view(): View
    {
        /** @var View $view */
        $view = view(
            view: 'lighty::api.exports.model_collection',
            data: [
                'items' => $this->items,
                'export_columns' => $this->export_columns,
            ]
        );

        return $view;
    }

    /**
     * @param  Worksheet  $sheet
     * @return array<int|string, mixed>
     */
    public function styles(Worksheet $sheet): array
    {
        return config('lighty.export.xlsx.styles');
    }

    public function title(): string
    {
        return $this->page_title ?: config('app.name');
    }
}
