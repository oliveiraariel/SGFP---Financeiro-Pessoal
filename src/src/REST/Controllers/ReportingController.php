<?php

declare(strict_types=1);

namespace SGFP\REST\Controllers;

use SGFP\Application\Services\GetDashboardService;
use SGFP\Application\Services\GetNetWorthService;
use SGFP\Application\Services\ListMovementsService;

final class ReportingController
{
    public function __construct(
        private readonly ListMovementsService $listMovementsService,
        private readonly GetNetWorthService $getNetWorthService,
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
                'amount' => number_format($entry->amount, 2, '.', ''),
                'effect' => $entry->effectType->value,
                'effective_at' => $entry->settledAt->format('c'),
                'description' => $entry->description,
                'state' => $entry->state->value,
                'created_at' => $entry->createdAt->format('c'),
            ], $entries);

            return new \WP_REST_Response(['items' => $data], 200);
        } catch (\InvalidArgumentException $e) {
            return new \WP_REST_Response(['error' => $e->getMessage()], 400);
        } catch (\RuntimeException $e) {
            return new \WP_REST_Response(['error' => $e->getMessage()], $e->getCode() ?: 500);
        } catch (\Throwable $e) {
            return new \WP_REST_Response(['error' => 'Erro interno.'], 500);
        }
    }

    public function netWorth(): \WP_REST_Response
    {
        try {
            $result = $this->getNetWorthService->execute();

            return new \WP_REST_Response($result, 200);
        } catch (\RuntimeException $e) {
            return new \WP_REST_Response(['error' => $e->getMessage()], $e->getCode() ?: 500);
        } catch (\Throwable $e) {
            return new \WP_REST_Response(['error' => 'Erro interno.'], 500);
        }
    }

    public function dashboard(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $month = isset($request['month']) ? (string) $request['month'] : null;

            if ($month === null || !preg_match('/^\d{4}-\d{2}$/', $month)) {
                return new \WP_REST_Response(['error' => 'O parâmetro month deve estar no formato YYYY-MM.'], 400);
            }

            $result = $this->getDashboardService->execute($month);

            return new \WP_REST_Response($result, 200);
        } catch (\RuntimeException $e) {
            return new \WP_REST_Response(['error' => $e->getMessage()], $e->getCode() ?: 500);
        } catch (\Throwable $e) {
            return new \WP_REST_Response(['error' => 'Erro interno.'], 500);
        }
    }

    public function permissionCheck(): bool
    {
        return current_user_can('use_sgfp');
    }
}
