<?php

declare(strict_types=1);

namespace SGFP\REST\Controllers;

use SGFP\Application\Services\GetDashboardService;
use SGFP\Application\Services\ListMovementsService;

final class ReportingController
{
    public function __construct(
        private readonly ListMovementsService $listMovementsService,
        private readonly GetDashboardService $getDashboardService,
    ) {
    }

    public function movements(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $entries = $this->listMovementsService->execute(
                isset($request['month']) ? (string) $request['month'] : null,
            );

            $data = array_map(fn ($entry) => [
                'id' => $entry->id,
                'account_id' => $entry->accountId,
                'commitment_id' => $entry->commitmentId,
                'origin' => $entry->origin->value,
                'name' => $entry->name,
                'amount' => $entry->effectType->value === 'SAIDA'
                    ? '-' . ltrim($entry->amount, '+-')
                    : $entry->amount,
                'effect' => $entry->effectType->value,
                'effective_at' => $entry->settledAt->format('c'),
                'description' => $entry->description,
                'state' => $entry->state->value,
                'created_at' => $entry->createdAt->format('c'),
            ], $entries);

            return new \WP_REST_Response(['items' => $data], 200);
        } catch (\InvalidArgumentException $e) {
            return \SGFP\REST\PublicError::response($e, 400, 'VALIDATION_ERROR');
        } catch (\RuntimeException $e) {
            return \SGFP\REST\PublicError::response($e, $e->getCode() ?: 500);
        } catch (\Throwable $e) {
            return \SGFP\REST\PublicError::response($e, 500);
        }
    }


    public function dashboard(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $month = isset($request['month']) ? (string) $request['month'] : null;

            if ($month === null || !preg_match('/^\d{4}-\d{2}-01$/', $month)) {
                return \SGFP\REST\PublicError::fromCode(400, 'VALIDATION_ERROR');
            }

            $result = $this->getDashboardService->execute($month);

            return new \WP_REST_Response($result, 200);
        } catch (\RuntimeException $e) {
            return \SGFP\REST\PublicError::response($e, $e->getCode() ?: 500);
        } catch (\Throwable $e) {
            return \SGFP\REST\PublicError::response($e, 500);
        }
    }

    public function permissionCheck(): bool
    {
        return \SGFP\Infrastructure\WordPress\WpUserContext::canAccessSgfp();
    }
}
