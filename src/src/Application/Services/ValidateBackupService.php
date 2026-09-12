<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Backup\StagedBackupDecoder;

use SGFP\Application\Ports\UserContext;
use SGFP\Application\Ports\UserPreferenceRepository;

final class ValidateBackupService
{
    private const MAX_BYTES = 26214400;

    public function __construct(
        private readonly UserPreferenceRepository $preferences,
        private readonly UserContext $userContext,
        private readonly StagedBackupDecoder $decoder = new StagedBackupDecoder(),
    ) {}

    /** @return array{token:string,version:int,origin:string,created_at:string,counts:array<string,int>} */
    public function validate(string $encoded): array
    {
        $this->userContext->requireCapability('use_sgfp');
        $userId = $this->userContext->requireUserId();
        if ($encoded === '' || strlen($encoded) > self::MAX_BYTES) {
            throw new \InvalidArgumentException('Arquivo de backup inválido ou excede o limite permitido.');
        }

        $binary = base64_decode($encoded, true);
        $keyValue = getenv('SGFP_BACKUP_KEY') ?: '';
        if ($binary === false || $keyValue === '' || strlen($binary) <= SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES) {
            throw new \InvalidArgumentException('Arquivo de backup inválido.');
        }
        $key = hash('sha256', $keyValue, true);
        $nonceSize = SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES;
        $plain = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(substr($binary, $nonceSize), '', substr($binary, 0, $nonceSize), $key);
        $json = $plain === false ? false : gzdecode($plain);
        $payload = $json === false ? null : json_decode($json, true);
        if (!is_array($payload)) throw new \InvalidArgumentException('Backup inválido.');
        $validated = $this->decoder->decode($payload, $userId);

        $token = bin2hex(random_bytes(32));
        $directory = getenv('SGFP_BACKUP_DIR') ?: '';
        if ($directory === '' || !is_dir($directory) || !is_writable($directory)) {
            throw new \RuntimeException('O staging privado de backups não está configurado.');
        }
        $filename = bin2hex(random_bytes(24)) . '.sgfp';
        $temporary = tempnam($directory, 'sgfp-');
        if ($temporary === false || file_put_contents($temporary, $encoded, LOCK_EX) === false) {
            if ($temporary !== false) @unlink($temporary);
            throw new \RuntimeException('Não foi possível preservar o backup para validação.');
        }
        $path = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
        if (!rename($temporary, $path)) {
            @unlink($temporary);
            throw new \RuntimeException('Não foi possível preservar o backup para validação.');
        }
        $stored = file_get_contents($path);
        if ($stored === false || !hash_equals(hash('sha256', $encoded), hash('sha256', $stored))) {
            @unlink($path);
            throw new \RuntimeException('Não foi possível confirmar o staging do backup.');
        }
        $this->preferences->set('restore_validation_' . hash('sha256', $token), $userId, json_encode([
            'expires_at' => time() + 900,
            'origin' => $validated->metadata['origin'],
            'hash' => hash('sha256', $encoded),
            'path' => $path,
        ], JSON_THROW_ON_ERROR));

        return [
            'token' => $token,
            'version' => 1, 'origin' => $validated->metadata['origin'],
            'created_at' => $validated->metadata['created_at'], 'counts' => $validated->counts(),
        ];
    }
}
