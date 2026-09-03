<?php

namespace App\Support;

use Illuminate\Support\Facades\Hash;

/** Safety boundary for retained parameterized SQL while workflows move to models. */
final class PasswordStatement extends \PDOStatement
{
    protected function __construct() {}

    public function execute(?array $params = null): bool
    {
        $sql = $this->queryString;
        $index = null;
        if (preg_match('/INSERT\s+INTO\s+`?users`?\s*\((.*?)\)\s*VALUES\s*\((.*?)\)/is', $sql, $m)) {
            $columns = array_map(fn ($s) => trim($s, " \r\n\t`"), explode(',', $m[1]));
            $values = str_getcsv($m[2], ',', "'", '\\');
            $position = array_search('password', $columns, true);
            if ($position !== false && trim($values[$position] ?? '') === '?') {
                $index = substr_count(implode(',', array_slice($values, 0, $position)), '?');
            }
        } elseif (preg_match('/UPDATE\s+`?users`?\s+SET\s+(.*?)\bpassword\s*=\s*\?/is', $sql, $m)) {
            $index = substr_count($m[1], '?');
        }
        if ($index !== null && isset($params[$index])) {
            $password = (string) $params[$index];
            if (password_get_info($password)['algo'] === null) {
                $params[$index] = Hash::make($password);
            }
        }

        return parent::execute($params);
    }
}
