<?php
declare(strict_types=1);

namespace {
    if (!class_exists('WP_REST_Request')) {
        final class WP_REST_Request implements \ArrayAccess
        {
            public function __construct(private array $params) {}

            public function offsetExists(mixed $offset): bool
            {
                return array_key_exists($offset, $this->params);
            }

            public function offsetGet(mixed $offset): mixed
            {
                return $this->params[$offset] ?? null;
            }

            public function offsetSet(mixed $offset, mixed $value): void
            {
                $this->params[$offset] = $value;
            }

            public function offsetUnset(mixed $offset): void
            {
                unset($this->params[$offset]);
            }
        }
    }
}

namespace SGFP\Tests\Unit\Commitment {
use PHPUnit\Framework\TestCase;
use SGFP\REST\DTOs\UpdateCommitmentRequest;
use SGFP\REST\DTOs\CreateCommitmentRequest;

final class CommitmentRequestTest extends TestCase
{
    public function testUpdateNormalizesAmountAndValidatesName(): void
    {
        $dto = new UpdateCommitmentRequest(null, 'Conta', '1.234,56');
        $dto->validate();
        self::assertSame('1234.56', $dto->normalizedAmount());
    }

    public function testCreateNormalizesAmountAndAllowsOmittedOptionalFields(): void
    {
        $dto = new CreateCommitmentRequest(null, 'Conta de Luz', '123,55', 'SAIDA', '2026-09-15', null);
        $dto->validate();

        self::assertSame('123.55', $dto->normalizedAmount());
        self::assertNull($dto->categoryId);
        self::assertNull($dto->recurrenceMonthsCount);
    }

    public function testCreateAllowsIndefiniteRecurrenceWithoutRecurrenceCount(): void
    {
        $dto = new CreateCommitmentRequest(null, 'Conta', '10.00', 'SAIDA', '2026-09-01', null, true);

        $dto->validate();

        self::assertNull($dto->recurrenceMonthsCount);
    }

    public function testRequestBoundaryPreservesExplicitNoRecurrenceWithResidualCount(): void
    {
        $dto = CreateCommitmentRequest::fromRequest(new \WP_REST_Request([
            'name' => 'Conta de luz',
            'amount' => '10.00',
            'nature' => 'SAIDA',
            'commitment_date' => '2026-09-15',
            'recurrence_months_count' => 12,
            'recurrence_enabled' => false,
        ]));

        $dto->validate();

        self::assertFalse($dto->recurrenceEnabled);
        self::assertSame(12, $dto->recurrenceMonthsCount);
    }

    public function testCreateAcceptsDailyCommitmentDate(): void
    {
        $dto = new CreateCommitmentRequest(null, 'Conta', '10.00', 'SAIDA', '2026-02-28', null);
        $dto->validate();
        self::assertSame('2026-02-28', $dto->commitmentDate);
    }

    public function testUpdateAcceptsAndPreservesDailyCommitmentDate(): void
    {
        $dto = new UpdateCommitmentRequest(null, 'Conta', '10,00', '2026-02-28');

        $dto->validate();

        self::assertSame('2026-02-28', $dto->commitmentDate);
    }

    public function testUpdateRejectsCalendarInvalidDailyCommitmentDate(): void
    {
        $dto = new UpdateCommitmentRequest(null, 'Conta', '10,00', '2026-02-31');

        $this->expectException(\InvalidArgumentException::class);
        $dto->validate();
    }
}
}
