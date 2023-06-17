<?php

namespace BankApi\Controllers;

use BankApi\Models\{Error, Success, User};
use Mateodioev\HttpRouter\{Request, Response};

use function BankApi\genUUIDv4;
use function json_decode;

class UserController extends baseController
{
    public function all(Request $r): Response
    {
        $q = $r->query();
        $users = (isset($q['limit']) && isset($q['offset']))
            ? $this->sqlAll('SELECT * FROM users LIMIT ? OFFSET ?', [(int) $q['limit'], (int) $q['offset']])
            : $this->sqlAll('SELECT * FROM users');

        return Success::json($users);
    }

    public function byId(Request $r): Response
    {
        $user = User::find($r->param('id'));

        return Success::json($user->toArray());
    }

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

    public function delete(Request $r): Response
    {
        $user = User::find($r->param('id'));

        if ($user->delete()) return Success::json($user->toArray());
        return Error::json('User not deleted');
    }

    public function getTransactions(Request $r): Response
    {
        // TODO: Implement userTransactions() method.
        return Error::json('');
    }
}
