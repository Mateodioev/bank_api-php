<?php

namespace BankApi\Models;

use BankApi\Db\Sql;
use Mateodioev\HttpRouter\exceptions\RequestException;

use function BankApi\genUUIDv4;

class User extends Sql
{
    public string $id;
    public string $name;
    public float $balance = 0;

    /**
     * @var Transaction[]
     */
    public array $transactions = [];

    public static function create(string $name, ?string $id = null)
    {
        return (new static)
            ->setName($name)
            ->setId($id ?? genUUIDv4());
    }

    public static function find(string $id): ?User
    {
        $user = new static;
        $user->setId($id);
        return $user->load();
    }

    public function toArray(): array
    {
        return [
            'id'      => $this->id,
            'name'    => $this->name,
            'balance' => $this->balance,
        ];
    }

    private function load(): static
    {
        $user = self::exec('SELECT * FROM users WHERE id = ?', [$this->id]);
        if (!$user)
            throw new RequestException('User not found', 404);

        $user = $user['data'];
        return $this->setName($user['nombre'])
            ->setBalance($user['saldo']);
    }

    public function save(): static
    {
        self::exec('INSERT INTO users (id, nombre, saldo) VALUES (?, ?, ?)', [$this->id, $this->name, $this->balance]);
        return $this;
    }

    public function update()
    {
        self::exec('UPDATE users SET nombre = ?, saldo = ? WHERE id = ?', [$this->name, $this->balance, $this->id]);
        return $this;
    }

    public function findTransactions(): array
    {
        $transactions = self::getAll('SELECT * FROM transactions WHERE user_id = ?', [$this->id]);
        return $this->transactions;
    }

    public function setId(string $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function setBalance(float $balance): static
    {
        $this->balance = $balance;
        return $this;
    }
}
