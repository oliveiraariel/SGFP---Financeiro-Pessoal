<?php

declare(strict_types=1);

namespace SGFP\REST\Controllers;

use SGFP\Application\Services\CreateCategoryService;
use SGFP\Application\Services\ListCategoriesService;

final class CategoryController
{
    public function __construct(
        private readonly CreateCategoryService $createService,
        private readonly ListCategoriesService $listService,
    ) {
    }

    public function create(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $category = $this->createService->execute(
                (string) ($request['name'] ?? ''),
                (string) ($request['type'] ?? '')
            );

            return new \WP_REST_Response([
                'id' => $category->id,
                'name' => $category->name,
                'type' => $category->type->value,
                'created_at' => $category->createdAt->format('c'),
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
            $categories = $this->listService->execute();

            $data = array_map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'type' => $category->type->value,
                'created_at' => $category->createdAt->format('c'),
            ], $categories);

            return new \WP_REST_Response($data, 200);
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
