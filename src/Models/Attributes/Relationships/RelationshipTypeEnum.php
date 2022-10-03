<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\Models\Attributes\Relationships;

enum RelationshipTypeEnum: string
{
    case BelongsTo = 'belongs_to';
    case HasMany = 'has_many';
    case HasOne = 'has_one';
    case BelongsToMany = 'belongs_to_many';
    case HasOneThrough = 'has_one_through';
    case HasManyThrough = 'has_many_through';
    case MorphOne = 'morph_one';
    case MorphMany = 'morph_many';
    case MorphToMany = 'morph_to_many';
    case MorphTo = 'morph_to';
    case MorphedByMany = 'morphed_by_many';
}
