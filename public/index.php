<?php

// Подключение автозагрузки через composer
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;

$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);

$phones = [1,2,3];

$app->get('/', function ($request, $response) {
    return $response->write('Welcome to Slim!');
});

// $app->get('/users', function ($request, $response) {
//     return $response->write('GET /users');
// });
//
// $app->post('/users', function ($request, $response) {
//     return $response->write('POST /users');
// });

$app->get('/phone', function ($request, $response) use ($phones) {
    return $response->write(json_encode($phones));
});

$app->post('/users', function ($request, $response) {
    return $response->withStatus(302);
});

$app->get('/courses/{id}', function ($request, $response, array $args) {
    $id = $args['id'];
    return $response->write("Course id: {$id}");
});

$app->run();
