<?php

declare(strict_types = 1);

namespace Khazhinov\LaravelLighty\OpenApi\Complexes\Reflector;

use GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use JsonException;
use Khazhinov\LaravelLighty\Http\Resources\CollectionResource;
use Khazhinov\LaravelLighty\Http\Resources\SingleResource;
use Khazhinov\LaravelLighty\OpenApi\Complexes\Reflector\DTO\ModelPropertyDTO;
use Khazhinov\LaravelLighty\OpenApi\Complexes\Reflector\DTO\ResourceAdditionsDTO;
use phpDocumentor\Reflection\DocBlock\Tags\Property;
use phpDocumentor\Reflection\DocBlock\Tags\PropertyRead;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\PseudoTypes\Numeric_;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Float_;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\String_;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class ModelReflector
{
    /**
     * @param  string  $model_class
     * @param  string  $collection_resource
     * @return ResourceAdditionsDTO
     * @throws JsonException
     * @throws ReflectionException
     * @throws UnknownProperties
     */
    public function getResourceAdditions(string $model_class, string $collection_resource): ResourceAdditionsDTO
    {
        if (! is_a($model_class, Model::class, true)) {
            throw new RuntimeException(sprintf('Полученный класс модели (%s) не имеет родительского класса %s', $model_class, Model::class));
        }

        if (! is_a($collection_resource, CollectionResource::class, true)) {
            throw new RuntimeException(sprintf('Полученный класс (%s) не имеет родительского класса %s', $model_class, CollectionResource::class));
        }

        $model = new $model_class();
        // Получаем вектор полей модели с приведением к скалярному типу
        $model_properties = $this->getModelProperties($model);
        // Получаем фейковый класс модели для передачи в ресурс
        $virtual_model = $this->getVirtualModelByProperties($model, $model_properties);
        // Формируем класс ресурса коллекции
        $collection = new $collection_resource([]);
        $collection::$from_collection = false;
        // Получаем класс ресурса единичного элемента коллекции
        /** @var SingleResource $single */
        $single = new $collection->collects($virtual_model, true, true);
        // Прогоняем фейковый класс модели через ресурс единичного элемента коллекции, чтобы получить дополнения
        $single_result = json_decode($single->toResponse(\request())->content(), true, 512, JSON_THROW_ON_ERROR);

        return new ResourceAdditionsDTO($single->additions);
    }

    /**
     * @param  string  $model_class
     * @param  string  $single_resource
     * @return SchemaContract[]
     * @throws ReflectionException
     * @throws UnknownProperties
     * @throws JsonException
     */
    public function getSchemaForSingle(string $model_class, string $single_resource): array
    {
        if (! is_a($model_class, Model::class, true)) {
            throw new RuntimeException(sprintf('Полученный класс модели (%s) не имеет родительского класса %s', $model_class, Model::class));
        }

        if (! is_a($single_resource, SingleResource::class, true)) {
            throw new RuntimeException(sprintf('Полученный класс (%s) не имеет родительского класса %s', $model_class, SingleResource::class));
        }

        $model = new $model_class();
        // Получаем вектор полей модели с приведением к скалярному типу
        $model_properties = $this->getModelProperties($model);
        // Получаем фейковый класс модели для передачи в ресурс
        $virtual_model = $this->getVirtualModelByProperties($model, $model_properties);
        // Формируем класс единичного ресурса
        /** @var SingleResource $single */
        $single = new $single_resource($virtual_model, true, true);
        // Прогоняем фейковый класс модели через ресурс единичного элемента коллекции
        $single_result = json_decode($single->toResponse(\request())->content(), true, 512, JSON_THROW_ON_ERROR);
        // Избавляемся от врапинга, в случае его наличия
        if (array_key_exists('data', $single_result)) {
            $single_result = $single_result['data'];
        }

        $schema_properties = $this->makeSchemaPropertiesByResourceResult($single_result, $model_properties);

        /** @var SchemaContract[] $result */
        $result = $this->buildOpenApiSchemaBySchemaProperties($schema_properties);

        return $result;
    }

    public function getSchemaForCollection(string $model_class, string $collection_resource): SchemaContract
    {
        if (! is_a($model_class, Model::class, true)) {
            throw new RuntimeException(sprintf('Полученный класс модели (%s) не имеет родительского класса %s', $model_class, Model::class));
        }

        if (! is_a($collection_resource, CollectionResource::class, true)) {
            throw new RuntimeException(sprintf('Полученный класс (%s) не имеет родительского класса %s', $model_class, CollectionResource::class));
        }

        $model = new $model_class();
        // Получаем вектор полей модели с приведением к скалярному типу
        $model_properties = $this->getModelProperties($model);
        // Получаем фейковый класс модели для передачи в ресурс
        $virtual_model = $this->getVirtualModelByProperties($model, $model_properties);
        // Формируем класс ресурса коллекции
        $collection = new $collection_resource([]);
        $collection::$from_collection = false;
        // Получаем класс ресурса единичного элемента коллекции
        /** @var SingleResource $single */
        $single = new $collection->collects($virtual_model, true, true);
        // Прогоняем фейковый класс модели через ресурс единичного элемента коллекции
        $single_result = json_decode($single->toResponse(\request())->content(), true, 512, JSON_THROW_ON_ERROR);
        // Избавляемся от врапинга, в случае его наличия
        if (array_key_exists('data', $single_result)) {
            $single_result = $single_result['data'];
        }

        $schema_properties = $this->makeSchemaPropertiesByResourceResult($single_result, $model_properties);

        /** @var SchemaContract $result */
        $result = $this->buildOpenApiSchemaBySchemaProperties($schema_properties, wrap_to_object: true);

        return $result;
    }

    /**
     * @param  string  $model_class
     * @return array<string>
     * @throws ReflectionException
     * @throws UnknownProperties
     */
    public function getFlattenModelProperties(string $model_class): array
    {
        if (! is_a($model_class, Model::class, true)) {
            throw new RuntimeException(sprintf('Полученный класс модели (%s) не имеет родительского класса %s', $model_class, Model::class));
        }

        $model = new $model_class();
        $model_properties = $this->getModelProperties($model, false);
        $result_properties = [];
        foreach ($model_properties as $model_property) {
            $result_properties[] = $model_property->name;
        }

        return $result_properties;
    }

    /**
     * @param  Model  $model
     * @param  bool  $need_relationships
     * @return ModelPropertyDTO[]
     * @throws ReflectionException
     * @throws UnknownProperties
     */
    public function getModelProperties(Model $model, bool $need_relationships = true): array
    {
        $reflection_model = new ReflectionClass($model);

        // Данная секция необходима для подбора в докблоках неймспейсов для классов,
        // которые в докблоке указаны без абсолютного пути
        // Такое случается, когда вы указываете класс в атрибутах use перед объявлением класса
        // Данный код формирует полный перечь use строк в ассоциативном виде,
        // где ключом является \ClassName, а значением \Absolute\Path\With\Namespace\To\ClassName
        /** @var string $file_path */
        $file_path = $reflection_model->getFileName();
        $file_data = file_get_contents($file_path);
        $file_use_blocks = [];
        if ($file_data) {
            preg_match_all('/use .*/i', $file_data, $file_use_blocks_raw);
            if (count($file_use_blocks_raw) === 1) {
                foreach ($file_use_blocks_raw[0] as $file_use_block_raw) {
                    // Умираем лишние символы
                    $file_use_block_raw = trim($file_use_block_raw);
                    // Убираем из строки "use "
                    $file_use_block_raw = substr($file_use_block_raw, 4, strlen($file_use_block_raw));
                    // Убираем последний символ ";"
                    $file_use_block_raw = substr($file_use_block_raw, 0, -1);
                    $exploded_file_use_block_raw = explode('\\', $file_use_block_raw);
                    $file_use_blocks[sprintf('\\%s', last($exploded_file_use_block_raw))] = implode('\\', $exploded_file_use_block_raw);
                }
            }
        }

        $model_comments = $reflection_model->getDocComment();
        $model_docblock = $model_comments ? DocBlockFactory::createInstance()->create($model_comments) : null;

        if (! $model_docblock || ! count($model_docblock->getTags())) {
            throw new RuntimeException(sprintf('Модель (%s) должна иметь @property и @property-read поля', $model::class));
        }

        $result_properties = [];

        foreach ($model_docblock->getTags() as $tag) {
            if ($tag instanceof Property || $tag instanceof PropertyRead) {
                // В случае, если тип отсутствует - пропускам
                // OpenApi - строго-типизированный стандарт
                if (! $property_type = $tag->getType()) {
                    continue;
                }

                // Если у свойства несколько типов данных, то требуется перебрать их все
                if (is_a($property_type, Compound::class, true)) {
                    $property_types = [];
                    foreach ($property_type->getIterator() as $_) {
                        $property_types[] = $_;
                    }
                } else {
                    $property_types = [$property_type];
                }

                $nullable = false;
                $related = null;
                $type = null;
                $_ = null;
                foreach ($property_types as $property_type) {
                    // Для получения понимания о возможности установки null
                    if ($property_type::class === Null_::class || $property_type::class === Nullable::class) {
                        $nullable = true;

                        continue;
                    }

                    if ($tag instanceof Property) {
                        // В случае свойства требуется определить скалярный тип данных
                        $_ = match ($property_type::class) {
                            String_::class, Object_::class => SchemeTypeEnum::String,
                            Float_::class, Numeric_::class => SchemeTypeEnum::Number,
                            Integer::class => SchemeTypeEnum::Integer,
                            Boolean::class => SchemeTypeEnum::Boolean,
                            Array_::class => SchemeTypeEnum::Single,
                            default => false,
                        };
                    } else {
                        if ($need_relationships) {
                            // В случае отношения требуется определить вид отношения - к одному или ко многим
                            // Если к одному
                            if ($property_type::class === Object_::class) {
                                $tmp_related = (string) $property_type->getFqsen();
                                // В случае, если класс представлен в виде \ClassName
                                if (substr_count($tmp_related, '\\') === 1) {
                                    // Пытаемся найти его в списке use в файле этого класса
                                    if (array_key_exists($tmp_related, $file_use_blocks)) {
                                        $tmp_related = $file_use_blocks[$tmp_related];
                                    } else {
                                        // Или полагаем, что файл лежит в той же папке, что и этот класс
                                        $tmp_related = $reflection_model->getNamespaceName() . $tmp_related;
                                    }
                                }

                                if (is_a($tmp_related, Model::class, true)) {
                                    $related = $tmp_related;
                                    $nullable = true;
                                    $_ = SchemeTypeEnum::Single;
                                }
                            }

                            // Если ко многим
                            if ($property_type::class === \phpDocumentor\Reflection\Types\Collection::class || $property_type::class === Array_::class) {
                                $tmp_related = (string) $property_type->getValueType();
                                // В случае, если класс представлен в виде \ClassName
                                if (substr_count($tmp_related, '\\') === 1) {
                                    // Пытаемся найти его в списке use в файле этого класса
                                    if (array_key_exists($tmp_related, $file_use_blocks)) {
                                        $tmp_related = $file_use_blocks[$tmp_related];
                                    } else {
                                        // Или полагаем, что файл лежит в той же папке, что и этот класс
                                        $tmp_related = $reflection_model->getNamespaceName() . $tmp_related;
                                    }
                                }

                                if (is_a($tmp_related, Model::class, true)) {
                                    $related = $tmp_related;
                                    $nullable = true;
                                    $_ = SchemeTypeEnum::Collection;
                                }
                            }
                        }

                        if (! $_) {
                            $_ = false;
                        }
                    }


                    if ($_) {
                        $type = $_;
                    }
                }


                if (! $type) {
                    continue;
                }

                $property_body = [
                    'name' => $tag->getVariableName(),
                    'description' => $tag->getDescription(),
                    'type' => $type,
                    'related' => $related,
                    'nullable' => $nullable,
                ];

                // Если выгружается отношение, то требуется получить список его полей БЕЗ его отношений
                if ($related) {
                    $related = new $related();
                    $related_reflector = new ReflectionClass($related);
                    $related_namespace = $related_reflector->getNamespaceName();
                    // Не инспектируем модели вне основного пространства имён
                    if (str_starts_with($related_namespace, 'App')) {
                        $property_body['related_properties'] = $this->getModelProperties(new $related(), false);
                    }
                }

                $property = new ModelPropertyDTO($property_body);
                $result_properties[] = $property->withFakeValue();
            }
        }

        return $result_properties;
    }

    /**
     * @param  ModelPropertyDTO[]  $schema_properties
     * @param  bool  $wrap_to_object
     * @param  string  $object_name
     * @return SchemaContract|SchemaContract[]
     */
    protected function buildOpenApiSchemaBySchemaProperties(array $schema_properties, bool $wrap_to_object = false, string $object_name = ''): SchemaContract|array
    {
        $properties = [];
        foreach ($schema_properties as $schema_property) {
            if ($schema_property->type === SchemeTypeEnum::Single || $schema_property->type === SchemeTypeEnum::Collection) {
                if ($schema_property->type === SchemeTypeEnum::Single) {
                    $properties[] = $this->buildOpenApiSchemaBySchemaProperties($schema_property->related_properties, wrap_to_object: true, object_name: $schema_property->name);
                } else {
                    /** @var SchemaContract $related */
                    $related = $this->buildOpenApiSchemaBySchemaProperties($schema_property->related_properties, wrap_to_object: true);
                    $properties[] = Schema::array($schema_property->name)->items($related);
                }
            } else {
                $property_type = $schema_property->type->value;
                $properties[] = Schema::$property_type($schema_property->name)->description($schema_property->description)->nullable($schema_property->nullable);
            }
        }

        if ($wrap_to_object) {
            return Schema::object($object_name)->properties(...$properties);
        }

        return $properties;
    }

    /**
     * @param  array<string, mixed>  $resource_result
     * @param  ModelPropertyDTO[]  $model_properties
     * @return ModelPropertyDTO[]
     */
    protected function makeSchemaPropertiesByResourceResult(array $resource_result, array $model_properties): array
    {
        $schema_properties = [];
        foreach ($resource_result as $resource_property_name => $resource_property_value) {
            if (is_array($resource_property_value)) {
                // Если массив ассоциативный (ключ => значение), то это отношение вида к одному, иначе ко многим
                $need_skip = false;
                if (helper_array_is_assoc($resource_property_value)) {
                    foreach ($resource_property_value as $single_relation_name => $single_relation_value) {
                        if ($need_skip) {
                            continue;
                        }

                        foreach ($model_properties as $model_property) {
                            if ($model_property->type === SchemeTypeEnum::Single) {
                                foreach ($model_property->related_properties as $single_property) {
                                    if ($single_property->fake_value === $single_relation_value) {
                                        $model_property->name = $resource_property_name;
                                        $model_property->related_properties = $this->makeSchemaPropertiesByResourceResult($resource_property_value, $model_property->related_properties);
                                        $schema_properties[] = $model_property;

                                        $need_skip = true;
                                    }
                                }
                            }
                        }
                    }
                } else {
                    $resource_property_value = $resource_property_value[0];

                    foreach ($resource_property_value as $single_relation_name => $single_relation_value) {
                        if ($need_skip) {
                            continue;
                        }

                        foreach ($model_properties as $model_property) {
                            if ($model_property->type === SchemeTypeEnum::Collection) {
                                foreach ($model_property->related_properties as $single_property) {
                                    if ($single_property->fake_value === $single_relation_value) {
                                        $model_property->name = $resource_property_name;
                                        $model_property->related_properties = $this->makeSchemaPropertiesByResourceResult($resource_property_value, $model_property->related_properties);
                                        $schema_properties[] = $model_property;

                                        $need_skip = true;
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                foreach ($model_properties as $model_property) {
                    if ($model_property->fake_value === $resource_property_value) {
                        $model_property->name = $resource_property_name;
                        $schema_properties[] = $model_property;
                    }
                }
            }
        }

        return $schema_properties;
    }

    /**
     * @param  Model  $source_model
     * @param  ModelPropertyDTO[]  $properties
     * @return VirtualModel
     */
    protected function getVirtualModelByProperties(Model $source_model, array $properties): VirtualModel
    {
        $result_properties = [];
        foreach ($properties as $property) {
            switch ($property->type) {
                case SchemeTypeEnum::Collection:
                    if ($property->related) {
                        /** @var Model $related_source_model */
                        $related_source_model = new $property->related();
                        /** @var Model $virtual_related_model */
                        $virtual_related_model = $this->getVirtualModelByProperties($related_source_model, $property->related_properties);
                        $collection = new Collection();
                        $collection = $collection->push($virtual_related_model);
                        $result_properties[$property->name] = $collection;
                    } else {
                        $result_properties[$property->name] = $property->fake_value;
                    }

                    break;
                case SchemeTypeEnum::Single:
                    if ($property->related) {
                        /** @var Model $related_source_model */
                        $related_source_model = new $property->related();
                        $result_properties[$property->name] = $this->getVirtualModelByProperties($related_source_model, $property->related_properties);
                    } else {
                        $result_properties[$property->name] = $property->fake_value;
                    }


                    break;
                default:
                    $result_properties[$property->name] = $property->fake_value;

                    break;
            }
        }

        return new VirtualModel($source_model, $result_properties);
    }
}
