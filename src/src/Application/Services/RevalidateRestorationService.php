<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Backup\BackupArchive;
use SGFP\Application\Backup\BackupProtector;
use SGFP\Application\Backup\PrivateBackupDirectory;
use SGFP\Application\Backup\RestorationImportPlanner;
use SGFP\Application\Backup\StagedBackupDecoder;
use SGFP\Application\Ports\RestorationTokenClaim;
use SGFP\Application\Ports\RestorationTokenStore;
use SGFP\Application\Ports\UserContext;
use SGFP\Application\Ports\UserOperationLock;
use SGFP\Application\Ports\UserPreferenceRepository;

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
        private readonly BackupArchive $archive = new BackupArchive(),
        private readonly BackupProtector $protector = new BackupProtector(),
        private readonly PrivateBackupDirectory $stagingDirectory = new PrivateBackupDirectory(),
    ) {}

    public function prepare(string $token): array
    {
        $this->userContext->requireCapability('use_sgfp');
        $userId = $this->userContext->requireUserId();

        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            throw new \InvalidArgumentException('Token de restauração inválido.');
        }

        $this->operationLock->acquire($userId);
        try {
            $prepared = $this->prepareUnlocked($token, $userId);
            unset($prepared['zip']);
            return $prepared;
        } finally {
            $this->operationLock->release($userId);
        }
    }

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
            $plan = $this->reopenAndPlan($prepared['zip'], $userId);

            // Gate obrigatório: sem snapshot recuperável não há restauração.
            $snapshotInternal = $this->snapshotCapture->captureUnderLock($userId);
            $snapshot = $this->snapshotForClient($snapshotInternal);

            if ($this->restorer === null) {
                if ($this->tokenClaim->claim($userId, $token, time()) === null) {
                    throw new \InvalidArgumentException('Token de restauração expirado ou já consumido.');
                }

                return [
                    'status' => 'confirmation_accepted',
                    'expires_at' => $prepared['expires_at'],
                    'origin' => $prepared['origin'],
                    'snapshot' => $snapshot,
                    'import_plan' => [
                        'user_id' => $plan->userId,
                        'replacement_order' => $plan->replacementOrder,
                        'counts' => $plan->counts(),
                        'reference_remapping' => $plan->referenceRemapping,
                        'theme' => $plan->theme,
                    ],
                ];
            }

            $restored = $this->restorer->execute($plan, $token);

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

    private function prepareUnlocked(string $token, int $userId): array
    {
        $row = $this->tokenStore?->get($userId, hash('sha256', $token));

        $raw = is_array($row)
            ? (string) ($row['metadata'] ?? '')
            : $this->preferences->get('restore_validation_' . hash('sha256', $token), $userId);

        $metadata = $raw === '' ? null : json_decode($raw, true);

        if (is_array($row) && isset($row['claimed_at'])) {
            $metadata = null;
        }

        if (!is_array($metadata) || (int) ($metadata['expires_at'] ?? 0) < time()) {
            throw new \InvalidArgumentException('Token de restauração expirado ou inválido.');
        }

        $path = (string) ($metadata['path'] ?? '');
        $realDirectory = $this->stagingDirectory->resolve();
        $realPath = $path === '' ? false : realpath($path);

        if ($realPath === false
            || !str_starts_with($realPath, $realDirectory . DIRECTORY_SEPARATOR)
            || !is_file($realPath)) {
            throw new \InvalidArgumentException('O arquivo de restauração não está disponível.');
        }

        $stored = file_get_contents($realPath);

        if ($stored === false
            || !hash_equals((string) ($metadata['hash'] ?? ''), hash('sha256', $stored))) {
            throw new \InvalidArgumentException('O arquivo de restauração foi alterado.');
        }

        return [
            'status' => 'ready_for_confirmation',
            'expires_at' => (int) $metadata['expires_at'],
            'origin' => (string) ($metadata['origin'] ?? 'unknown'),
            'zip' => $stored,
        ];
    }

    private function reopenAndPlan(string $zipContent, int $userId): \SGFP\Application\Backup\RestorationImportPlan
    {
        $packed = $this->archive->unpack($zipContent);
        $json = $this->protector->unprotect($packed['payload']);
        $payload = json_decode($json, true);

        if (!is_array($payload)) {
            throw new \InvalidArgumentException('Arquivo de restauração inválido.');
        }

        return $this->planner->plan(
            $this->decoder->decode($payload, $userId),
            $userId
        );
    }

    private function snapshotForClient(array $snapshot): array
    {
        $path = (string) ($snapshot['path'] ?? '');
        $content = $path === '' ? false : file_get_contents($path);

        if ($content === false
            || !hash_equals((string) ($snapshot['hash'] ?? ''), hash('sha256', $content))) {
            throw new \RuntimeException('A cópia pré-restauração não está recuperável.');
        }

        return [
            // The archive remains in the private backup store. REST responses
            // expose only safe metadata; binary content needs a separate,
            // explicitly authorized download flow.
            'filename' => 'sgfp-pre-restore-' . gmdate('Ymd-His') . '.zip',
            'content_type' => 'application/zip',
            'sha256' => hash('sha256', $content),
            'expires_at' => (int) ($snapshot['expires_at'] ?? 0),
            'origin' => 'pre_restore',
        ];
    }
}
