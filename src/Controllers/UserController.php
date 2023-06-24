<?php

namespace BankApi\Controllers;

use BankApi\Models\{Error, Success, User};
use Mateodioev\HttpRouter\{Request, Response};
use Mateodioev\HttpRouter\exceptions\RequestException;

use OpenApi\Attributes as OA;

use function BankApi\genUUIDv4;
use function json_decode;

class UserController extends baseController
{
    #[OA\Get(
        path: '/api/users/',
        description: 'Get all users',
        tags: ['Users'],
        parameters: [
            new OA\Parameter(
                name: 'limit',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'offset',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Users list',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            type: 'boolean',
                            property: 'ok'
                        ),
                        new OA\Property(
                            type: 'object',
                            property: 'data',
                            ref: '#/components/schemas/User'
                        )
                    ]
                )
            ),
        ]
    )]
    public function all(Request $r): Response
    {
        $getAll = 'SELECT id, nombre, saldo, created_at FROM users';
        $q = $r->query();
        $users = (isset($q['limit']) && isset($q['offset']))
            ? $this->sqlAll($getAll . ' LIMIT ? OFFSET ?', [(int) $q['limit'], (int) $q['offset']])
            : $this->sqlAll($getAll);

        return Success::json($users);
    }

    #[OA\Get(
        path: '/api/users/{id}',
        description: 'Find user by id',
        tags: ['Users'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'User id',
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            type: 'boolean',
                            property: 'ok'
                        ),
                        new OA\Property(
                            type: 'object',
                            property: 'data',
                            ref: '#/components/schemas/User'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'User not found',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
        ]
    )]
    public function byId(Request $r): Response
    {
        $user = User::find($r->param('id'));

        return Success::json($user->toArray());
    }

    #[OA\Post(
        path: '/api/users/login/',
        description: 'Login in a user account with id and pin',
        tags: ['Users'],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'id',
                        type: 'string',
                        description: 'User id'
                    ),
                    new OA\Property(
                        property: 'pin',
                        type: 'int',
                        description: 'User secret pin'
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'User data',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'ok',
                            type: 'boolean'
                        ),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            ref: '#/components/schemas/User'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid user pin',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 404,
                description: 'User not found',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function login(Request $r): Response
    {
        $body = json_decode($r->body(), true);
        $user = User::find($body['id'] ?? '');

        if ($user->pin !== ($body['pin'] ?? 0))
            throw new RequestException('Invalid pin', 401);

        return Success::json($user->toArray());
    }

    #[OA\Put(
        path: '/api/users/{id}/withdraw/',
        description: 'Withdraw money from an account',
        tags: ['Users'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'User id',
                schema: new OA\Schema(type: 'string')
            )
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'amount',
                        type: 'float',
                        description: 'Amount to withdraw'
                    ),
                    new OA\Property(
                        property: 'pin',
                        type: 'int',
                        description: 'User secret pin'
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'User data',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'ok',
                            type: 'boolean'
                        ),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            ref: '#/components/schemas/User'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid amount to withdraw',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid user pin',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 404,
                description: 'User not found',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 500,
                description: 'Fail to update user',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function withdraw(Request $r): Response
    {
        $body = json_decode($r->body(), true);
        $pin = (int) $body['pin'] ?? 0;
        $amount = (float) $body['amount'] ?? 0;

        $user = User::find($r->param('id'));

        if ($user->pin !== $pin)
            throw new RequestException('Invalid user pin', 401);

        if ($user->balance < $amount)
            throw new RequestException('Invalid amount', 400);

        $user->setBalance($user->balance - $amount)->update();

        return Success::json($user->toArray());
    }

    #[OA\Post(
        path: '/api/users/',
        description: 'Create new user',
        tags: ['Users'],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        type: 'string',
                        property: 'name',
                        description: 'New user name'
                    ),
                    new OA\Property(
                        type: 'float',
                        property: 'balance',
                        description: 'User balance'
                    ),
                    new OA\Property(
                        type: 'int',
                        property: 'pin',
                        description: 'Secret user pin'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'User create',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            type: 'boolean',
                            property: 'ok'
                        ),
                        new OA\Property(
                            type: 'object',
                            property: 'data',
                            ref: '#/components/schemas/User'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 409,
                description: 'Duplicated user',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 500,
                description: 'Invalid payload / Fail to save user',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function create(Request $r): Response
    {
        /**
         * @var User $user
         */
        $user = (new \JsonMapper)->map(json_decode($r->body()), User::class);

        if (!$user->id)
            $user->setId(genUUIDv4());

        $user->save();
        return Success::json($user->toArray());
    }

    #[OA\Put(
        path: '/api/users/{id}',
        description: 'Update existing user',
        tags: ['Users'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'User id',
                schema: new OA\Schema(type: 'string')
            )
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        type: 'string',
                        property: 'name',
                        description: 'New user name'
                    ),
                    new OA\Property(
                        type: 'float',
                        property: 'balance',
                        description: 'User balance'
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'User update',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            type: 'boolean',
                            property: 'ok'
                        ),
                        new OA\Property(
                            type: 'object',
                            property: 'data',
                            ref: '#/components/schemas/User',
                            description: 'User data after update'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Invalid payload / Fail to save user',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    public function update(Request $r): Response
    {
        /**
         * @var User $user
         */
        $user = User::find($r->param('id'));
        $user = (new \JsonMapper)->map(json_decode($r->body()), $user);

        $user->update();

        return Success::json($user->toArray());
    }

    #[OA\Delete(
        path: '/api/users/{id}',
        description: 'Delete user',
        tags: ['Users'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'User id',
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User deleted',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            type: 'boolean',
                            property: 'ok'
                        ),
                        new OA\Property(
                            type: 'object',
                            property: 'data',
                            ref: '#/components/schemas/User'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'User not deleted / Fail to delete user',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 404,
                description: 'User not found',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
        ]
    )]
    public function delete(Request $r): Response
    {
        $user = User::find($r->param('id'));

        if ($user->delete())
            return Success::json($user->toArray());
        return Error::json('User not deleted');
    }

    #[OA\Get(
        path: '/api/users/{id}/transactions',
        description: 'Get all user transactions',
        tags: ['Users'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'User id',
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User transactions',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            type: 'boolean',
                            property: 'ok'
                        ),
                        new OA\Property(
                            type: 'array',
                            property: 'data',
                            items: new OA\Items(ref: '#/components/schemas/Transaction')
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'User not found',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
        ]
    )]
    public function getTransactions(Request $r): Response
    {
        $user = User::find($r->param('id'));
        $transactions = \array_map(fn($t) => $t->toArray(), $user->findTransactions());

        return Success::json($transactions);
    }
}