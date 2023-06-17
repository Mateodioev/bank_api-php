<?php

namespace BankApi\Controllers;

use BankApi\Models\{Error, Success, User};
use Mateodioev\HttpRouter\{Request, Response};

class UserController extends baseController
{
    public function allUsers(Request $r): Response
    {
        $q = $r->query();
        $users = (isset($q['limit']) && isset($q['offset']))
            ? $this->sqlAll('SELECT * FROM users LIMIT ? OFFSET ?', [(int) $q['limit'], (int) $q['offset']])
            : $this->sqlAll('SELECT * FROM users');

        return Success::json($users);
    }

    public function oneUser(Request $r): Response
    {
        $user = User::find($r->param('id'));
        if (!$user)
            return Error::json('User not found', 404);

        return Success::json($user->toArray());
    }

    public function createUser(Request $r): Response
    {
        $name = json_decode($r->body(), true)['name'];
        $u    = User::create($name)->save();
        return Success::json($u->toArray());
    }
}
