<?php

namespace BankApi\Controllers;

use BankApi\Models\{Error, Success, Transaction, User};
use Mateodioev\HttpRouter\{Request, Response};

use OpenApi\Attributes as OA;

use function BankApi\genUUIDv4;

class TransactionController extends baseController
{
    #[OA\Get(
        path: '/api/transactions/{id}',
        description: 'Get transaction by id',
        tags: ['Transactions'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Transaction id', schema: new OA\Schema(type: 'string'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Transaction',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(type: 'boolean', property: 'ok'),
                        new OA\Property(type: 'object', property: 'data', ref: '#/components/schemas/Transaction'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Transaction not found',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function byId(Request $r): Response
    {
        return Success::json(
            Transaction::find($r->param('id'))
                ->toArray()
        );
    }

    #[OA\Post(
        path: '/api/transactions/',
        description: 'Create new transaction',
        tags: ['Transactions'],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(type: 'float', property: 'mount', description: 'Transaction mount'),
                    new OA\Property(type: 'string', property: 'user_id', description: 'User id'),
                    new OA\Property(type: 'string', property: 'target_id', description: 'User id target'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Transaction created',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(type: 'boolean', property: 'ok'),
                        new OA\Property(type: 'object', property: 'data', ref: '#/components/schemas/Transaction'),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Insufficient user funds',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 404,
                description: 'User not found',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 409,
                description: 'Duplicated transaction',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 500,
                description: 'Fail to update user info / Transaction not created',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function create(Request $r): Response
    {
        /**
         * @var Transaction $transaction
         */
        $transaction = (new \JsonMapper)->map(json_decode($r->body()), Transaction::class);
        if ($transaction->id === null) $transaction->setId(genUUIDv4());

        $user   = User::find($transaction->user_id);
        $target = User::find($transaction->target_id);

        if ($user->balance < $transaction->mount)
            return Error::json('Insufficient funds', 400);

        $user->balance -= $transaction->mount;
        $target->balance += $transaction->mount;

        $user->update();
        $target->update();

        $tr = $transaction->create()->toArray();
        // Set user and target info4
        // $tr['user_id']   = $user->toArray();
        // $tr['target_id'] = $target->toArray();

        return Success::json($tr);
    }
}
