<?php

declare(strict_types=1);

namespace SGFP\REST;

final class PublicError
{
    private const MAX_DETAILS_DEPTH = 3;

    /** @var list<string> */
    private const DETAIL_KEYS = ['field', 'fields', 'reason', 'type'];

    /** @var array<string,string> */
    private const MESSAGES = [
        'VALIDATION_ERROR' => 'Dados inválidos.',
        'REQUEST_ERROR' => 'Erro na requisição.',
        'NOT_FOUND' => 'Recurso não encontrado.',
        'INVALID_STATE' => 'Operação não permitida no estado atual.',
        'UNAUTHORIZED' => 'Não autorizado.',
        'FORBIDDEN' => 'Acesso negado.',
        'CONFLICT' => 'Conflito ao processar a operação.',
    ];

    /** @return array{code:string,message:string,details:mixed,correlation_id:string} */
    public static function contract(mixed $error, int $status, string $correlationId): array
    {
        $correlationId = self::normalizeCorrelationId($correlationId);

        if ($status >= 500) {
            error_log(sprintf('SGFP REST failure [%s]', $correlationId));
            return [
                'code' => 'INTERNAL_ERROR',
                'message' => 'Erro interno.',
                'details' => null,
                'correlation_id' => $correlationId,
            ];
        }

        if (is_array($error)) {
            $code = (string) ($error['code'] ?? 'REQUEST_ERROR');
            if (!array_key_exists($code, self::MESSAGES)) {
                $code = match ($status) {
                    401 => 'UNAUTHORIZED',
                    403 => 'FORBIDDEN',
                    404 => 'NOT_FOUND',
                    409 => 'CONFLICT',
                    default => 'REQUEST_ERROR',
                };
            }
            return [
                'code' => $code,
                'message' => self::MESSAGES[$code],
                'details' => self::safeDetails($error['details'] ?? null),
                'correlation_id' => $correlationId,
            ];
        }

        return [
            'code' => 'REQUEST_ERROR',
            'message' => self::MESSAGES['REQUEST_ERROR'],
            'details' => null,
            'correlation_id' => $correlationId,
        ];
    }

    public static function response(\Throwable $error, int $status, ?string $code = null): \WP_REST_Response
    {
        $resolvedStatus = $status > 0 ? $status : 500;
        return new \WP_REST_Response([
            'error' => self::contract(['code' => $code ?? self::codeForStatus($resolvedStatus)], $resolvedStatus, ''),
        ], $resolvedStatus);
    }

    public static function fromCode(int $status, ?string $code = null): \WP_REST_Response
    {
        $resolvedStatus = $status > 0 ? $status : 500;
        return new \WP_REST_Response([
            'error' => self::contract(['code' => $code ?? self::codeForStatus($resolvedStatus)], $resolvedStatus, ''),
        ], $resolvedStatus);
    }

    private static function codeForStatus(int $status): string
    {
        return match ($status) {
            401 => 'UNAUTHORIZED',
            403 => 'FORBIDDEN',
            404 => 'NOT_FOUND',
            409 => 'CONFLICT',
            default => $status >= 500 ? 'INTERNAL_ERROR' : 'REQUEST_ERROR',
        };
    }

    private static function safeDetails(mixed $details, int $depth = 0): ?array
    {
        if (!is_array($details) || $depth >= self::MAX_DETAILS_DEPTH) {
            return null;
        }

        $safe = [];
        foreach ($details as $key => $value) {
            if (!is_string($key) || !in_array($key, self::DETAIL_KEYS, true)) {
                continue;
            }

            if (is_array($value)) {
                $nested = array_is_list($value)
                    ? self::safeDetailsList($value, $depth + 1)
                    : self::safeDetails($value, $depth + 1);
                if ($nested !== null) {
                    $safe[$key] = $nested;
                }
                continue;
            }

            if (is_string($value) || is_int($value) || is_float($value) || is_bool($value)) {
                $safe[$key] = $value;
            }
        }

        return $safe === [] ? null : $safe;
    }

    private static function safeDetailsList(array $details, int $depth): ?array
    {
        $safe = [];
        foreach ($details as $value) {
            if (is_array($value)) {
                $item = self::safeDetails($value, $depth);
                if ($item !== null) {
                    $safe[] = $item;
                }
            } elseif (is_string($value) || is_int($value) || is_float($value) || is_bool($value)) {
                $safe[] = $value;
            }
        }

        return $safe === [] ? null : $safe;
    }

    private static function normalizeCorrelationId(string $correlationId): string
    {
        if (preg_match('/^[A-Za-z0-9._:-]{1,128}$/', $correlationId) === 1) {
            return $correlationId;
        }

        try {
            return bin2hex(random_bytes(16));
        } catch (\Throwable) {
            return uniqid('sgfp-', true);
        }
    }
}
