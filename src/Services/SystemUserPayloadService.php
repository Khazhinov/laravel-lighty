<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\Services;

use Exception;
use Illuminate\Foundation\Auth\User;

class SystemUserPayloadService
{
    /** @var array<string, mixed> */
    private static array $system_user_id = [
        'string' => '24bc67bb-6c4f-4f8e-884e-4f25f7857b03',
        'int' => 1,
    ];

    private static string $system_password = 'Ysj7hYgZgi';

    public static function getSystemUserId(): string
    {
        $user_class = config('auth.providers.users.model');
        /** @var User $user_model */
        $user_model = new $user_class();

        return match ($user_model->getKeyType()) {
            'string' => self::$system_user_id['string'],
            default => self::$system_user_id['int'],
        };
    }

    /**
     * @return array<string, string>
     * @throws Exception
     */
    public static function getSystemUserPayload(): array
    {
        return [
            'id' => self::getSystemUserId(),
            'name' => 'System',
            'email' => 'system@site.ru',
            'password' => self::$system_password,
        ];
    }
}
