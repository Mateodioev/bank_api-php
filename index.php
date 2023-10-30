<?php

use BankApi\Db\Sql;
use BankApi\Routes;
use Mateodioev\HttpRouter\Router;
use BankApi\Models\Error as ErrorResponse;
use Mateodioev\HttpRouter\exceptions\{HttpNotFoundException, RequestException};

require __DIR__ . '/vendor/autoload.php';

Sql::prepare(__DIR__);
$router = new Router;

Routes::register($router);

try {
    $router->run();
} catch (HttpNotFoundException $e) {
    $router->send(ErrorResponse::json($e->getMessage() ?? 'Not found', $e->getCode() ?: 404));
} catch (RequestException $e) {
    $router->send(ErrorResponse::json($e->getMessage() ?? 'Server error', $e->getCode() ?: 500));
} catch (Exception $e) {
    $router->send(ErrorResponse::json('Unknown error', 500));
    // $router->send(ErrorResponse::json($e->getMessage(), 500));
}
