<?php

declare(strict_types=1);

namespace SGFP\REST\Controllers;

use SGFP\Application\Services\CreateCommitmentService;
use SGFP\Application\Services\SettleCommitmentService;

final class CommitmentController
{
    public function __construct(
        private readonly CreateCommitmentService $createService,
        private readonly SettleCommitmentService $settleService,
    ) {
    }

    public function create(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $commitment = $this->createService->execute(
                (int) ($request['account_id'] ?? 0),
                isset($request['category_id']) ? (int) $request['category_id'] : null,
                (string) ($request['description'] ?? ''),
                (float) ($request['amount'] ?? 0),
                (string) ($request['type'] ?? ''),
                (string) ($request['due_date'] ?? '')
            );

            return new \WP_REST_Response([
                'id' => $commitment->id,
                'account_id' => $commitment->accountId,
                'category_id' => $commitment->categoryId,
                'description' => $commitment->description,
                'amount' => $commitment->amount,
                'type' => $commitment->type->value,
                'status' => $commitment->status->value,
                'due_date' => $commitment->dueDate->format('Y-m-d'),
                'created_at' => $commitment->createdAt->format('c'),
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return new \WP_REST_Response(['error' => $e->getMessage()], 400);
        } catch (\RuntimeException $e) {
            return new \WP_REST_Response(['error' => $e->getMessage()], $e->getCode() ?: 500);
        } catch (\Throwable $e) {
            return new \WP_REST_Response(['error' => 'Erro interno.'], 500);
        }
    }

    public function settle(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $entry = $this->settleService->execute((int) $request['id']);

            return new \WP_REST_Response([
                'entry_id' => $entry->id,
                'commitment_id' => $entry->commitmentId,
                'account_id' => $entry->accountId,
                'amount' => $entry->amount,
                'type' => $entry->type->value,
                'competence_date' => $entry->competenceDate->format('Y-m-d'),
                'settled_at' => $entry->settledAt->format('c'),
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return new \WP_REST_Response(['error' => $e->getMessage()], 400);
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
