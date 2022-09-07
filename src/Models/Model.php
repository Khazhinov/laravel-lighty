<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\Models;

use Illuminate\Database\Eloquent\Model as ModelBase;
use Khazhinov\LaravelLighty\Models\Attributes\Relationships\Relationship;
use Khazhinov\LaravelLighty\Models\Attributes\Relationships\RelationshipDTO;
use Khazhinov\LaravelLighty\Models\UUID\Uuidable;
use Khazhinov\LaravelLighty\Models\UUID\UuidableContract;
use ReflectionException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

/**
 * Базовая абстракция модели.
 * Подразумевается, что все сущности будут использовать в качестве primary key тип UUID.
 *
 * @see https://en.wikipedia.org/wiki/Universally_unique_identifier
 */
abstract class Model extends ModelBase implements UuidableContract
{
    use Uuidable;

    public $incrementing = false;

    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $relations_aliases = [];

    public static function boot(): void
    {
        parent::boot();

        static::creating(static function ($instance) {
            if (! $instance->{$instance->getKeyName()}) {
                $instance->{$instance->getKeyName()} = $instance->generateUuid();
            }
        });

        static::updating(static function ($instance) {
            // ...
        });

        static::deleting(static function ($instance) {
            // ...
        });
    }

    public function getModelName(): bool|string
    {
        if ($model_name = helper_string_title(class_basename($this))) {
            return $model_name;
        }

        return false;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getRelationsAliases(): array
    {
        return $this->relations_aliases;
    }

    /**
     * @param  array<string, array<string, mixed>>  $relations_aliases
     * @return void
     */
    public function setRelationsAliases(array $relations_aliases): void
    {
        $this->relations_aliases = $relations_aliases;
    }

    /**
     * @param  string  $needle_relation
     * @return bool|string
     */
    public function completeRelation(string $needle_relation): bool|string
    {
        foreach ($this->getLocalRelations() as $relation_name => $relation_properties) {
            if ($needle_relation === $relation_name) {
                return $needle_relation;
            }
            if (in_array($needle_relation, $relation_properties->aliases, true)) {
                return $relation_name;
            }
        }

        return false;
    }

    /**
     * @param  string  $alias
     * @return bool
     */
    public function hasRelationByAlias(string $alias): bool
    {
        foreach ($this->relations_aliases as $relation => $aliases) {
            if ($alias === $relation || in_array($alias, $aliases, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return RelationshipDTO[]
     * @throws ReflectionException
     * @throws UnknownProperties
     */
    public function getLocalRelations(): array
    {
        $result = [];
        $reflection = new \ReflectionObject($this);
        foreach ($reflection->getMethods() as $method) {
            $attributes = $method->getAttributes(Relationship::class);

            if (count($attributes) === 1) {
                $attribute = $attributes[0];
                $relationship_detail = $attribute->getArguments();
                if (! isset($relationship_detail['aliases'])) {
                    $relationship_detail['aliases'] = [];
                }

                $result[$method->getName()] = new RelationshipDTO($relationship_detail);
            }
        }

        return $result;
    }

    public function getFormattedModelName(): string
    {
        $name = class_basename($this);

        return helper_string_snake($name);
    }

    /**
     * Get the value indicating whether the IDs are incrementing.
     *
     * @return bool
     */
    public function getIncrementing(): bool
    {
        return false;
    }

    /**
     * Get the auto-incrementing key type.
     *
     * @return string
     */
    public function getKeyType(): string
    {
        return 'string';
    }
}
