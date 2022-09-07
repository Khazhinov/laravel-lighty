<?php

declare(strict_types=1);

return [
    'models' => [
        'uuid' => [
            'version' => (int) env('MODEL_UUID_VERSION', 4),
        ],
        'user_model_class' => 'App\Models\User',
    ],
];
