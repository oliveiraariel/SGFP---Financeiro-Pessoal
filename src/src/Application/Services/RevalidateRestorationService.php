<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Ports\UserContext;
use SGFP\Application\Ports\UserPreferenceRepository;
use SGFP\Application\Ports\UserOperationLock;
use SGFP\Application\Ports\RestorationTokenClaim;

final class RevalidateRestorationService
{
    public function __construct(
        private readonly UserPreferenceRepository $preferences,
        private readonly UserContext $userContext,
        private readonly UserOperationLock $operationLock,
        private readonly CapturePreRestorationSnapshotService $snapshotCapture,
        private readonly RestorationTokenClaim $tokenClaim,
    ) {}

    /** @return array{status:string,expires_at:int,origin:string} */
    public function prepare(string $token): array
    {
        $this->userContext->requireCapability('use_sgfp');
        $userId = $this->userContext->requireUserId();
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            throw new \InvalidArgumentException('Token de restauração inválido.');
        }
        $this->operationLock->acquire($userId);
        try {
            return $this->prepareUnlocked($token, $userId);
        } finally {
            $this->operationLock->release($userId);
        }
    }

    /** @return array{status:string,expires_at:int,origin:string} */
    public function confirm(string $token, bool $confirmation): array
    {
        if (!$confirmation) {
            throw new \InvalidArgumentException('A confirmação da restauração é obrigatória.');
        }
        $this->userContext->requireCapability('use_sgfp');
        $userId = $this->userContext->requireUserId();
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            throw new \InvalidArgumentException('Token de restauração inválido.');
        }
        $this->operationLock->acquire($userId);
        try {
            $prepared = $this->prepareUnlocked($token, $userId);
            $snapshot = $this->snapshotCapture->captureUnderLock($userId);
            if ($this->tokenClaim->claim($userId, $token, time()) === null) {
                throw new \InvalidArgumentException('Token de restauração expirado ou já consumido.');
            }
            return [
                'status' => 'confirmation_accepted',
                'expires_at' => $prepared['expires_at'],
                'origin' => $prepared['origin'],
                'snapshot' => $snapshot,
            ];
        } finally {
            $this->operationLock->release($userId);
        }
    }

    /** @return array{status:string,expires_at:int,origin:string} */
    private function prepareUnlocked(string $token, int $userId): array
    {
        $raw = $this->preferences->get('restore_validation_' . hash('sha256', $token), $userId);
        $metadata = $raw === null ? null : json_decode($raw, true);
        if (!is_array($metadata) || (int) ($metadata['expires_at'] ?? 0) < time()) {
            throw new \InvalidArgumentException('Token de restauração expirado ou inválido.');
        }
        $path = (string) ($metadata['path'] ?? '');
        $directory = rtrim((string) (getenv('SGFP_BACKUP_DIR') ?: ''), DIRECTORY_SEPARATOR);
        $realDirectory = $directory === '' ? '' : realpath($directory);
        $realPath = $path === '' ? false : realpath($path);
        if ($realDirectory === '' || $realPath === false || !str_starts_with($realPath, $realDirectory . DIRECTORY_SEPARATOR) || !is_file($realPath)) {
            throw new \InvalidArgumentException('O arquivo de restauração não está disponível.');
        }
        $stored = file_get_contents($realPath);
        if ($stored === false || !hash_equals((string) ($metadata['hash'] ?? ''), hash('sha256', $stored))) {
            throw new \InvalidArgumentException('O arquivo de restauração foi alterado.');
        }

        return [
            'status' => 'ready_for_confirmation',
            'expires_at' => (int) $metadata['expires_at'],
            'origin' => (string) ($metadata['origin'] ?? 'unknown'),
        ];
    }
}
