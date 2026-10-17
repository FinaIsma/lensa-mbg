<?php

namespace App\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ForeignKeyDefinition;

class Keys
{
    public const SPPG_TABLE = 'sppg';      // nama tabel SPPG
    public const SPPG_PK    = 'id_sppg';   // nama kolom primary key SPPG
    public const SPPG_TYPE  = 'bigint';    // tipe PK: 'bigint' | 'int' | 'uuid'
    public const SPPG_OWNER = 'id_user';   // kolom di tabel SPPG yang menunjuk ke user pemiliknya

    public const USER_TABLE = 'users';     // nama tabel user
    public const USER_PK    = 'id_user';   // nama kolom primary key user
    public const USER_TYPE  = 'bigint';    // tipe PK: 'bigint' | 'int' | 'uuid'

    public static function sppg(Blueprint $t, string $column = 'id_sppg', bool $nullable = false): ForeignKeyDefinition
    {
        return self::make($t, $column, self::SPPG_TYPE, self::SPPG_TABLE, self::SPPG_PK, $nullable);
    }

    public static function user(Blueprint $t, string $column, bool $nullable = false): ForeignKeyDefinition
    {
        return self::make($t, $column, self::USER_TYPE, self::USER_TABLE, self::USER_PK, $nullable);
    }

    public static function staff(Blueprint $t, string $column, bool $nullable = false): ForeignKeyDefinition
    {
        return self::to($t, $column, 'staff', $nullable);
    }

    public static function to(Blueprint $t, string $column, string $table, bool $nullable = false, bool $index = true): ForeignKeyDefinition
    {
        return self::make($t, $column, 'bigint', $table, 'id', $nullable, $index);
    }

    private static function make(
        Blueprint $t,
        string $column,
        string $type,
        string $refTable,
        string $refColumn,
        bool $nullable,
        bool $index = true
    ): ForeignKeyDefinition {
        $col = match ($type) {
            'uuid'  => $t->uuid($column),
            'int'   => $t->unsignedInteger($column),
            default => $t->unsignedBigInteger($column),
        };

        if ($nullable) {
            $col->nullable();
        }

        if ($index) {
            $col->index();
        }

        return $t->foreign($column)->references($refColumn)->on($refTable)->restrictOnDelete();
    }
}
