<?php

declare(strict_types=1);

namespace SGFP\Infrastructure\WordPress;

use SGFP\Application\Ports\CategoryRepository;
use SGFP\Domain\Enums\CategoryType;
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
            'tipo' => $category->type->value,
        ];

        if ($category->id === null) {
            $result = $wpdb->insert(TableNames::category(), $data, ['%d', '%s', '%s']);

            if ($result === false) {
                throw new \RuntimeException('Falha ao criar categoria: ' . $wpdb->last_error);
            }

            return $category->withId((int) $wpdb->insert_id);
        }

        $wpdb->update(
            TableNames::category(),
            $data,
            ['id_categoria' => $category->id],
            ['%d', '%s', '%s'],
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
            "SELECT * FROM " . TableNames::category() . " WHERE fk_id_usuario = %d ORDER BY tipo, nome",
            $userId
        ), ARRAY_A);

        return array_map([$this, 'mapRow'], $rows ?: []);
    }

    public function existsByNameAndType(int $userId, string $name, CategoryType $type): bool
    {
        global $wpdb;

        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM " . TableNames::category() . " WHERE fk_id_usuario = %d AND nome = %s AND tipo = %s",
            $userId,
            $name,
            $type->value
        ));

        return (int) $count > 0;
    }

    public function seedDefaults(int $userId): void
    {
        $now = new \DateTimeImmutable();

        $defaults = [
            ['Salário', CategoryType::RECEITA],
            ['Investimentos', CategoryType::RECEITA],
            ['Outras Receitas', CategoryType::RECEITA],
            ['Moradia', CategoryType::DESPESA],
            ['Alimentação', CategoryType::DESPESA],
            ['Transporte', CategoryType::DESPESA],
            ['Saúde', CategoryType::DESPESA],
            ['Educação', CategoryType::DESPESA],
            ['Lazer', CategoryType::DESPESA],
            ['Vestuário', CategoryType::DESPESA],
            ['Serviços', CategoryType::DESPESA],
            ['Impostos', CategoryType::DESPESA],
            ['Outras Despesas', CategoryType::DESPESA],
        ];

        foreach ($defaults as [$name, $type]) {
            if (!$this->existsByNameAndType($userId, $name, $type)) {
                $this->save(Category::create($userId, $name, $type, $now));
            }
        }
    }

    private function mapRow(array $row): Category
    {
        return new Category(
            (int) $row['id_categoria'],
            (int) $row['fk_id_usuario'],
            $row['nome'],
            CategoryType::from($row['tipo']),
            new \DateTimeImmutable($row['criada_em'])
        );
    }
}
