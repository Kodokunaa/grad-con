<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

final class PageContext
{
    public array $headers = [];

    public int $status = 200;

    public array $session = [];

    public array $post = [];

    public array $query = [];

    public function header(string $header, bool $replace = true, int $code = 0): void
    {
        [$name, $value] = array_pad(explode(':', $header, 2), 2, '');
        if (strtolower($name) === 'location') {
            $this->status = $code ?: 302;
        }
        $this->headers[trim($name)] = trim($value);
        if ($code) {
            $this->status = $code;
        }
    }

    public function pdo(): \PDO
    {
        $pdo = DB::connection()->getPdo();
        // MariaDB does not support native placeholders in legacy SHOW ... LIKE queries.
        $pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
        $pdo->setAttribute(\PDO::ATTR_STATEMENT_CLASS, [PasswordStatement::class]);

        return $pdo;
    }

    public function schemaChange(\PDO $pdo, string $sql): int|false
    {
        // Schema is installed by versioned migrations, never by HTTP requests.
        if (preg_match('/^\s*(CREATE|ALTER|DROP)\s/i', $sql)) {
            return 0;
        }

        return $pdo->exec($sql);
    }
}
