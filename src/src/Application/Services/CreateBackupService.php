<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Backup\BackupArchive;
use SGFP\Application\Ports\AccountRepository;
use SGFP\Application\Ports\CategoryRepository;
use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Application\Ports\EntryRepository;
use SGFP\Application\Ports\RecurrenceRepository;
use SGFP\Application\Ports\TransactionManager;
use SGFP\Application\Ports\UserContext;
use SGFP\Application\Ports\UserPreferenceRepository;
use SGFP\Application\Ports\UserOperationLock;

final class CreateBackupService
{
    private const ASSOCIATED_DATA = 'sgfp-backup-v2';

    public function __construct(
        private readonly AccountRepository $accounts,
        private readonly CategoryRepository $categories,
        private readonly CommitmentRepository $commitments,
        private readonly EntryRepository $entries,
        private readonly RecurrenceRepository $recurrences,
        private readonly UserPreferenceRepository $preferences,
        private readonly TransactionManager $transactions,
        private readonly UserContext $userContext,
        private readonly UserOperationLock $operationLock,
        private readonly BackupArchive $archive = new BackupArchive(),
    ) {}

    /** @return array{filename:string,content:string,content_type:string,sha256:string} */
    public function create(): array
    {
        $this->userContext->requireCapability('use_sgfp');
        $userId = $this->userContext->requireUserId();

        $this->operationLock->acquire($userId);
        try {
            $payload = $this->transactions->transactional(function () use ($userId): array {
                return [
                    'metadata' => [
                        'schema' => 'sgfp-backup',
                        'version' => 2,
                        'owner' => $userId,
                        'origin' => 'manual',
                        'created_at' => gmdate('c'),
                    ],
                    'theme' => $this->preferences->get('theme', $userId) ?: 'light',
                    'accounts' => array_map([$this, 'accountRecord'], $this->accounts->findAllByUser($userId)),
                    'categories' => array_map([$this, 'categoryRecord'], $this->categories->findAllByUser($userId)),
                    'recurrences' => array_map([$this, 'recurrenceRecord'], $this->recurrences->findAllByUser($userId)),
                    'commitments' => array_map([$this, 'commitmentRecord'], $this->commitments->findAllByUser($userId)),
                    'entries' => array_map([$this, 'entryRecord'], $this->entries->findAllByUser($userId)),
                ];
            });
        } finally {
            $this->operationLock->release($userId);
        }

        $json = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        $keyValue = getenv('SGFP_BACKUP_KEY') ?: '';

        if ($keyValue === '') {
            throw new \RuntimeException('A chave de proteção do backup não está configurada.');
        }

        $key = hash('sha256', $keyValue, true);
        $nonce = random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);
        $ciphertext = sodium_crypto_aead_xchacha20poly1305_ietf_encrypt(
            $json,
            self::ASSOCIATED_DATA,
            $nonce,
            $key
        );

        $zipContent = $this->archive->pack($nonce . $ciphertext);

        return [
            'filename' => 'sgfp-backup-' . gmdate('Ymd-His') . '.zip',
            'content_type' => 'application/zip',
            'content' => base64_encode($zipContent),
            'sha256' => hash('sha256', $zipContent),
        ];
    }

    private function accountRecord(object $account): array
    {
        return [
            'id' => $account->id,
            'userId' => $account->userId,
            'name' => $account->name,
            'createdAt' => $account->createdAt->format('c'),
        ];
    }

    private function categoryRecord(object $category): array
    {
        return [
            'id' => $category->id,
            'userId' => $category->userId,
            'name' => $category->name,
            'createdAt' => $category->createdAt->format('c'),
        ];
    }

    private function recurrenceRecord(object $recurrence): array
    {
        return [
            'id' => $recurrence->id,
            'userId' => $recurrence->userId,
            'startsIn' => $recurrence->startsIn->format('Y-m-d'),
            'monthsCount' => $recurrence->monthsCount,
            'endedIn' => $recurrence->endedIn?->format('Y-m-d'),
            'createdAt' => $recurrence->createdAt->format('c'),
        ];
    }

    private function commitmentRecord(object $commitment): array
    {
        return [
            'id' => $commitment->id,
            'userId' => $commitment->userId,
            'categoryId' => $commitment->categoryId,
            'recurrenceId' => $commitment->recurrenceId,
            'name' => $commitment->name,
            'amount' => $commitment->amount,
            'nature' => $commitment->nature->value,
            'referenceMonth' => $commitment->referenceMonth->format('Y-m-d'),
            'status' => $commitment->status->value,
            'createdAt' => $commitment->createdAt->format('c'),
        ];
    }

    private function entryRecord(object $entry): array
    {
        return [
            'id' => $entry->id,
            'userId' => $entry->userId,
            'accountId' => $entry->accountId,
            'commitmentId' => $entry->commitmentId,
            'origin' => $entry->origin->value,
            'name' => $entry->name,
            'amount' => $entry->amount,
            'effectType' => $entry->effectType->value,
            'settledAt' => $entry->settledAt->format('c'),
            'description' => $entry->description,
            'state' => $entry->state->value,
            'createdAt' => $entry->createdAt->format('c'),
            'undoneAt' => $entry->undoneAt?->format('c'),
        ];
    }
}
