<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\OpenApi\Complexes\Reflector\DTO;

use Khazhinov\LaravelLighty\OpenApi\Complexes\Reflector\SchemeTypeEnum;
use Khazhinov\PhpSupport\DTO\Custer\EnumCaster;
use Khazhinov\PhpSupport\DTO\DataTransferObject;
use Spatie\DataTransferObject\Attributes\CastWith;

class ModelPropertyDTO extends DataTransferObject
{
    public string $name;
    public ?string $description = null;
    public ?string $related = null;
    public bool $nullable = false;
    /** @var ModelPropertyDTO[] */
    public array $related_properties = [];
    public mixed $fake_value = null;

    #[CastWith(EnumCaster::class, enumType: SchemeTypeEnum::class)]
    public SchemeTypeEnum $type;

    public function withFakeValue(): self
    {
        if ($this->type == SchemeTypeEnum::Collection || $this->type == SchemeTypeEnum::Single) {
            foreach ($this->related_properties as &$related_property) {
                $related_property = $related_property->withFakeValue();
            }
        } else {
            $this->fake_value = md5(microtime());
        }

        return $this;
    }
}
