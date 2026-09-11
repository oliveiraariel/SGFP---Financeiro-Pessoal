<?php

declare(strict_types=1);

namespace SGFP\Infrastructure\Database;

final class TableNames
{
    public static function prefix(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'sgfp_';
    }

    public static function account(): string
    {
        return self::prefix() . 'conta_financeira';
    }

    public static function category(): string
    {
        return self::prefix() . 'categoria';
    }

    public static function recurrence(): string
    {
        return self::prefix() . 'recorrencia';
    }

    public static function commitment(): string
    {
        return self::prefix() . 'compromisso_financeiro';
    }

    public static function entry(): string
    {
        return self::prefix() . 'lancamento_financeiro';
    }

    public static function transfer(): string
    {
        return self::prefix() . 'transferencia';
    }
}
