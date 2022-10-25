<?php
// @formatter:off
// phpcs:ignoreFile

namespace Khazhinov\LaravelLightyMongoDBBundle\Models {

    use Khazhinov\LaravelLighty\Models\Attributes\Relationships\RelationshipDTO;
    use ReflectionException;
    use Spatie\DataTransferObject\Exceptions\UnknownProperties;

    class Model
    {
        /**
         * @param  string  $needle_relation
         * @return bool|string
         */
        public function completeRelation(string $needle_relation): bool|string
        {
            return false;
        }

        /**
         * Get the table associated with the model.
         *
         * @return string
         */
        public function getTable()
        {
            return '';
        }

        /**
         * Get the primary key for the model.
         *
         * @return string
         */
        public function getKeyName()
        {
            return '';
        }

        /**
         * Get the value of the model's primary key.
         *
         * @return mixed
         */
        public function getKey()
        {
            return null;
        }

        /**
         * @return RelationshipDTO[]
         * @throws ReflectionException
         * @throws UnknownProperties
         */
        public function getLocalRelations(): array
        {
            return [];
        }

        /**
         * Eager load relations on the model.
         *
         * @param  array|string  $relations
         * @return $this
         */
        public function load($relations)
        {
            return $this;
        }

        /**
         * Get the fillable attributes for the model.
         *
         * @return array<string>
         */
        public function getFillable()
        {
            return [''];
        }

        /**
         * Set a given attribute on the model.
         *
         * @param  string  $key
         * @param  mixed  $value
         * @return mixed
         */
        public function setAttribute($key, $value)
        {
            return null;
        }

        /**
         * Save the model to the database.
         *
         * @param  array  $options
         */
        public function save(array $options = [])
        {
        }

        public function forceDelete()
        {
        }

        public function delete()
        {
        }

        /**
         * Destroy the models for the given IDs.
         *
         * @param  \Illuminate\Support\Collection|array|int|string  $ids
         */
        public static function destroy($ids)
        {
        }
    }
}
