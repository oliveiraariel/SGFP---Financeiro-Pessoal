<?php
declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Backup\RestorationImportPlan;
use SGFP\Application\Ports\RestorationPersistence;
use SGFP\Application\Ports\TransactionManager;

/** Executes only a validated plan. The caller owns the user lock. */
final class RestoreFromImportPlanService
{
    public function __construct(
        private readonly RestorationPersistence $persistence,
        private readonly TransactionManager $transactions,
    ) {}

    /** @return array{status:string,counts:array<string,int>,references:int,owner:int,theme:string,invariants:bool} */
    public function execute(RestorationImportPlan $plan): array
    {
        if ($plan->userId <= 0 || $plan->replacementOrder !== ['accounts','categories','recurrences','commitments','transfers','entries','theme']) {
            throw new \InvalidArgumentException('Plano de restauração inválido.');
        }

        return $this->transactions->transactional(function () use ($plan): array {
            $this->persistence->replace($plan);
            $result = $this->persistence->verify($plan);
            $expected = $plan->counts();
            if ($result['counts'] !== $expected || $result['owner'] !== $plan->userId || $result['theme'] !== $plan->theme || !$result['invariants']) {
                throw new \RuntimeException('A verificação da restauração falhou.');
            }
            return ['status' => 'restored'] + $result;
        });
    }
}
