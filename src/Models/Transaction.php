<?php

namespace BankApi\Models;

use DateTime;

class Transaction
{
    public string $id;
    public string $type;
    public int $mount = null;
    public DateTime $time = null;
}
