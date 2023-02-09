<?php

declare(strict_types=1);

return [
    'models' => [
        'uuid' => [
            'version' => (int) env('MODEL_UUID_VERSION', 4),
        ],
        'user_model_class' => 'App\Models\User',
    ],
    'auth' => [
        'guard' => 'sanctum',
    ],
    'export' => [
        'xlsx' => [
            'styles' => [
                // Стилизация результирующего документа XLSX
                // https://docs.laravel-excel.com/3.1/exports/column-formatting.html#styling
                // Применить к первой строке жирный шрифт
                1 => ['font' => ['bold' => true]],
                // Применение стилей по координате
                //'B2' => ['font' => ['italic' => true]],
                // Применение стилей по столбцу
                //'C' => ['font' => ['size' => 16]],
            ],
        ],
        'csv' => [
            // Настройки для результирующего документа CSV
            // https://docs.laravel-excel.com/3.1/exports/settings.html#custom-csv-settings
            // 'delimiter' => ';',
            'input_encoding' => 'UTF-8',
            'output_encoding' => 'windows-1251', // Для корректного отображения кириллицы в Excel
        ],
    ],
];
