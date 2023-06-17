<?php

namespace BankApi\Models;

use Mateodioev\HttpRouter\Response;

class Success
{
    public static function json(array $body): Response
    {
        return Response::json([
            'ok'   => true,
            'data' => $body
        ]);
    }

    public static function text(string $text): Response
    {
        return Response::text($text);
    }
}
