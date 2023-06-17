<?php

namespace BankApi\Models;

use Mateodioev\HttpRouter\Response;

class Error
{
    public static function json(string $message, int $status = 400): Response
    {
        return Response::json([
            'ok'   => false,
            'error' => [
                'code'    => $status,
                'message' => $message
            ]
        ])->setStatus($status);
    }

    public static function text(string $message, int $status = 400): Response
    {
        return Response::text($message)->setStatus($status);
    }
}
