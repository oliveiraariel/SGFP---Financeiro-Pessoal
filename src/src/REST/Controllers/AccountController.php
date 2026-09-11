<?php

declare(strict_types=1);

namespace SGFP\REST\Controllers;

use SGFP\Application\Services\CreateAccountService;
use SGFP\Application\Services\SetInitialBalanceService;
use SGFP\REST\DTOs\CreateAccountRequest;
use SGFP\REST\DTOs\SetInitialBalanceRequest;

final class AccountController
{
    public function __construct(
        private readonly CreateAccountService $createService,
        private readonly \SGFP\Application\Services\ListAccountsService $listService,
        private readonly SetInitialBalanceService $setInitialBalanceService,
    ) {
    }

    public function create(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $dto = CreateAccountRequest::fromRequest($request);
            $dto->validate();

            $account = $this->createService->execute($dto->name, $dto->role);

            return new \WP_REST_Response([
                'id' => $account->id,
                'name' => $account->name,
                'role' => $account->role->value,
                'created_at' => $account->createdAt->format('c'),
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return new \WP_REST_Response(['error' => $e->getMessage()], 400);
        } catch (\RuntimeException $e) {
            return new \WP_REST_Response(['error' => $e->getMessage()], $e->getCode() ?: 500);
        } catch (\Throwable $e) {
            return new \WP_REST_Response(['error' => 'Erro interno.'], 500);
        }
    }

    public function list(): \WP_REST_Response
    {
        try {
            $accounts = $this->listService->execute();

            $data = array_map(fn ($account) => [
                'id' => $account->id,
                'name' => $account->name,
                'role' => $account->role->value,
                'created_at' => $account->createdAt->format('c'),
            ], $accounts);

            return new \WP_REST_Response($data, 200);
        } catch (\RuntimeException $e) {
            return new \WP_REST_Response(['error' => $e->getMessage()], $e->getCode() ?: 500);
        } catch (\Throwable $e) {
            return new \WP_REST_Response(['error' => 'Erro interno.'], 500);
        }
    }

    public function setInitialBalance(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $dto = SetInitialBalanceRequest::fromRequest($request);
            $dto->validate();

            $entry = $this->setInitialBalanceService->execute(
                (int) $request['id'],
                $dto->toFloat(),
                $dto->name,
                $dto->description,
                $dto->toEffectiveMonth(),
            );

            return new \WP_REST_Response([
                'id' => $entry->id,
                'account_id' => $entry->accountId,
                'origin' => $entry->origin->value,
                'name' => $entry->name,
                'amount' => number_format($entry->amount, 2, '.', ''),
                'effect' => $entry->effectType->value,
                'effective_at' => $entry->settledAt->format('c'),
                'description' => $entry->description,
                'state' => $entry->state->value,
                'created_at' => $entry->createdAt->format('c'),
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
