<?php

declare(strict_types=1);

namespace SGFP\REST;

use SGFP\Application\Services\CreateAccountService;
use SGFP\Application\Services\CreateCategoryService;
use SGFP\Application\Services\CreateCommitmentService;
use SGFP\Application\Services\ListAccountsService;
use SGFP\Application\Services\ListCategoriesService;
use SGFP\Application\Services\SeedCategoriesService;
use SGFP\Application\Services\SetInitialBalanceService;
use SGFP\Application\Services\SettleCommitmentService;
use SGFP\Domain\Policies\AccountPolicy;
use SGFP\Infrastructure\WordPress\WpAccountRepository;
use SGFP\Infrastructure\WordPress\WpCategoryRepository;
use SGFP\Infrastructure\WordPress\WpCommitmentRepository;
use SGFP\Infrastructure\WordPress\WpEntryRepository;
use SGFP\Infrastructure\WordPress\WpTransactionManager;
use SGFP\Infrastructure\WordPress\WpUserContext;
use SGFP\REST\Controllers\AccountController;
use SGFP\REST\Controllers\CategoryController;
use SGFP\REST\Controllers\CommitmentController;

final class Routes
{
    private const NAMESPACE = 'sgfp/v1';

    public function register(): void
    {
        $userContext = new WpUserContext();
        $accountRepository = new WpAccountRepository();
        $categoryRepository = new WpCategoryRepository();
        $commitmentRepository = new WpCommitmentRepository();
        $entryRepository = new WpEntryRepository();
        $transactionManager = new WpTransactionManager();
        $accountPolicy = new AccountPolicy($accountRepository);
        $seedCategories = new SeedCategoriesService($categoryRepository);

        $accountController = new AccountController(
            new CreateAccountService($accountRepository, $userContext, $accountPolicy, $seedCategories),
            new ListAccountsService($accountRepository, $userContext),
            new SetInitialBalanceService($accountRepository, $entryRepository, $transactionManager, $userContext)
        );

        $categoryController = new CategoryController(
            new CreateCategoryService($categoryRepository, $userContext),
            new ListCategoriesService($categoryRepository, $userContext)
        );

        $commitmentController = new CommitmentController(
            new CreateCommitmentService($commitmentRepository, $categoryRepository, $userContext),
            new SettleCommitmentService($commitmentRepository, $entryRepository, $accountRepository, $transactionManager, $userContext)
        );

        register_rest_route(self::NAMESPACE, '/accounts', [
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => [$accountController, 'create'],
            'permission_callback' => [$accountController, 'permissionCheck'],
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

        register_rest_route(self::NAMESPACE, '/accounts', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [$accountController, 'list'],
            'permission_callback' => [$accountController, 'permissionCheck'],
        ]);

        register_rest_route(self::NAMESPACE, '/accounts/(?P<id>\d+)/initial-balance', [
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => [$accountController, 'setInitialBalance'],
            'permission_callback' => [$accountController, 'permissionCheck'],
            'args' => [
                'id' => [
                    'required' => true,
                    'type' => 'integer',
                ],
                'amount' => [
                    'required' => true,
                    'type' => 'string',
                ],
                'name' => [
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'description' => [
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_textarea_field',
                ],
                'effective_month' => [
                    'required' => false,
                    'type' => 'string',
                    'pattern' => '^\d{4}-\d{2}$',
                ],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/categories', [
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => [$categoryController, 'create'],
            'permission_callback' => [$categoryController, 'permissionCheck'],
            'args' => [
                'name' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/categories', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [$categoryController, 'list'],
            'permission_callback' => [$categoryController, 'permissionCheck'],
        ]);

        register_rest_route(self::NAMESPACE, '/commitments', [
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => [$commitmentController, 'create'],
            'permission_callback' => [$commitmentController, 'permissionCheck'],
            'args' => [
                'category_id' => [
                    'required' => false,
                    'type' => 'integer',
                ],
                'name' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'amount' => [
                    'required' => true,
                    'type' => 'number',
                ],
                'type' => [
                    'required' => true,
                    'type' => 'string',
                    'enum' => ['PADRAO', 'TRANSFERENCIA'],
                ],
                'nature' => [
                    'required' => true,
                    'type' => 'string',
                    'enum' => ['ENTRADA', 'SAIDA'],
                ],
                'reference_month' => [
                    'required' => true,
                    'type' => 'string',
                    'pattern' => '^\d{4}-\d{2}$',
                ],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/commitments/(?P<id>\d+)/settle', [
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => [$commitmentController, 'settle'],
            'permission_callback' => [$commitmentController, 'permissionCheck'],
            'args' => [
                'id' => [
                    'required' => true,
                    'type' => 'integer',
                ],
            ],
        ]);
    }
}
