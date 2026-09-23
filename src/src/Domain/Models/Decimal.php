<?php
declare(strict_types=1);
namespace SGFP\Domain\Models;
final class Decimal
{
    public static function normalize(string $value, bool $allowNegative = false): string
    {
        $value = trim($value);
        $sign = '';
        if ($allowNegative && str_starts_with($value, '-')) {
            $sign = '-';
            $value = substr($value, 1);
        }
        if (preg_match('/^(?:\d+|\d{1,3}(?:\.\d{3})+)(?:,\d{2}|\.\d{2})$/', $value) !== 1) {
            throw new \InvalidArgumentException('Valor decimal inválido.');
        }
        if (str_contains($value, ',')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif (substr_count($value, '.') > 1) {
            $last = strrpos($value, '.');
            $value = str_replace('.', '', substr($value, 0, $last)) . substr($value, $last);
        }
        [$whole, $fraction] = explode('.', $value, 2);
        $whole = ltrim($whole, '0') ?: '0';
        if (!$allowNegative && $sign === '-') {
            throw new \InvalidArgumentException('Valor decimal inválido.');
        }
        if ($whole === '0' && $fraction === '00' && $sign === '-') {
            $sign = '';
        }
        return $sign . $whole . '.' . $fraction;
    }

    public static function cents(string $value): int
    {
        $normalized = self::normalize($value, true);
        $negative = str_starts_with($normalized, '-');
        $normalized = ltrim($normalized, '-');
        [$whole, $fraction] = explode('.', $normalized, 2);
        $cents = (int) $whole * 100 + (int) $fraction;
        return $negative ? -$cents : $cents;
    }
    public static function formatCents(int $cents): string { $sign = $cents < 0 ? '-' : ''; $cents = abs($cents); return $sign . intdiv($cents, 100) . '.' . str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT); }
}
