<?php

declare(strict_types=1);

namespace SGFP\REST\Controllers;

use SGFP\Application\Services\CreateCommitmentService;
use SGFP\Application\Services\ListCommitmentsService;
use SGFP\Application\Services\SettleCommitmentService;
use SGFP\Application\Services\UndoCommitmentSettlementService;
use SGFP\Application\Services\UpdateCommitmentService;
use SGFP\Application\Services\DeleteCommitmentService;
use SGFP\REST\DTOs\CreateCommitmentRequest;
use SGFP\REST\DTOs\UpdateCommitmentRequest;

final class CommitmentController
{
    public function __construct(
        private readonly CreateCommitmentService $createService,
        private readonly ListCommitmentsService $listService,
        private readonly SettleCommitmentService $settleService,
        private readonly UndoCommitmentSettlementService $undoService,
        private readonly UpdateCommitmentService $updateService,
        private readonly DeleteCommitmentService $deleteService,
    ) {}

    public function list(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $result = $this->listService->execute(
                isset($request['month']) ? (string) $request['month'] : null,
                isset($request['page']) ? (int) $request['page'] : 1,
                isset($request['per_page']) ? (int) $request['per_page'] : 50,
            );
            $result['items'] = array_map([$this, 'resource'], $result['items']);
            return new \WP_REST_Response($result, 200);
        } catch (\InvalidArgumentException $e) {
            return \SGFP\REST\PublicError::response($e, 400, 'VALIDATION_ERROR');
        }
    }

    public function show(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $item = $this->listService->find((int) $request['id']);
            return new \WP_REST_Response($this->resource($item), 200);
        } catch (\RuntimeException $e) { return \SGFP\REST\PublicError::response($e, $e->getCode() ?: 404, 'NOT_FOUND'); }
    }

    public function update(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $dto = UpdateCommitmentRequest::fromRequest($request);
            $dto->validate();
            return new \WP_REST_Response($this->resource($this->updateService->execute((int) $request['id'], $dto->categoryId, trim($dto->name), $dto->normalizedAmount(), $dto->commitmentDate)), 200);
        } catch (\InvalidArgumentException $e) { return $this->error($e, 400, 'VALIDATION_ERROR');
        } catch (\RuntimeException $e) { return $this->error($e, $e->getCode() ?: 404, 'NOT_FOUND'); }
    }

    public function delete(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $this->deleteService->execute((int) $request['id']);
            return new \WP_REST_Response(null, 204);
        } catch (\RuntimeException $e) {
            return \SGFP\REST\PublicError::response($e, $e->getCode() ?: 500, $e->getCode() === 404 ? 'NOT_FOUND' : 'INVALID_STATE');
        }
    }

    private function resource(object $commitment): array
    {
        return ['id' => $commitment->id, 'category_id' => $commitment->categoryId, 'recurrence_id' => $commitment->recurrenceId, 'name' => $commitment->name, 'amount' => $commitment->amount, 'nature' => $commitment->nature->value, 'commitment_date' => $commitment->referenceMonth->format('Y-m-d'), 'status' => $commitment->status->value, 'created_at' => $commitment->createdAt->format('c')];
    }

    public function create(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $dto = CreateCommitmentRequest::fromRequest($request);
            $dto->validate();

            $commitment = $this->createService->execute(
                $dto->categoryId,
                $dto->name,
                $dto->normalizedAmount(),
                $dto->nature,
                $dto->commitmentDate,
                $dto->recurrenceMonthsCount,
                $dto->recurrenceEnabled,
            );

            return new \WP_REST_Response([
                'id' => $commitment->id,
                'category_id' => $commitment->categoryId,
                'recurrence_id' => $commitment->recurrenceId,
                'name' => $commitment->name,
                'amount' => $commitment->amount,
                'nature' => $commitment->nature->value,
                'commitment_date' => $commitment->referenceMonth->format('Y-m-d'),
                'status' => $commitment->status->value,
                'created_at' => $commitment->createdAt->format('c'),
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e, 400, 'VALIDATION_ERROR');
        } catch (\RuntimeException $e) {
            return $this->error($e, $e->getCode() ?: 500, 'REQUEST_ERROR');
        } catch (\Throwable $e) {
            return $this->error($e, 500, 'INTERNAL_ERROR');
        }
    }

    public function settle(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $entry = $this->settleService->execute(
                (int) $request['id'],
                isset($request['settled_at']) ? (string) $request['settled_at'] : null,
            );

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

    public function undo(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $this->undoService->execute((int) $request['id']);
            return new \WP_REST_Response(['status' => 'desfeito'], 200);
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

    private function error(\Throwable $error, int $status, string $code): \WP_REST_Response
    {
        $correlationId = function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : bin2hex(random_bytes(16));
        return new \WP_REST_Response(['error' => \SGFP\REST\PublicError::contract(['code' => $code], $status, $correlationId)], $status);
    }
}
