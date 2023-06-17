<?php

use Mateodioev\HttpRouter\{Request, Response, Router};
use Mateodioev\HttpRouter\exceptions\{HttpNotFoundException, RequestException};
use BankApi\Models\{Error as ErrorResponse, Success as SuccessResponse};

require __DIR__ . '/vendor/autoload.php';

$router = new Router;

$router->get('/', function () {
    return Response::text('Hello World');
});

$router->mount('/api', function () use ($router) {
    $router->all('/{all:path}?', fn (Request $r) => ErrorResponse::json('Invalid endpoint ' . $r->param('path')));
});

try {
    $router->run();
} catch (HttpNotFoundException $e) {
    $router->send(ErrorResponse::text($e->getMessage() ?? 'Not found', 404));
} catch (RequestException $e) {
    $router->send(ErrorResponse::text($e->getMessage() ?? 'Server error', 500));
}
