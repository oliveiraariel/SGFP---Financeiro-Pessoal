<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Ports\UserContext;
use SGFP\Application\Ports\UserPreferenceRepository;

final class ValidateBackupService
{
    private const MAX_BYTES = 26214400;

    public function __construct(
        private readonly UserPreferenceRepository $preferences,
        private readonly UserContext $userContext,
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
        if (!is_array($payload) || ($payload['version'] ?? null) !== 1 || ($payload['user_id'] ?? null) !== $userId) {
            throw new \InvalidArgumentException('Backup incompatível com o usuário atual.');
        }
        foreach (['accounts', 'categories', 'recurrences', 'commitments', 'transfers', 'entries'] as $section) {
            if (!isset($payload[$section]) || !is_array($payload[$section])) {
                throw new \InvalidArgumentException('Backup incompleto.');
            }
        }

        $token = bin2hex(random_bytes(32));
        $this->preferences->set('restore_validation_' . hash('sha256', $token), $userId, json_encode([
            'expires_at' => time() + 900,
            'origin' => $payload['origin'] ?? null,
            'hash' => hash('sha256', $encoded),
        ], JSON_THROW_ON_ERROR));

        return [
            'token' => $token,
            'version' => 1,
            'origin' => (string) ($payload['origin'] ?? 'unknown'),
            'created_at' => (string) ($payload['created_at'] ?? ''),
            'counts' => [
                'accounts' => count($payload['accounts']),
                'categories' => count($payload['categories']),
                'recurrences' => count($payload['recurrences']),
                'commitments' => count($payload['commitments']),
                'transfers' => count($payload['transfers']),
                'entries' => count($payload['entries']),
            ],
        ];
    }
}
