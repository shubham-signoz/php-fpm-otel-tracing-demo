<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->get('/', function (Request $request, Response $response): Response {
    $response->getBody()->write((string) json_encode([
        'message' => 'Hello from PHP-FPM + Slim',
        'path' => '/',
        'timestamp' => date('c'),
    ], JSON_PRETTY_PRINT));

    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/hello/{name}', function (Request $request, Response $response, array $args): Response {
    $response->getBody()->write((string) json_encode([
        'message' => sprintf('Hello, %s', $args['name'] ?? 'world'),
        'path' => '/hello/{name}',
        'timestamp' => date('c'),
    ], JSON_PRETTY_PRINT));

    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/error', function (Request $request, Response $response): Response {
    $response->getBody()->write((string) json_encode([
        'message' => 'intentional test error',
        'path' => '/error',
        'timestamp' => date('c'),
    ], JSON_PRETTY_PRINT));

    return $response
        ->withStatus(500)
        ->withHeader('Content-Type', 'application/json');
});

$app->run();
