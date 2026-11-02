<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class LegacyPassword
{
    private const SALT_1 = 'qv&lm*';
    private const SALT_2 = 'pl@!x';

    public static function make(string $password): string
    {
        return hash('ripemd128', self::SALT_1 . self::sanitize($password) . self::SALT_2);
    }

    public static function check(string $password, string $storedHash): bool
    {
        return hash_equals($storedHash, self::make($password));
    }

    private static function sanitize(string $value): string
    {
        $value = strip_tags($value);
        $value = htmlentities($value);
        $value = stripslashes($value);

        $quoted = DB::connection()->getPdo()->quote($value);

        if ($quoted === false) {
            throw new RuntimeException('Unable to escape the password using the database connection.');
        }

        return substr($quoted, 1, -1);
    }
}
