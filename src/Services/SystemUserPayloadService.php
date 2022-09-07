<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\Services;

use Exception;

class SystemUserPayloadService
{
    private static string $system_user_id = '24bc67bb-6c4f-4f8e-884e-4f25f7857b03';
    private static string $system_password = 'Ysj7hYgZgi';

    public static function getSystemUserId(): string
    {
        return self::$system_user_id;
    }

    /**
     * @return array<string, string>
     * @throws Exception
     */
    public static function getSystemUserPayload(): array
    {
        return [
            'id' => self::$system_user_id,
            'name' => 'System',
            'email' => 'system@site.ru',
            'password' => self::$system_password,
        ];
    }
}
