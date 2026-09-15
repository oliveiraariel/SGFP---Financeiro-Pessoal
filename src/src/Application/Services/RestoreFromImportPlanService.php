<?php

declare(strict_types=1);

namespace SGFP\Application\Services;

use SGFP\Application\Backup\RestorationImportPlan;
use SGFP\Application\Ports\RestorationPersistence;
use SGFP\Application\Ports\RestorationTokenClaim;
use SGFP\Application\Ports\TransactionManager;

final class RestoreFromImportPlanService
{
    public function __construct(
        private readonly RestorationPersistence $persistence,
        private readonly TransactionManager $transactions,
        private readonly RestorationTokenClaim $tokenClaim,
    ) {}

    public function execute(RestorationImportPlan $plan, string $token, ?int $now = null): array
    {
        $expectedOrder = ['accounts', 'categories', 'recurrences', 'commitments', 'entries', 'theme'];

        if ($plan->userId <= 0
            || !preg_match('/^[a-f0-9]{64}$/', $token)
            || $plan->replacementOrder !== $expectedOrder) {
            throw new \InvalidArgumentException('Plano de restauração inválido.');
        }

        $result = $this->transactions->transactional(function () use ($plan, $token, $now): array {
            if ($this->tokenClaim->claim($plan->userId, $token, $now ?? time()) === null) {
                throw new \InvalidArgumentException('Token de restauração expirado ou já consumido.');
            }

            $this->persistence->replace($plan);
            $verified = $this->persistence->verify($plan);

            if ($verified['counts'] !== $plan->counts()
                || $verified['owner'] !== $plan->userId
                || $verified['theme'] !== $plan->theme
                || !$verified['invariants']) {
                throw new \RuntimeException('A verificação da restauração falhou.');
            }

            return ['status' => 'restored'] + $verified;
        });

        clean_user_cache($plan->userId);

        global $wpdb;
        $theme = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key = %s LIMIT 1",
            $plan->userId,
            'sgfp_theme'
        ));

        if ($theme !== $plan->theme) {
            throw new \RuntimeException('Falha na leitura pós-commit do tema restaurado.');
        }

        return $result;
    }
}
