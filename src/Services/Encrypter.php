<?php

declare(strict_types=1);

namespace Khazhinov\LaravelLighty\Services;

use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\Encrypter as EncrypterContract;
use Illuminate\Contracts\Encryption\EncryptException;
use Illuminate\Support\Str;
use JsonException;
use Khazhinov\LaravelLighty\Patterns\Singleton;
use RuntimeException;

use function sodium_crypto_auth;
use function sodium_crypto_auth_verify;
use function sodium_crypto_secretbox;
use function sodium_crypto_secretbox_keygen;
use function sodium_crypto_secretbox_open;

use SodiumException;
use Throwable;

/**
 * @method static Encrypter getInstance()
 */
class Encrypter extends Singleton implements EncrypterContract
{
    /**
     * The encryption key.
     *
     * @var string
     */
    protected string $key;

    protected function init(): void
    {
        $key = (string) config('app.key');

        if (Str::startsWith($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        if (! static::supported($key)) {
            throw new RuntimeException('Incorrect key provided.');
        }

        $this->key = $key;
    }

    /**
     * Determine if the given key is valid.
     *
     * @param  string  $key
     * @return bool
     */
    public static function supported(string $key): bool
    {
        return mb_strlen($key, '8bit') === SODIUM_CRYPTO_SECRETBOX_KEYBYTES;
    }

    /**
     * Create a new encryption key.
     *
     * @return string
     */
    public function generateKey(): string
    {
        return sodium_crypto_secretbox_keygen();
    }

    /**
     * Encrypt the given value.
     *
     * @param  mixed  $value
     * @param  bool  $serialize
     * @return string|null
     *
     * @throws EncryptException|SodiumException
     * @throws Exception
     */
    public function encrypt(mixed $value, $serialize = true): ?string
    {
        $this->init();

        if (is_null($value)) {
            return null;
        }

        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        try {
            $value = sodium_crypto_secretbox($serialize ? serialize($value) : $value, $nonce, $this->key);
        } catch (Throwable $e) {
            throw new EncryptException($e->getMessage(), $e->getCode(), $e);
        }

        $mac = sodium_crypto_auth($value, $this->key);

        $json = json_encode(array_map('base64_encode', compact('nonce', 'value', 'mac')), JSON_THROW_ON_ERROR);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new EncryptException('Could not encrypt the data.');
        }

        return base64_encode($json);
    }

    /**
     * Encrypt a string without serialization.
     *
     * @param  string  $value
     * @return string|null
     *
     * @throws SodiumException
     */
    public function encryptString(string $value): ?string
    {
        return $this->encrypt($value, false);
    }

    /**
     * Decrypt the given value.
     *
     * @param  mixed  $payload
     * @param  bool  $serialize
     * @return mixed
     *
     * @throws DecryptException|SodiumException|JsonException
     */
    public function decrypt($payload, $serialize = true): mixed
    {
        $this->init();

        if (is_null($payload)) {
            return null;
        }

        $payload = $this->getJsonPayload($payload);

        $decrypted = sodium_crypto_secretbox_open($payload['value'], $payload['nonce'], $this->key);

        if ($decrypted === false) {
            throw new DecryptException('Could not decrypt the data.');
        }

        return $serialize ? unserialize($decrypted, ['allowed_classes' => false]) : $decrypted;
    }

    /**
     * Decrypt the given string without unserialization.
     *
     * @param  string  $payload
     * @return string
     *
     * @throws DecryptException
     * @throws SodiumException|JsonException
     */
    public function decryptString(string $payload): string
    {
        return $this->decrypt($payload, false);
    }

    /**
     * Get the JSON array from the given payload.
     *
     * @param  string  $payload
     * @return array<string, mixed>
     *
     * @throws DecryptException
     * @throws SodiumException
     * @throws JsonException
     */
    protected function getJsonPayload(string $payload): array
    {
        /** @var array<string, string> $payload_decoded */
        $payload_decoded = json_decode(base64_decode($payload), true, 512, JSON_THROW_ON_ERROR);

        if (! $this->validPayload($payload_decoded)) {
            throw new DecryptException('The payload is invalid.');
        }

        $payload_decoded = $this->decodePayloadValues($payload_decoded);

        if (! $this->validMac($payload_decoded)) {
            throw new DecryptException('The MAC is invalid.');
        }

        return $payload_decoded;
    }

    /**
     * Verify that the encryption payload is valid.
     *
     * @param  mixed  $payload
     * @return bool
     */
    protected function validPayload(mixed $payload): bool
    {
        return is_array($payload) && isset($payload['nonce'], $payload['value'], $payload['mac']);
    }

    /**
     * Decode the base64 encoded values of the payload.
     *
     * @param  array<string, string>  $payload
     * @return array<string, string|false>
     */
    protected function decodePayloadValues(array $payload): array
    {
        return array_map(static function ($value) {
            return base64_decode($value, true);
        }, $payload);
    }

    /**
     * Determine if the MAC for the given payload is valid.
     *
     * @param  array<string, mixed>  $payload
     * @return bool
     * @throws SodiumException
     */
    protected function validMac(array $payload): bool
    {
        return sodium_crypto_auth_verify($payload['mac'], $payload['value'], $this->key);
    }

    /**
     * Get the encryption key.
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }
}
