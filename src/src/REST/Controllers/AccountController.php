<?php

declare(strict_types=1);

namespace SGFP\REST\Controllers;

use SGFP\Application\Services\SetInitialBalanceService;
use SGFP\REST\DTOs\SetInitialBalanceRequest;
use SGFP\Application\Ports\EntryRepository;

final class AccountController
{
    public function __construct(
        private readonly \SGFP\Application\Services\ListAccountsService $listService,
        private readonly EntryRepository $entryRepository,
        private readonly SetInitialBalanceService $setInitialBalanceService,
        private readonly \SGFP\Application\Services\RenameAccountService $renameService,
    ) {
    }

    public function rename(\WP_REST_Request $request): \WP_REST_Response
    {
        try { $account = $this->renameService->execute((int) $request['id'], (string) $request['name']); return new \WP_REST_Response(['id'=>$account->id,'name'=>$account->name,'created_at'=>$account->createdAt->format('c')], 200); }
        catch (\InvalidArgumentException $e) { return \SGFP\REST\PublicError::response($e, 400, 'VALIDATION_ERROR'); }
        catch (\RuntimeException $e) { return \SGFP\REST\PublicError::response($e, $e->getCode() ?: 500); }
        catch (\Throwable $e) { return \SGFP\REST\PublicError::response($e, 500); }
    }

    public function get(): \WP_REST_Response
    {
        try {
            $accounts = $this->listService->execute();
            if (count($accounts) === 0) {
                return \SGFP\REST\PublicError::fromCode(404, 'NOT_FOUND');
            }
            if (count($accounts) > 1) {
                return \SGFP\REST\PublicError::fromCode(409, 'CONFLICT');
            }
            $account = $accounts[0];
            $initialBalance = $this->entryRepository->findActiveInitialBalanceByAccount($account->id, $account->userId);
            $balance = 0;
            foreach ($this->entryRepository->findActiveEntriesByUser($account->userId) as $entry) {
                $balance += ($entry->effectType->value === 'ENTRADA' ? 1 : -1) * \SGFP\Domain\Models\Decimal::cents($entry->amount);
            }
            return new \WP_REST_Response([
                'id' => $account->id,
                'name' => $account->name,
                'created_at' => $account->createdAt->format('c'),
                'balance' => \SGFP\Domain\Models\Decimal::formatCents($balance),
                'initial_balance_configured' => $initialBalance !== null,
            ], 200);
        } catch (\RuntimeException $e) {
            return \SGFP\REST\PublicError::response($e, $e->getCode() ?: 500);
        } catch (\Throwable $e) {
            return \SGFP\REST\PublicError::response($e, 500);
        }
    }

    public function list(): \WP_REST_Response
    {
        try {
            $accounts = $this->listService->execute();

            $data = array_map(fn ($account) => [
                'id' => $account->id,
                'name' => $account->name,
                'created_at' => $account->createdAt->format('c'),
            ], $accounts);

            return new \WP_REST_Response($data, 200);
        } catch (\RuntimeException $e) {
            return \SGFP\REST\PublicError::response($e, $e->getCode() ?: 500);
        } catch (\Throwable $e) {
            return \SGFP\REST\PublicError::response($e, 500);
        }
    }

    public function setInitialBalance(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $dto = SetInitialBalanceRequest::fromRequest($request);
            $dto->validate();

            $entry = $this->setInitialBalanceService->execute(
                (int) $request['id'],
                $dto->toDecimal(),
                $dto->name,
                $dto->description,
                $dto->toEffectiveMonth(),
            );

            return new \WP_REST_Response([
                'id' => $entry->id,
                'account_id' => $entry->accountId,
                'origin' => $entry->origin->value,
                'name' => $entry->name,
                'amount' => $entry->amount,
                'effect' => $entry->effectType->value,
                'effective_at' => $entry->settledAt->format('c'),
                'description' => $entry->description,
                'state' => $entry->state->value,
                'created_at' => $entry->createdAt->format('c'),
            ], 201);
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
