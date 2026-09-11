<?php

declare(strict_types=1);

namespace SGFP\REST\Controllers;

use SGFP\Application\Services\CreateTransferService;
use SGFP\Application\Services\SettleTransferService;
use SGFP\Application\Services\UndoTransferSettlementService;
use SGFP\REST\DTOs\CreateTransferRequest;

final class TransferController
{
    public function __construct(
        private readonly CreateTransferService $createService,
        private readonly SettleTransferService $settleService,
        private readonly UndoTransferSettlementService $undoService,
    ) {
    }

    public function create(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $dto = CreateTransferRequest::fromRequest($request);
            $dto->validate();

            [$commitment, $transfer] = $this->createService->execute(
                $dto->name,
                $dto->toFloat(),
                $dto->referenceMonth,
                $dto->sourceAccountId,
                $dto->targetAccountId,
            );

            return new \WP_REST_Response([
                'id' => $commitment->id,
                'name' => $commitment->name,
                'amount' => number_format($commitment->amount, 2, '.', ''),
                'nature' => $commitment->nature->value,
                'month' => $commitment->referenceMonth->format('Y-m-d'),
                'status' => $commitment->status->value,
                'type' => $commitment->type->value,
                'source_account_id' => $transfer->sourceAccountId,
                'target_account_id' => $transfer->targetAccountId,
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
            [$commitment, $outflow, $inflow] = $this->settleService->execute((int) $request['id']);

            return new \WP_REST_Response([
                'id' => $commitment->id,
                'status' => $commitment->status->value,
                'entries' => [
                    [
                        'id' => $outflow->id,
                        'account_id' => $outflow->accountId,
                        'effect' => $outflow->effectType->value,
                        'amount' => number_format($outflow->amount, 2, '.', ''),
                    ],
                    [
                        'id' => $inflow->id,
                        'account_id' => $inflow->accountId,
                        'effect' => $inflow->effectType->value,
                        'amount' => number_format($inflow->amount, 2, '.', ''),
                    ],
                ],
            ], 200);
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
