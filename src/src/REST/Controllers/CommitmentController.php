<?php

declare(strict_types=1);

namespace SGFP\REST\Controllers;

use SGFP\Application\Services\CreateCommitmentService;
use SGFP\Application\Services\SettleCommitmentService;
use SGFP\Application\Services\UndoCommitmentSettlementService;
use SGFP\REST\DTOs\CreateCommitmentRequest;

final class CommitmentController
{
    public function __construct(
        private readonly CreateCommitmentService $createService,
        private readonly SettleCommitmentService $settleService,
        private readonly UndoCommitmentSettlementService $undoService,
    ) {
    }

    public function create(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $dto = CreateCommitmentRequest::fromRequest($request);
            $dto->validate();

            $commitment = $this->createService->execute(
                $dto->categoryId,
                $dto->name,
                $dto->amount,
                $dto->type,
                $dto->nature,
                $dto->referenceMonth,
                $dto->recurrenceMonthsCount,
            );

            return new \WP_REST_Response([
                'id' => $commitment->id,
                'category_id' => $commitment->categoryId,
                'recurrence_id' => $commitment->recurrenceId,
                'name' => $commitment->name,
                'amount' => $commitment->amount,
                'type' => $commitment->type->value,
                'nature' => $commitment->nature->value,
                'reference_month' => $commitment->referenceMonth->format('Y-m'),
                'status' => $commitment->status->value,
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
                'origin' => $entry->origin->value,
                'name' => $entry->name,
                'amount' => $entry->amount,
                'effect_type' => $entry->effectType->value,
                'state' => $entry->state->value,
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

    public function undo(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $this->undoService->execute((int) $request['id']);

            return new \WP_REST_Response(['status' => 'desfeito'], 200);
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
