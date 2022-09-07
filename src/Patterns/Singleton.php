<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\Patterns;

use RuntimeException;

abstract class Singleton
{
    /**
     * @var Singleton[]
     */
    private static array $instances = [];

    final private function __construct()
    {
    }
    private function __clone()
    {
    }

    /**
     * @throws RuntimeException
     */
    final public function __wakeup()
    {
        throw new RuntimeException("Cannot unserialize a " . get_class(self::getInstance()));
    }

    final public static function getInstance(): Singleton
    {
        $cls = static::class;
        if (! isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static();
        }

        return self::$instances[$cls];
    }
}
