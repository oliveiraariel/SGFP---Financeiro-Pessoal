<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Backup\RestorationImportPlanner;
use SGFP\Application\Backup\StagedBackupDecoder;
use SGFP\Application\Ports\UserContext;
use SGFP\Application\Ports\UserPreferenceRepository;
use SGFP\Application\Ports\UserOperationLock;
use SGFP\Application\Ports\RestorationTokenClaim;
use SGFP\Application\Ports\RestorationTokenStore;

final class RevalidateRestorationService
{
    public function __construct(
        private readonly UserPreferenceRepository $preferences,
        private readonly UserContext $userContext,
        private readonly UserOperationLock $operationLock,
        private readonly CapturePreRestorationSnapshotService $snapshotCapture,
        private readonly RestorationTokenClaim $tokenClaim,
        private readonly ?RestorationTokenStore $tokenStore = null,
        private readonly StagedBackupDecoder $decoder = new StagedBackupDecoder(),
        private readonly RestorationImportPlanner $planner = new RestorationImportPlanner(),
        private readonly ?RestoreFromImportPlanService $restorer = null,
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
            $plan = $this->reopenAndPlan($prepared['encoded'], $userId);
            $snapshot = $this->snapshotCapture->captureUnderLock($userId);
            if ($this->restorer === null) {
                // Kept only for isolated callers that have not yet supplied the executor.
                // The production composition always supplies it, so the token is claimed
                // inside the restoration transaction there.
                if ($this->tokenClaim->claim($userId, $token, time()) === null) {
                    throw new \InvalidArgumentException('Token de restauração expirado ou já consumido.');
                }
                return ['status' => 'confirmation_accepted', 'expires_at' => $prepared['expires_at'], 'origin' => $prepared['origin'], 'snapshot' => $snapshot, 'import_plan' => ['user_id' => $plan->userId, 'replacement_order' => $plan->replacementOrder, 'counts' => $plan->counts(), 'reference_remapping' => $plan->referenceRemapping, 'theme' => $plan->theme]];
            }
            $restored = $this->restorer->execute($plan, $token);
            $this->tryEmailSnapshot($snapshot, $userId);
            return [
                'status' => 'restored',
                'expires_at' => $prepared['expires_at'],
                'origin' => $prepared['origin'],
                'snapshot' => $snapshot,
                'restoration' => $restored,
                'import_plan' => [
                    'user_id' => $plan->userId,
                    'replacement_order' => $plan->replacementOrder,
                    'counts' => $plan->counts(),
                    'reference_remapping' => $plan->referenceRemapping,
                    'theme' => $plan->theme,
                ],
            ];
        } finally {
            $this->operationLock->release($userId);
        }
    }

    private function tryEmailSnapshot(array $snapshot, int $userId): void
    {
        $path = (string) ($snapshot['path'] ?? '');
        $user = wp_get_current_user();
        if ($path === '' || !is_file($path) || !is_email($user->user_email)) return;
        $sent = wp_mail($user->user_email, 'Cópia pré-restauração do SGFP', 'Cópia criada antes da restauração.', [], [$path]);
        if (!$sent) error_log(sprintf('SGFP: falha no envio da cópia pré-restauração do usuário %d.', $userId));
    }

    /** @return array{status:string,expires_at:int,origin:string,encoded:string} */
    private function prepareUnlocked(string $token, int $userId): array
    {
        $row = $this->tokenStore?->get($userId, hash('sha256', $token));
        $raw = is_array($row) ? (string) ($row['metadata'] ?? '') : $this->preferences->get('restore_validation_' . hash('sha256', $token), $userId);
        $metadata = $raw === '' ? null : json_decode($raw, true);
        if (is_array($row) && isset($row['claimed_at'])) $metadata = null;
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
            'encoded' => $stored,
        ];
    }

    private function reopenAndPlan(string $encoded, int $userId): \SGFP\Application\Backup\RestorationImportPlan
    {
        $binary = base64_decode($encoded, true);
        $keyValue = getenv('SGFP_BACKUP_KEY') ?: '';
        $nonceSize = SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES;
        if ($binary === false || $keyValue === '' || strlen($binary) <= $nonceSize) {
            throw new \InvalidArgumentException('Arquivo de restauração inválido.');
        }
        $plain = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(
            substr($binary, $nonceSize), '', substr($binary, 0, $nonceSize), hash('sha256', $keyValue, true)
        );
        $json = $plain === false ? false : gzdecode($plain);
        $payload = $json === false ? null : json_decode($json, true);
        if (!is_array($payload)) throw new \InvalidArgumentException('Arquivo de restauração inválido.');
        return $this->planner->plan($this->decoder->decode($payload, $userId), $userId);
    }
}
