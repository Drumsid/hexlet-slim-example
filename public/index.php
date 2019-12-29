<?php

// Подключение автозагрузки через composer
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;

// подключаем контейнер для подключения шаблонов к slim
$container = new Container();
$container->set('renderer', function () {
    // Параметром передается базовая директория в которой будут храниться шаблоны
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});

AppFactory::setContainer($container);
$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);

$phones = [1, 2, 3];

$users = [
    ['id' => 32, 'firstName' => 'Julianne', 'lastName' => 'Mueller', 'email' => 'claire77@satterfield.com'],
    ['id' => 54, 'firstName' => 'Brandy', 'lastName' => 'Reichel', 'email' => 'Reichel@satterfield.com'],
    ['id' => 23, 'firstName' => 'Lonnie', 'lastName' => 'Ankunding', 'email' => 'Ankunding@satterfield.com'],
    ['id' => 76, 'firstName' => 'Juwan', 'lastName' => 'Weimann', 'email' => 'Weimann@satterfield.com'],
    ['id' => 2, 'firstName' => 'Norval', 'lastName' => 'Nitzsche', 'email' => 'Nitzsche@satterfield.com'],
];

$searchUsers = ['mike', 'mishel', 'adel', 'keks', 'kamila', 'satras', 'asdasdtrasd', 'asdtrmifs'];

// тестовый вывод slim при установке
// $app->get('/', function ($request, $response) {
//     return $response->write('Welcome to Slim!');
// });

//запрос на главную и подключение шаблона index в папке template
$app->get('/', function ($request, $response) {
    return $this->get('renderer')->render($response, 'index.phtml');
});

// get запрос
//
// $app->get('/users', function ($request, $response) {
//     return $response->write('GET /users');
// });
//
// post запрос
// $app->post('/users', function ($request, $response) {
//     return $response->write('POST /users');
// });
//
//

$app->get('/phone', function ($request, $response) use ($phones) {
    return $response->write(json_encode($phones));
});

$app->get('/courses/{id}', function ($request, $response, array $args) {
    $id = $args['id'];
    return $response->write("Course id: {$id}");
});

// запрос на /users и подключение шаблона users/index из папки template
$app->get('/users', function ($request, $response, $args) use ($users) {
    $params = ['users' => $users];
    return $this->get('renderer')->render($response, 'users/index.phtml', $params);
});

// запрос на /users/{id} и подключение шаблона users/show из папки template
$app->get('/users/{id}', function ($request, $response, $args) use ($users) {
    $params = ['users' => $users, 'userId' => $args['id'], 'id' => $args['id'], 'nickname' => 'user-' . $args['id']];
    return $this->get('renderer')->render($response, 'users/show.phtml', $params);
});

// 
//
// подключаем переадресацию на статус 302
// $app->post('/users', function ($request, $response) {
//     return $response->withStatus(302);
// });
// подключаем шаблон users/show и выводим переменные id и nickname
// $app->get('/users/{id}', function ($request, $response, $args) {
//     $params = ['id' => $args['id'], 'nickname' => 'user-' . $args['id']];
//     // Указанный путь считается относительно базовой директории для шаблонов, заданной на этапе конфигурации
//     // $this доступен внутри анонимной функции благодаря http://php.net/manual/ru/closure.bindto.php
//     return $this->get('renderer')->render($response, 'users/show.phtml', $params);
// });
//
//

// запрос на /search и подключение шаблона search/index из папки template и реализация поиска в массиве $searchUsers
$app->get('/search', function ($request, $response) use ($searchUsers) {
    $term = $request->getQueryParam('term');
    $params = ['searchUsers' => $searchUsers, 'term' => $term];
    return $this->get('renderer')->render($response, 'search/index.phtml', $params);
});

// запрос на /addusers/new и подключение шаблона users/new из папки template и реализация регистрации нового пользователя
$app->get('/addusers/new', function ($request, $response) {
    $params = [
        'user' => ['nickname' => '', 'email' => ''],
        'errors' => [],
        'dir' => __DIR__,
    ];
    return $this->get('renderer')->render($response, "users/new.phtml", $params);
});

//
function validate($user)
{
    $errors = [];
    if (empty($user['nickname'])) {
        $errors['nickname'] = "Can't be blank";
    }
    return $errors;
}
//
$app->post('/addusers', function ($request, $response) {
    $user = $request->getParsedBodyParam('user');
    $errors = validate($user);
    if (count($errors) === 0) {
        $pathToFile = __DIR__ . "../users/";
        fopen($pathToFile, 'w+');
        return $response->withHeader('Location', '/')
            ->withStatus(302);
    }
    $params = [
        'user' => $user,
        'errors' => $errors
    ];
    return $this->get('renderer')->render($response, "users/new.phtml", $params);
});

$app->run();
