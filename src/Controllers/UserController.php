<?php

namespace BankApi\Controllers;

use BankApi\Models\{Error, Success, User};
use Mateodioev\HttpRouter\{Request, Response};
use OpenApi\Attributes as OA;

use function BankApi\genUUIDv4;
use function json_decode;

class UserController extends baseController
{
    #[OA\Get(
        path: '/api/users/',
        description: 'Get all users',
        tags: ['Users'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Users list',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(type: 'boolean', property: 'ok'),
                        new OA\Property(type: 'object', property: 'data', ref: '#/components/schemas/User')
                    ]
                )
            ),
        ]
    )]
    public function all(Request $r): Response
    {
        $q = $r->query();
        $users = (isset($q['limit']) && isset($q['offset']))
            ? $this->sqlAll('SELECT * FROM users LIMIT ? OFFSET ?', [(int) $q['limit'], (int) $q['offset']])
            : $this->sqlAll('SELECT * FROM users');

        return Success::json($users);
    }

    #[OA\Get(
        path: '/api/users/{id}',
        description: 'Find user by id',
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'User id', schema: new OA\Schema(type: 'string'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(type: 'boolean', property: 'ok'),
                        new OA\Property(type: 'object', property: 'data', ref: '#/components/schemas/User'),
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
        path: '/api/users/',
        description: 'Create new user',
        tags: ['Users'],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(type: 'string', property: 'name', description: 'New user name'),
                    new OA\Property(type: 'float', property: 'balance', description: 'User balance'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'User create',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(type: 'boolean', property: 'ok'),
                        new OA\Property(type: 'object', property: 'data', ref: '#/components/schemas/User'),
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

        if (!$user->id) $user->setId(genUUIDv4());

        $user->save();
        return Success::json($user->toArray());
    }

    #[OA\Put(
        path: '/api/users/{id}',
        description: 'Update existing user',
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'User id', schema: new OA\Schema(type: 'string'))
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(type: 'string', property: 'name', description: 'New user name'),
                    new OA\Property(type: 'float', property: 'balance', description: 'User balance')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'User update',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(type: 'boolean', property: 'ok'),
                        new OA\Property(type: 'object', property: 'data', ref: '#/components/schemas/User', description: 'User data after update'),
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
        $user = (new \JsonMapper)->map(json_decode($r->body()), User::class);
        $user->setId($r->param('id'));

        $user->update();

        return Success::json($user->toArray());
    }

    #[OA\Delete(
        path: '/api/users/{id}',
        description: 'Delete user',
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'User id', schema: new OA\Schema(type: 'string'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User deleted',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(type: 'boolean', property: 'ok'),
                        new OA\Property(type: 'object', property: 'data', ref: '#/components/schemas/User'),
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

        if ($user->delete()) return Success::json($user->toArray());
        return Error::json('User not deleted');
    }

    #[OA\Get(
        path: '/api/users/{id}/transactions',
        description: 'Get all user transactions',
        tags: ['Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'User id', schema: new OA\Schema(type: 'string'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User transactions',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(type: 'boolean', property: 'ok'),
                        new OA\Property(type: 'array', property: 'data', items: new OA\Items(ref: '#/components/schemas/Transaction')),
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
        $transactions = \array_map(fn ($t) => $t->toArray(), $user->findTransactions());

        return Success::json($transactions);
    }
}
