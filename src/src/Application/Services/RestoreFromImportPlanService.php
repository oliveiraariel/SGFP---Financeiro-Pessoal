<?php
declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Backup\RestorationImportPlan;
use SGFP\Application\Ports\RestorationPersistence;
use SGFP\Application\Ports\RestorationTokenClaim;
use SGFP\Application\Ports\TransactionManager;

/** Executes only a validated plan. The caller owns the user lock. */
final class RestoreFromImportPlanService
{
    public function __construct(
        private readonly RestorationPersistence $persistence,
        private readonly TransactionManager $transactions,
        private readonly RestorationTokenClaim $tokenClaim,
    ) {}

    /** @return array{status:string,counts:array<string,int>,references:int,owner:int,theme:string,invariants:bool} */
    public function execute(RestorationImportPlan $plan, string $token, ?int $now = null): array
    {
        if ($plan->userId <= 0 || !preg_match('/^[a-f0-9]{64}$/', $token) || $plan->replacementOrder !== ['accounts','categories','recurrences','commitments','transfers','entries','theme']) {
            throw new \InvalidArgumentException('Plano de restauração inválido.');
        }

        $result = $this->transactions->transactional(function () use ($plan, $token, $now): array {
            if ($this->tokenClaim->claim($plan->userId, $token, $now ?? time()) === null) {
                throw new \InvalidArgumentException('Token de restauração expirado ou já consumido.');
            }
            $this->persistence->replace($plan);
            $result = $this->persistence->verify($plan);
            $expected = $plan->counts();
            if ($result['counts'] !== $expected || $result['owner'] !== $plan->userId || $result['theme'] !== $plan->theme || !$result['invariants']) {
                throw new \RuntimeException('A verificação da restauração falhou.');
            }
            return ['status' => 'restored'] + $result;
        });
        call_user_func('clean_user_cache', $plan->userId);
        global $wpdb;
        $theme = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key = %s LIMIT 1", $plan->userId, 'sgfp_theme'));
        if ($theme !== $plan->theme) throw new \RuntimeException('Falha na leitura pós-commit do tema restaurado.');
        return $result;
    }
}
