<?php

namespace BankApi\Models;

use Mateodioev\HttpRouter\Response;
use OpenApi\Attributes as OA;

#[OA\Schema(properties: [
    new OA\Property(property: 'ok', type: 'boolean', description: 'Indicates if the request was successful. Always true'),
    new OA\Property(
        property: 'data',
        type: 'mixed',
        description: 'Request content, can be User, Transaction or an array of these'
    )
])]
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
