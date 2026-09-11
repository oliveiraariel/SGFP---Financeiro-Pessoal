<?php

declare(strict_types=1);

namespace SGFP\REST;

use SGFP\Application\Services\CreateAccountService;
use SGFP\Domain\Policies\AccountPolicy;
use SGFP\Infrastructure\WordPress\WpAccountRepository;
use SGFP\Infrastructure\WordPress\WpUserContext;
use SGFP\REST\Controllers\AccountController;

final class Routes
{
    private const NAMESPACE = 'sgfp/v1';

    public function register(): void
    {
        $userContext = new WpUserContext();
        $accountRepository = new WpAccountRepository();
        $accountPolicy = new AccountPolicy($accountRepository);

        $createAccountController = new AccountController(
            new CreateAccountService($accountRepository, $userContext, $accountPolicy)
        );

        register_rest_route(self::NAMESPACE, '/accounts', [
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => [$createAccountController, 'create'],
            'permission_callback' => [$createAccountController, 'permissionCheck'],
            'args' => [
                'name' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'role' => [
                    'required' => false,
                    'type' => 'string',
                    'enum' => ['PRINCIPAL', 'SECUNDARIA'],
                ],
            ],
        ]);
    }
}
