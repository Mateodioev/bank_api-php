<?php

namespace BankApi\Controllers;

use BankApi\Db\Sql;

class baseController extends Sql
{
    /**
     * Execute a sql query and return the first row
     */
    protected function sql(string $query, array $args = []): array
    {
        return self::exec($query, $args);
    }

    /**
     * Execute a sql query and return all rows
     */
    protected function sqlAll(string $query, array $args = []): array
    {
        return self::getAll($query, $args);
    }
}
