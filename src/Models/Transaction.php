<?php

namespace BankApi\Models;

use BankApi\Db\Sql;
use DateTime;
use Mateodioev\HttpRouter\exceptions\RequestException;
use OpenApi\Attributes as OA;

#[OA\Schema(type: 'object', title: 'Transaction')]
class Transaction extends Sql
{
    #[OA\Property(type: 'string', description: 'Transaction unique id')]
    public ?string $id = null;

    #[OA\Property(type: 'float', description: 'Transaction mount')]
    public ?float $mount = null;

    #[OA\Property(type: 'datetime', description: 'Transaction date')]
    public ?DateTime $created_at = null;

    #[OA\Property(type: 'string', description: 'User id')]
    public string $user_id;

    #[OA\Property(type: 'string', description: 'Target id')]
    public string $target_id;

    protected static function new(): static
    {
        return new static;
    }

    public static function find(string $id): static
    {
        return self::new()->setId($id)->load();
    }

    public static function fromArray(array $transaction): static
    {
        return self::new()
            ->setId($transaction['id'])
            ->setMount($transaction['mount'])
            ->setCreatedAt(new DateTime($transaction['created_at']))
            ->setUserId($transaction['user_id'])
            ->setTargetId($transaction['target_id']);
    }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'mount'      => $this->mount,
            'created_at' => $this->getCreatedAt()->format('Y-m-d H:i:s'),
            'user_id'    => $this->user_id,
            'target_id'  => $this->target_id,
        ];
    }

    protected function load()
    {
        try {
            $transaction = self::exec('SELECT * FROM transactions WHERE id = ?', [$this->id]);
        } catch (\Throwable) {
            throw new RequestException('Transaction not found', 404);
        }
        if (!$transaction)
            throw new RequestException('Transaction not found', 404);

        return self::fromArray($transaction['data']);
    }

    public function create(): static
    {
        try {
            if (self::exec('INSERT INTO transactions (id, mount, user_id, target_id) VALUES (?, ?, ?, ?)', [
                $this->id,
                $this->mount,
                $this->user_id,
                $this->target_id,
            ]) === false) {
                throw new RequestException('Transaction not created', 500);
            }
        } catch (\Throwable) {
            throw new RequestException('Duplicated transaction', 409);
        }
        return $this;
    }

    public function setId(string $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function setMount(float $mount): static
    {
        $this->mount = $mount;
        return $this;
    }

    public function setCreatedAt(DateTime $createdAt): static
    {
        $this->created_at = $createdAt;
        return $this;
    }

    public function setUserId(string $userId): static
    {
        $this->user_id = $userId;
        return $this;
    }

    public function setTargetId(string $targetId): static
    {
        $this->target_id = $targetId;
        return $this;
    }

    public function getCreatedAt(): DateTime
    {
        if ($this->created_at === null) {
            $date = self::exec('SELECT created_at FROM transactions WHERE id = ?', [$this->id]);
            if ($date === false) {
                throw new RequestException('Transaction not found', 404);
            }

            $this->created_at = new DateTime($date['data']['created_at']);
        }
        return $this->created_at;
    }
}
