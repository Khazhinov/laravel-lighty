<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\Models\Attributes\Relationships;

enum RelationshipTypeEnum: string
{
    case BelongsTo = 'belongs_to';
    case HasMany = 'has_many';
    case BelongsToMany = 'belongs_to_many';
}
