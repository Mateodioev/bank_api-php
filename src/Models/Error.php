<?php

namespace BankApi\Models;

use Mateodioev\HttpRouter\Response;
use OpenApi\Attributes as OA;

#[OA\Schema(properties: [
    new OA\Property(property: 'ok', type: 'boolean', description: 'Indicates if the request was successful. Always false'),
    new OA\Property(
        property: 'error',
        type: 'object',
        properties: [
            new OA\Property(property: 'code', type: 'integer', description: 'HTTP status code'),
            new OA\Property(property: 'message', type: 'string', description: 'Error message')
        ]
    )
])]
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
