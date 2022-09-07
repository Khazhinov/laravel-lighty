<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Khazhinov\LaravelLighty\Services\SystemUserPayloadService;

/**
 * Class ModelLoggingable
 * The class provides a centralized logging mechanism.
 * A model inherited from this class must have the following attributes:
 * Timestamp: created_at, updated_at, deleted_at
 * UUID (with a foreign key to the users table): created_by, updated_by, deleted_by
 */
abstract class ModelLoggingable extends Model
{
    use SoftDeletes;

    public $timestamps = false;

    /**
     * @return void
     */
    public static function boot(): void
    {
        static::creating(static function ($instance) {
            $instance->created_at = now();

            if (! $instance->created_by) {
                $user = ModelLoggingable::getUserForLogging();
                if ($user instanceof Authenticatable) {
                    $instance->created_by = $user->getKey();
                } else {
                    $instance->created_by = SystemUserPayloadService::getSystemUserId();
                }
            }
        });

        static::created(static function ($instance) {
        });

        static::updating(static function ($instance) {
            $instance->updated_at = now();

            $user = ModelLoggingable::getUserForLogging();
            if ($user instanceof Authenticatable) {
                $instance->updated_by = $user->getKey();
            } else {
                $instance->updated_by = SystemUserPayloadService::getSystemUserId();
            }
        });

        static::updated(static function ($instance) {
        });

        static::deleting(static function ($instance) {
            $instance->deleted_at = now();

            $user = ModelLoggingable::getUserForLogging();
            if ($user instanceof Authenticatable) {
                $instance->deleted_by = $user->getKey();
            } else {
                $instance->deleted_by = SystemUserPayloadService::getSystemUserId();
            }
        });

        static::deleted(static function ($instance) {
            // ...
        });

        static::forceDeleted(static function ($instance) {
            // ...
        });

        static::restoring(static function ($instance) {
            // ...
        });

        static::restored(static function ($instance) {
            // ...
        });

        parent::boot();
    }

    public static function getUserForLogging(): bool|AuthenticatableModel
    {
        $user = get_user();

        if ($user instanceof AuthenticatableModel) {
            return $user;
        }

        return false;
    }

    public function getCreatedAtAttribute(): ?Carbon
    {
        return $this->getDateAttr('created_at');
    }

    /**
     * Get date attribute.
     *
     * @param  string  $key
     * @return ?Carbon
     */
    protected function getDateAttr(string $key): ?Carbon
    {
        if (array_key_exists($key, $this->attributes) && $this->attributes[$key]) {
            $this->attributes[$key] = $this->asDateTime($this->attributes[$key]);

            return $this->attributes[$key];
        }

        return null;
    }

    /**
     * @param mixed $value
     * @return void
     */
    public function setCreatedAtAttribute(mixed $value): void
    {
        $this->setDateAttr('created_at', $value);
    }

    /**
     * Set date attribute.
     *
     * @param  string  $key
     * @param mixed $value
     *
     * @return void
     */
    protected function setDateAttr(string $key, mixed $value): void
    {
        $this->attributes[$key] = $this->fromDateTime($value);
    }

    public function getUpdatedAtAttribute(): ?Carbon
    {
        return $this->getDateAttr('updated_at');
    }

    /**
     * @param  mixed  $value
     * @return void
     */
    public function setUpdatedAtAttribute(mixed $value): void
    {
        $this->setDateAttr('updated_at', $value);
    }

    public function getDeletedAtAttribute(): ?Carbon
    {
        return $this->getDateAttr('deleted_at');
    }

    /**
     * @param  mixed  $value
     * @return void
     */
    public function setDeletedAtAttribute(mixed $value): void
    {
        $this->setDateAttr('deleted_at', $value);
    }
}
