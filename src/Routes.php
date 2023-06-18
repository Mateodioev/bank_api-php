<?php

namespace BankApi;

use BankApi\Models\Error;
use Mateodioev\HttpRouter\{Response, Router};
use OpenApi\Attributes as OA;

#[Oa\Info(title: "Bank api", version: "0.1")]
class Routes
{
    #[OA\Get(path: '/')]
    #[OA\Response(response: 200, description: 'Welcome page')]
    public static function register(Router &$router): void
    {
        $router->get('/', function () {
            return Response::text('Hello World');
        });

        self::registerApi($router);
    }

    protected static function registerApi(Router &$router): void
    {
        $router->mount('/api', function () use ($router) {
            self::registerApiUsers($router);
            self::registerApiTransactions($router);
            self::registerApiDocs($router);
        });
    }

    protected static function registerApiDocs(Router &$router): void
    {
        $router->mount('/docs', function () use ($router) {
            $router->get('/', fn () => Response::html(\file_get_contents($_ENV['WORK_DIR'] . '/public/swagger.html')));
            $router->get('/openapi.yaml', fn () => Response::text(\file_get_contents($_ENV['OPENAPI_FILE']))->setContentType('text/yaml'));
        });
    }

    protected static function registerApiUsers(Router &$router): void
    {
        $router->mount('/users', function () use ($router) {
            $userController = new Controllers\UserController;

            // Get user transactions
            $router->get('/{id}/transactions', $userController->getTransactions(...));
            // Send money to another user
            $router->post('/{id}/send/{targetId}', fn () => '');
            // Get user by id
            $router->get('/{id}', $userController->byId(...));
            // Update user info
            $router->put('/{id}', $userController->update(...));
            // Delete user
            $router->delete('/{id}', $userController->delete(...));
            // Get all users
            $router->get('/', $userController->all(...));
            // Create new user
            $router->post('/', $userController->create(...));
        });
    }

    protected static function registerApiTransactions(Router &$router): void
    {
        $router->mount('/transactions', function () use ($router) {
            $router->all('/', fn () => Error::json('Not implemented'));
        });
    }
}
