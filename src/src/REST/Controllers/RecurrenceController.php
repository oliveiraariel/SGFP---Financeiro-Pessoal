<?php

declare(strict_types=1);

namespace SGFP\REST\Controllers;

use SGFP\Application\Services\MaterializeRecurrenceOccurrenceService;
use SGFP\Application\Services\EndRecurrenceFromOccurrenceService;
use SGFP\Application\Services\SettleRecurrenceOccurrenceService;
use SGFP\Application\Services\UndoRecurrenceOccurrenceSettlementService;
use SGFP\Application\Services\UpdateRecurrenceFromOccurrenceService;

final class RecurrenceController
{
    public function __construct(
        private readonly MaterializeRecurrenceOccurrenceService $materializeService,
        private readonly SettleRecurrenceOccurrenceService $settleService,
        private readonly UndoRecurrenceOccurrenceSettlementService $undoService,
        private readonly EndRecurrenceFromOccurrenceService $endService,
        private readonly ?UpdateRecurrenceFromOccurrenceService $updateService = null,
    ) {}

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
                'amount' => $commitment->amount,
                'nature' => $commitment->nature->value,
                'commitment_date' => $commitment->referenceMonth->format('Y-m-d'),
                'status' => $commitment->status->value,
                'created_at' => $commitment->createdAt->format('c'),
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return \SGFP\REST\PublicError::response($e, 400, 'VALIDATION_ERROR');
        } catch (\RuntimeException $e) {
            return \SGFP\REST\PublicError::response($e, $e->getCode() ?: 500);
        } catch (\Throwable $e) {
            return \SGFP\REST\PublicError::response($e, 500);
        }
    }

    public function updateOccurrence(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $item = $this->updateService?->execute((int) $request['id'], (string) $request['month'], $request['category_id'] === null ? null : (int) $request['category_id'], (string) $request['name'], (string) $request['amount'], $request['commitment_date'] ?? null, (string) ($request['scope'] ?? 'current'));
            return new \WP_REST_Response(['id'=>$item->id,'recurrence_id'=>$item->recurrenceId,'category_id'=>$item->categoryId,'name'=>$item->name,'amount'=>$item->amount,'nature'=>$item->nature->value,'commitment_date'=>$item->referenceMonth->format('Y-m-d'),'status'=>$item->status->value,'created_at'=>$item->createdAt->format('c')], 200);
        } catch (\InvalidArgumentException $e) { return \SGFP\REST\PublicError::response($e, 400, 'VALIDATION_ERROR'); }
        catch (\RuntimeException $e) { return \SGFP\REST\PublicError::response($e, $e->getCode() ?: 500); }
    }

    public function settleOccurrence(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $entry = $this->settleService->execute((int) $request['id'], (string) $request['month']);

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
            return \SGFP\REST\PublicError::response($e, 400, 'VALIDATION_ERROR');
        } catch (\RuntimeException $e) {
            return \SGFP\REST\PublicError::response($e, $e->getCode() ?: 500);
        } catch (\Throwable $e) {
            return \SGFP\REST\PublicError::response($e, 500);
        }
    }

    public function undoOccurrence(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $this->undoService->execute((int) $request['id'], (string) $request['month']);
            return new \WP_REST_Response(['status' => 'desfeito'], 200);
        } catch (\InvalidArgumentException $e) {
            return \SGFP\REST\PublicError::response($e, 400, 'VALIDATION_ERROR');
        } catch (\RuntimeException $e) {
            return \SGFP\REST\PublicError::response($e, $e->getCode() ?: 500);
        } catch (\Throwable $e) {
            return \SGFP\REST\PublicError::response($e, 500);
        }
    }

    public function endFromOccurrence(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $this->endService->execute((int) $request['id'], (string) $request['month']);
            return new \WP_REST_Response(['status' => 'encerrada'], 200);
        } catch (\InvalidArgumentException $e) {
            return \SGFP\REST\PublicError::response($e, 400, 'VALIDATION_ERROR');
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
