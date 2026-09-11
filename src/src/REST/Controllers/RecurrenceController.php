<?php

declare(strict_types=1);

namespace SGFP\REST\Controllers;

use SGFP\Application\Services\MaterializeRecurrenceOccurrenceService;
use SGFP\Application\Services\SettleRecurrenceOccurrenceService;
use SGFP\Application\Services\UndoRecurrenceOccurrenceSettlementService;

final class RecurrenceController
{
    public function __construct(
        private readonly MaterializeRecurrenceOccurrenceService $materializeService,
        private readonly SettleRecurrenceOccurrenceService $settleService,
        private readonly UndoRecurrenceOccurrenceSettlementService $undoService,
    ) {
    }

    public function showOccurrence(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $commitment = $this->materializeService->execute(
                (int) $request['id'],
                (string) $request['month'],
            );

            return new \WP_REST_Response([
                'id' => $commitment->id,
                'recurrence_id' => $commitment->recurrenceId,
                'category_id' => $commitment->categoryId,
                'name' => $commitment->name,
                'amount' => number_format($commitment->amount, 2, '.', ''),
                'type' => $commitment->type->value,
                'nature' => $commitment->nature->value,
                'reference_month' => $commitment->referenceMonth->format('Y-m-d'),
                'status' => $commitment->status->value,
                'created_at' => $commitment->createdAt->format('c'),
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return new \WP_REST_Response(['error' => $e->getMessage()], 400);
        } catch (\RuntimeException $e) {
            return new \WP_REST_Response(['error' => $e->getMessage()], $e->getCode() ?: 500);
        } catch (\Throwable $e) {
            return new \WP_REST_Response(['error' => 'Erro interno.'], 500);
        }
    }

    public function settleOccurrence(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $entry = $this->settleService->execute(
                (int) $request['id'],
                (string) $request['month'],
            );

            return new \WP_REST_Response([
                'entry_id' => $entry->id,
                'commitment_id' => $entry->commitmentId,
                'account_id' => $entry->accountId,
                'origin' => $entry->origin->value,
                'name' => $entry->name,
                'amount' => number_format($entry->amount, 2, '.', ''),
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

    public function undoOccurrence(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $this->undoService->execute(
                (int) $request['id'],
                (string) $request['month'],
            );

            return new \WP_REST_Response(['status' => 'desfeito'], 200);
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
