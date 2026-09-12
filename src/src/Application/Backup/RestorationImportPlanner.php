<?php
declare(strict_types=1);
namespace SGFP\Application\Backup;

/** Builds deterministic import data without performing any database operation. */
final class RestorationImportPlanner
{
    private const ORDER = ['accounts','categories','recurrences','commitments','transfers','entries','theme'];
    private const REFERENCES = [
        'commitments' => ['categoryId'=>'categories','recurrenceId'=>'recurrences'],
        'transfers' => ['commitmentId'=>'commitments','sourceAccountId'=>'accounts','targetAccountId'=>'accounts'],
        'entries' => ['accountId'=>'accounts','commitmentId'=>'commitments'],
    ];

    public function plan(StagedBackup $backup, int $effectiveUserId): RestorationImportPlan
    {
        if ($effectiveUserId <= 0 || ($backup->metadata['owner'] ?? null) !== $effectiveUserId) throw new \InvalidArgumentException('Backup incompatível com o usuário efetivo.');
        $maps = [];
        foreach (array_slice(self::ORDER, 0, -1) as $section) {
            $maps[$section] = [];
            foreach ($backup->{$section} as $index => $record) {
                $this->assertUser($record, $effectiveUserId);
                $identity = $record['id'] ?? null;
                $identity = $identity === null ? 'index-'.$index : (string)$identity;
                if (isset($maps[$section][$identity])) throw new \InvalidArgumentException('Identificador duplicado em '.$section.'.');
                $maps[$section][$identity] = $section.':'.$identity;
            }
        }
        $records = [];
        foreach (array_slice(self::ORDER, 0, -1) as $section) {
            $records[$section] = [];
            foreach ($backup->{$section} as $index => $record) {
                $identity = isset($record['id']) && $record['id'] !== null ? (string)$record['id'] : 'index-'.$index;
                $record['logical_key'] = $maps[$section][$identity];
                foreach (self::REFERENCES[$section] ?? [] as $field => $target) {
                    if (!array_key_exists($field, $record) || $record[$field] === null) continue;
                    $lookup = is_int($record[$field]) ? (string)$record[$field] : $record[$field];
                    if (!is_string($lookup) || !isset($maps[$target][$lookup])) throw new \InvalidArgumentException('Referência não resolvida em '.$section.'.');
                    $record[$field] = $maps[$target][$lookup];
                }
                $records[$section][] = $record;
            }
        }
        return new RestorationImportPlan($effectiveUserId, self::ORDER, $records, $maps, $backup->theme);
    }

    private function assertUser(array $record, int $userId): void
    {
        foreach (['userId','user_id'] as $field) if (array_key_exists($field, $record) && $record[$field] !== $userId) throw new \InvalidArgumentException('Registro pertence a outro usuário.');
    }
}
