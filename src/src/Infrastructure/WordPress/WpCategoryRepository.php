<?php

declare(strict_types=1);

namespace SGFP\Infrastructure\WordPress;

use SGFP\Application\Ports\CategoryRepository;
use SGFP\Domain\Models\Category;
use SGFP\Infrastructure\Database\TableNames;

final class WpCategoryRepository implements CategoryRepository
{
    public function save(Category $category): Category
    {
        global $wpdb;

        $data = [
            'fk_id_usuario' => $category->userId,
            'nome' => $category->name,
        ];

        if ($category->id === null) {
            $result = $wpdb->insert(TableNames::category(), $data, ['%d', '%s']);

            if ($result === false) {
                throw new \RuntimeException('Falha ao criar categoria: ' . $wpdb->last_error);
            }

            return $category->withId((int) $wpdb->insert_id);
        }

        $wpdb->update(
            TableNames::category(),
            $data,
            ['id_categoria' => $category->id],
            ['%d', '%s'],
            ['%d']
        );

        return $category;
    }

    public function findById(int $id, int $userId): ?Category
    {
        global $wpdb;

        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . TableNames::category() . " WHERE id_categoria = %d AND fk_id_usuario = %d",
            $id,
            $userId
        ), ARRAY_A);

        return $row ? $this->mapRow($row) : null;
    }

    public function findAllByUser(int $userId): array
    {
        global $wpdb;

        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM " . TableNames::category() . " WHERE fk_id_usuario = %d ORDER BY nome",
            $userId
        ), ARRAY_A);

        return array_map([$this, 'mapRow'], $rows ?: []);
    }

    public function existsByName(int $userId, string $name): bool
    {
        global $wpdb;

        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM " . TableNames::category() . " WHERE fk_id_usuario = %d AND nome = %s",
            $userId,
            $name
        ));

        return (int) $count > 0;
    }

    public function seedDefaults(int $userId): void
    {
        $now = new \DateTimeImmutable();

        $defaults = [
            'Salário',
            'Investimentos',
            'Outras Receitas',
            'Moradia',
            'Alimentação',
            'Transporte',
            'Saúde',
            'Educação',
            'Lazer',
            'Vestuário',
            'Serviços',
            'Impostos',
            'Outras Despesas',
        ];

        foreach ($defaults as $name) {
            if (!$this->existsByName($userId, $name)) {
                $this->save(Category::create($userId, $name, $now));
            }
        }
    }

    private function mapRow(array $row): Category
    {
        return new Category(
            (int) $row['id_categoria'],
            (int) $row['fk_id_usuario'],
            $row['nome'],
            new \DateTimeImmutable($row['criada_em'])
        );
    }
}
