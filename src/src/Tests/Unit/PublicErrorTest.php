<?php

declare(strict_types=1);

namespace SGFP\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SGFP\REST\PublicError;

final class PublicErrorResponseStub
{
    /** @var array<string,string> */
    private array $headers = [];

    public function __construct(
        private mixed $data = null,
        private int $status = 200,
    ) {
    }

    public function get_data(): mixed
    {
        return $this->data;
    }

    public function set_data(mixed $data): void
    {
        $this->data = $data;
    }

    public function get_status(): int
    {
        return $this->status;
    }

    public function header(string $name, string $value): void
    {
        $this->headers[$name] = $value;
    }
}

if (!class_exists('WP_REST_Response')) {
    class_alias(PublicErrorResponseStub::class, 'WP_REST_Response');
}

final class PublicErrorTest extends TestCase
{
    public function testInternalErrorsNeverExposeInfrastructureMessage(): void
    {
        $result = PublicError::contract('Falha SQL: senha=secreta', 500, 'abc-123');

        self::assertSame('INTERNAL_ERROR', $result['code']);
        self::assertSame('Erro interno.', $result['message']);
        self::assertStringNotContainsString('senha', $result['message']);
        self::assertSame('abc-123', $result['correlation_id']);
    }

    public function testInternalErrorLogDoesNotIncludeRawErrorMessage(): void
    {
        $source = file_get_contents((new \ReflectionClass(PublicError::class))->getFileName());

        self::assertIsString($source);
        self::assertStringContainsString("error_log(sprintf('SGFP REST failure [%s]', " . '$correlationId' . "));", $source);
        self::assertStringNotContainsString("error_log(sprintf('SGFP REST failure [%s]: %s'", $source);
    }

    public function testClientErrorsUseAllowlistedPublicMessage(): void
    {
        $result = PublicError::contract(['code' => 'VALIDATION_ERROR', 'message' => 'Campo inválido.'], 400, 'abc');

        self::assertSame('VALIDATION_ERROR', $result['code']);
        self::assertSame('Dados inválidos.', $result['message']);
    }

    public function testUnknownCodeFallsBackAccordingToStatus(): void
    {
        $result = PublicError::contract(['code' => 'SQLSTATE[secret]'], 404, 'abc');

        self::assertSame('NOT_FOUND', $result['code']);
        self::assertSame('Recurso não encontrado.', $result['message']);
        self::assertStringNotContainsString('SQLSTATE', $result['message']);
    }

    public function testScalarClientErrorNeverBecomesPublicMessage(): void
    {
        $result = PublicError::contract('Falha SQL: password=secret', 400, 'abc');

        self::assertSame('REQUEST_ERROR', $result['code']);
        self::assertSame('Erro na requisição.', $result['message']);
        self::assertSame('abc', $result['correlation_id']);
    }

    public function testOnlyStructuredDetailsCrossThePublicBoundary(): void
    {
        $result = PublicError::contract([
            'code' => 'VALIDATION_ERROR',
            'message' => 'internal detail must not become the message',
            'details' => ['field' => 'name'],
        ], 400, 'abc');

        self::assertSame(['field' => 'name'], $result['details']);

        $scalarDetails = PublicError::contract([
            'code' => 'VALIDATION_ERROR',
            'details' => 'SQLSTATE[secret]',
        ], 400, 'abc');

        self::assertNull($scalarDetails['details']);
    }

    public function testDetailsAreRecursivelyAllowlisted(): void
    {
        $result = PublicError::contract([
            'code' => 'VALIDATION_ERROR',
            'details' => [
                'field' => 'amount',
                'path' => '/amount',
                'nested' => ['field' => 'currency', 'id' => 42],
                'fields' => [['field' => 'name', 'type' => 'required', 'path' => '/name']],
            ],
        ], 400, 'abc');

        self::assertSame([
            'field' => 'amount',
            'fields' => [['field' => 'name', 'type' => 'required']],
        ], $result['details']);
    }

    public function testInvalidCorrelationIdIsReplacedWithSafeIdentifier(): void
    {
        $result = PublicError::contract([], 400, "bad\ncorrelation");

        self::assertMatchesRegularExpression('/^[A-Za-z0-9._:-]{1,128}$/', $result['correlation_id']);
        self::assertNotSame("bad\ncorrelation", $result['correlation_id']);
    }

    public function testFromCodeBuildsNotFoundEnvelopeWithCorrelationId(): void
    {
        $response = PublicError::fromCode(404, 'NOT_FOUND');
        $data = $response->get_data();

        self::assertSame(404, $response->get_status());
        self::assertIsArray($data);
        self::assertSame('NOT_FOUND', $data['error']['code']);
        self::assertSame('Recurso não encontrado.', $data['error']['message']);
        self::assertNull($data['error']['details']);
        self::assertMatchesRegularExpression('/^[A-Za-z0-9._:-]{1,128}$/', $data['error']['correlation_id']);
    }

    public function testPublicControllersDoNotMaterializeExceptionMessages(): void
    {
        $controllers = glob(__DIR__ . '/../../REST/Controllers/*Controller.php');
        self::assertNotFalse($controllers);

        foreach ($controllers as $controller) {
            $source = file_get_contents($controller);
            self::assertIsString($source);
            self::assertStringNotContainsString('getMessage()', $source, $controller);
        }
    }

    public function testPublicControllersDoNotEmitLegacyStringErrorEnvelopes(): void
    {
        $controllers = glob(__DIR__ . '/../../REST/Controllers/*Controller.php');
        self::assertNotFalse($controllers);

        foreach ($controllers as $controller) {
            $source = file_get_contents($controller);
            self::assertIsString($source);
            self::assertStringNotContainsString("['error' => '", $source, $controller);
            self::assertStringNotContainsString("['error'=>'", $source, $controller);
            self::assertStringNotContainsString("['error' => \"", $source, $controller);
        }
    }

    public function testPublicControllersBindUnexpectedErrorsBeforeNormalizingThem(): void
    {
        $controllers = glob(__DIR__ . '/../../REST/Controllers/*Controller.php');
        self::assertNotFalse($controllers);

        foreach ($controllers as $controller) {
            $source = file_get_contents($controller);
            self::assertIsString($source);
            self::assertStringNotContainsString('catch (\\Throwable) {', $source, $controller);
        }
    }
}
