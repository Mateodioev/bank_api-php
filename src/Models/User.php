<?php

namespace BankApi\Models;

use BankApi\Db\Sql;
use DateTime;
use Mateodioev\HttpRouter\exceptions\RequestException;
use OpenApi\Attributes as OA;

use function BankApi\genUUIDv4;

#[OA\Schema(type: 'object', title: 'User')]
class User extends Sql
{
    #[OA\Property(type: 'string', description: 'User unique id')]
    public ?string $id = null;

    #[OA\Property(type: 'string', description: 'User name')]
    public string $name;

    #[OA\Property(type: 'float', description: 'User balance')]
    public float $balance = 0;
    #[OA\Property(type: 'datetime', description: 'User created date')]
    public ?DateTime $created_at = null;

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

    public static function find(string $id): User
    {
        $user = new static;
        $user->setId($id);
        return $user->load();
    }

    public function toArray(): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'balance'   => $this->balance,
            'create_at' => $this->getCreateAt()->format('Y-m-d H:i:s'),
        ];
    }

    private function load(): static
    {
        try {
            $user = self::exec('SELECT * FROM users WHERE id = ?', [$this->id]);
        } catch (\Throwable) {
            throw new RequestException('User not found', 404);
        }
        if (!$user)
            throw new RequestException('User not found', 404);

        $user = $user['data'];
        return $this->setName($user['nombre'])
            ->setBalance($user['saldo'])
            ->setCreatedAt(new DateTime($user['created_at']));
    }

    public function save(): static
    {
        try {
            $r = self::exec('INSERT INTO users (id, nombre, saldo) VALUES (?, ?, ?)', [$this->id, $this->name, $this->balance]);
        } catch (\Throwable) {
            throw new RequestException('Duplicate user', 409);
        }
        if (!$r) throw new RequestException('Fail to save user', 500);

        return $this;
    }

    public function update()
    {
        try {
            self::exec('UPDATE users SET nombre = ?, saldo = ? WHERE id = ?', [$this->name, $this->balance, $this->id]);
        } catch (\Throwable) {
            throw new RequestException('Fail to update user', 500);
        }
        return $this;
    }

    public function delete(): bool
    {
        try {
            if (self::exec('DELETE FROM users WHERE id = ?', [$this->id]) === false) {
                return false;
            }
            return true;
        } catch (\Throwable) {
            throw new RequestException('Fail to delete user', 500);
        }
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

    public function setCreatedAt(DateTime $createdAt): static
    {
        $this->created_at = $createdAt;
        return $this;
    }

    public function getCreateAt(): DateTime
    {
        if ($this->created_at === null) {
            $date = self::exec('SELECT created_at FROM users WHERE id = ?', [$this->id]);
            if ($date === false) {
                throw new RequestException('User not found', 404);
            }
            $this->created_at = new DateTime($date['data']['created_at']);
        }
        return $this->created_at;
    }
}
