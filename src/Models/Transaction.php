<?php

namespace BankApi\Models;

use DateTime;
use OpenApi\Attributes as OA;

#[OA\Schema(type: 'object', title: 'Transaction')]
class Transaction
{
    #[OA\Property(type: 'string', description: 'Transaction unique id')]
    public string $id;

    #[OA\Property(type: 'integer', description: 'Transaction mount')]
    public ?int $mount = null;

    #[OA\Property(type: 'datetime', description: 'Transaction date')]
    public ?DateTime $created_at = null;

    #[OA\Property(type: 'string', description: 'User id')]
    public string $user_id;

    #[OA\Property(type: 'string', description: 'Target id')]
    public string $target_id;
}
