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
        private readonly \SGFP\Application\Services\RenameCategoryService $renameService,
        private readonly \SGFP\Application\Services\DeleteCategoryService $deleteService,
    ) {
    }

    public function rename(\WP_REST_Request $request): \WP_REST_Response { try { $c=$this->renameService->execute((int)$request['id'],(string)$request['name']); return new \WP_REST_Response(['id'=>$c->id,'name'=>$c->name,'created_at'=>$c->createdAt->format('c')],200); } catch (\InvalidArgumentException $e) { return new \WP_REST_Response(['error'=>$e->getMessage()],400); } catch (\RuntimeException $e) { return new \WP_REST_Response(['error'=>$e->getMessage()],$e->getCode()?:500); } catch (\Throwable) { return new \WP_REST_Response(['error'=>'Erro interno.'],500); } }
    public function delete(\WP_REST_Request $request): \WP_REST_Response { try { $this->deleteService->execute((int)$request['id']); return new \WP_REST_Response(null,204); } catch (\RuntimeException $e) { return new \WP_REST_Response(['error'=>$e->getMessage()],$e->getCode()?:500); } catch (\Throwable) { return new \WP_REST_Response(['error'=>'Erro interno.'],500); } }

    public function create(\WP_REST_Request $request): \WP_REST_Response
    {
        try {
            $category = $this->createService->execute(
                (string) ($request['name'] ?? '')
            );

            return new \WP_REST_Response([
                'id' => $category->id,
                'name' => $category->name,
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
