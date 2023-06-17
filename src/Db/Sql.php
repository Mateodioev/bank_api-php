<?php

namespace BankApi\Db;

use Mateodioev\Db\{Connection, Query};

class Sql
{
    protected static ?Query $db = null;

    public static function prepare(string $dir)
    {
        Connection::PrepareFromEnv($dir);
    }

    protected static function getDb(): Query
    {
        if (!self::$db instanceof Query) {
            self::$db = new Query();
        }

        return self::$db;
    }

    /**
     * Execute a sql query and return the first row
     */
    protected static function exec(string $query, array $args = []): array|false
    {
        $result = self::getDb()->Exec($query, $args);
        if (!$result['ok'])
            return false;

        unset($result['ok']);
        unset($result['obj']);
        return $result;
    }

    /**
     * Execute a sql query and return all rows
     */
    protected static function getAll(string $query, array $args = []): array
    {
        return self::getDb()->GetAll($query, $args)['rows'];
    }
}
