<?php

use Mateodioev\HttpRouter\{Request, Response, Router};
use Mateodioev\HttpRouter\exceptions\{HttpNotFoundException, RequestException};

use BankApi\Models\Error as ErrorResponse;
use BankApi\Controllers;
use BankApi\Db\Sql;

require __DIR__ . '/vendor/autoload.php';

Sql::prepare(__DIR__);
$router = new Router;

$router->get('/', function () {
    return Response::text('Hello World');
});

$router->mount('/api', function () use ($router) {
    # $router->all('/{all:path}?', fn (Request $r) => ErrorResponse::json('Invalid endpoint ' . $r->param('path')));

    $router->mount('/users', function () use ($router) {

        $userController = new Controllers\UserController;
        // Get user by id
        $router->get('/{id}', $userController->byId(...));
        // Update user info
        # $router->put('/{id}', '');
        // Delete user
        $router->delete('/{id}', $userController->delete(...));
        // Get user transactions
        $router->get('/{id}/transactions', $userController->getTransactions(...));
        // Get all user
        $router->get('/', $userController->all(...));
        // Create new user
        $router->post('/', $userController->create(...));
    });
});

try {
    $router->run();
} catch (HttpNotFoundException $e) {
    $router->send(ErrorResponse::json($e->getMessage() ?? 'Not found', $e->getCode() ?: 404));
} catch (RequestException $e) {
    $router->send(ErrorResponse::json($e->getMessage() ?? 'Server error', $e->getCode() ?: 500));
}
