<?php

declare(strict_types=1);

namespace SGFP\REST;

use SGFP\Application\Services\CreateAccountService;
use SGFP\Application\Services\CreateCategoryService;
use SGFP\Application\Services\ListAccountsService;
use SGFP\Application\Services\ListCategoriesService;
use SGFP\Application\Services\SeedCategoriesService;
use SGFP\Domain\Policies\AccountPolicy;
use SGFP\Infrastructure\WordPress\WpAccountRepository;
use SGFP\Infrastructure\WordPress\WpCategoryRepository;
use SGFP\Infrastructure\WordPress\WpUserContext;
use SGFP\REST\Controllers\AccountController;
use SGFP\REST\Controllers\CategoryController;

final class Routes
{
    private const NAMESPACE = 'sgfp/v1';

    public function register(): void
    {
        $userContext = new WpUserContext();
        $accountRepository = new WpAccountRepository();
        $categoryRepository = new WpCategoryRepository();
        $accountPolicy = new AccountPolicy($accountRepository);
        $seedCategories = new SeedCategoriesService($categoryRepository);

        $accountController = new AccountController(
            new CreateAccountService($accountRepository, $userContext, $accountPolicy, $seedCategories),
            new ListAccountsService($accountRepository, $userContext)
        );

        $categoryController = new CategoryController(
            new CreateCategoryService($categoryRepository, $userContext),
            new ListCategoriesService($categoryRepository, $userContext)
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
                'type' => [
                    'required' => true,
                    'type' => 'string',
                    'enum' => ['RECEITA', 'DESPESA'],
                ],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/categories', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [$categoryController, 'list'],
            'permission_callback' => [$categoryController, 'permissionCheck'],
        ]);
    }
}
