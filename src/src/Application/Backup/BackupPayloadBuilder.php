<?php

declare(strict_types=1);

namespace SGFP\Application\Backup;

use SGFP\Application\Ports\AccountRepository;
use SGFP\Application\Ports\CategoryRepository;
use SGFP\Application\Ports\CommitmentRepository;
use SGFP\Application\Ports\EntryRepository;
use SGFP\Application\Ports\RecurrenceRepository;
use SGFP\Application\Ports\UserPreferenceRepository;

final class BackupPayloadBuilder
{
    public function __construct(
        private readonly AccountRepository $accounts,
        private readonly CategoryRepository $categories,
        private readonly CommitmentRepository $commitments,
        private readonly EntryRepository $entries,
        private readonly RecurrenceRepository $recurrences,
        private readonly UserPreferenceRepository $preferences,
    ) {}

    public function build(int $userId, string $origin): array
    {
        if ($userId <= 0 || !in_array($origin, ['manual', 'pre_restore'], true)) {
            throw new \InvalidArgumentException('Parâmetros de backup inválidos.');
        }

        $accounts = $this->accounts->findAllByUser($userId);

        if (count($accounts) !== 1) {
            throw new \RuntimeException('O backup exige exatamente uma Conta Financeira por usuário.');
        }

        return [
            'metadata' => [
                'schema' => 'sgfp-backup',
                'version' => 2,
                'owner' => $userId,
                'origin' => $origin,
                'created_at' => gmdate('c'),
            ],
            'theme' => $this->preferences->get('theme', $userId) ?: 'dark',
            'accounts' => array_map([$this, 'accountRecord'], $accounts),
            'categories' => array_map([$this, 'categoryRecord'], $this->categories->findAllByUser($userId)),
            'recurrences' => array_map([$this, 'recurrenceRecord'], $this->recurrences->findAllByUser($userId)),
            'commitments' => array_map([$this, 'commitmentRecord'], $this->commitments->findAllByUser($userId)),
            'entries' => array_map([$this, 'entryRecord'], $this->entries->findAllByUser($userId)),
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
