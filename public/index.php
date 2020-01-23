<?php

require_once __DIR__ . '/../lib/function.php';

// Подключение автозагрузки через composer
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;

session_start();

// подключаем контейнер для подключения шаблонов к slim
$container = new Container();
$container->set('renderer', function () {
    // Параметром передается базовая директория в которой будут храниться шаблоны
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});

// подключаем flash
$container->set('flash', function () {
    return new \Slim\Flash\Messages();
});

AppFactory::setContainer($container);
$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);
// Получаем роутер – объект отвечающий за хранение и обработку маршрутов
$router = $app->getRouteCollector()->getRouteParser();

$phones = [1, 2, 3];
// выводим юзеров из файла
$users = parseUsers('/../users/users.txt');

// юзеры для теста поиска
$searchUsers = ['mike', 'mishel', 'adel', 'keks', 'kamila', 'satras', 'asdasdtrasd', 'asdtrmifs'];

//запрос на главную и подключение шаблона index в папке template
$app->get('/', function ($request, $response) {
    // aad flash message
    $flashes = $this->get('flash')->getMessages();
    $params = [
        'flashes' => $flashes
    ];

    return $this->get('renderer')->render($response, 'index.phtml', $params);
})->setName('/');


$app->get('/phone', function ($request, $response) use ($phones) {
    return $response->write(json_encode($phones));
})->setName('phone');

$app->get('/courses/{id}', function ($request, $response, array $args) {
    $id = $args['id'];
    return $response->write("Course id: {$id}");
});

// ============== CRUD BLOCK ===================================

// запрос на /users и подключение шаблона users/index из папки template
$app->get('/users', function ($request, $response, $args) use ($users) {

    $page  = $request->getQueryParam('page', 1);
    $per =  5;
    $users = array_slice($users, $page === 1 ? 0 : ($page - 1) * $per, $per);

    $params = [
        'users' => $users,
        'page' => $page
    ];

    return $this->get('renderer')->render($response, 'users/index.phtml', $params);
})->setName('users');

// запрос на /users/{id} и подключение шаблона users/show из папки template
$app->get('/user/{id}', function ($request, $response, $args) use ($users) {
    $params = [
        'users' => $users,
        'userId' => $args['id'],
        'id' => $args['id'],
        'nickname' => 'user-' . $args['id']
    ];
    if (isUserId($args['id'], $users)) {
        return $this->get('renderer')->render($response, 'users/show.phtml', $params);
    }
    return $this->get('renderer')->render($response, 'error/index.phtml', $params)->withStatus(404);
})->setName('userId');

// запрос на /addusers/new и подключение шаблона users/new из папки template и
// реализация регистрации нового пользователя
$app->get('/addusers/new', function ($request, $response) {
    $params = [
        'user' => ['nickname' => '', 'email' => ''],
        'errors' => [],
        'dir' => __DIR__,
    ];
    return $this->get('renderer')->render($response, "users/new.phtml", $params);
})->setName('addNewUser');

// валидация, создание пользователя и переадресация на главную или вывод ошибок
$app->post('/addusers', function ($request, $response) use ($router) {

    $user = $request->getParsedBodyParam('user');
    $errors = validate($user);

    $pathToFile = __DIR__ . "/../users/users.txt";
    $strFromFileUser = file_get_contents($pathToFile);
    $user['id'] = setUserId($strFromFileUser);
    $jsonUser = json_encode($user);

    if (count($errors) === 0) {
        file_put_contents($pathToFile, $jsonUser . "|\n", FILE_APPEND);

        $this->get('flash')->addMessage('success', 'User Added');
        // в функцию передаётся имя маршрута, а она возвращает url
        $url = $router->urlFor('/');
        $response = $response->withStatus(302);
        return $response->withRedirect($url);
    }
    $params = [
        'user' => $user,
        'errors' => $errors
    ];
    return $this->get('renderer')->render($response, "users/new.phtml", $params);
})->setName('addusers');

// edit user
$app->get('/user/{id}/edit', function ($request, $response, array $args) use ($users) {
    // $users = new SchoolRepository();
    $id = $args['id'];
    $editUser = isUserById($id, $users);
    $params = [
        'editUser' => $editUser,
        'errors' => []
    ];
    return $this->get('renderer')->render($response, 'users/edit.phtml', $params);
})->setName('editSchool');

// ============== CRUD BLOCK ===================================


// запрос на /user/{nickname} и подключение шаблона users/nickname из папки template
// $app->get('/user/{nickname}', function ($request, $response, $args) use ($users) {
//     $params = [
//         'users' => $users,
//         'nickname' => $args['nickname']
//     ];
//     if (isUser($args['nickname'], $users)) {
//         return $this->get('renderer')->render($response, 'users/nickname.phtml', $params);
//     }
//     return $this->get('renderer')->render($response, 'error/index.phtml', $params)->withStatus(404);
// })->setName('usersName');

// запрос на /search и подключение шаблона search/index из папки template и реализация поиска в массиве $searchUsers
$app->get('/search', function ($request, $response) use ($searchUsers) {
    $term = $request->getQueryParam('term');
    $params = [
        'searchUsers' => $searchUsers,
        'term' => $term
    ];
    return $this->get('renderer')->render($response, 'search/index.phtml', $params);
})->setName('search');

// запрос на /phpcourses/new и подключение шаблона courses/new из папки template и реализация регистрации нового курса
$app->get('/phpcourses/new', function ($request, $response) {
    $params = [
        'course' => ['title' => '', 'paid' => ''],
        'errors' => [],
    ];
    return $this->get('renderer')->render($response, "courses/new.phtml", $params);
})->setName('addPhpCourse');

// валидация, создание курса и переадресация на главную или вывод ошибок
$app->post('/phpcourses', function ($request, $response) {

    $course = $request->getParsedBodyParam('course');
    $validator = new Hexlet\Slim\Example\Classes\Validator();
    $errors = $validator->validate($course);

    if (count($errors) === 0) {
        // $repo->save($course);
        return $response->withHeader('Location', '/')
            ->withStatus(302);
    }
    $params = [
        'course' => $course,
        'errors' => $errors,
        'post' => $_POST,
    ];
    return $this->get('renderer')->render($response, "courses/new.phtml", $params)->withStatus(422);
})->setName('phpcourses');

// Именованные маршруты
$app->get('/test', function ($request, $response) use ($router) {
    // в функцию передаётся имя маршрута, а она возвращает url
    $router->urlFor('test'); // /users
    //$router->urlFor('user', ['id' => 4]); // /users/4
    $params = [
        'router' => $router,
    ];
    return $this->get('renderer')->render($response, "test/index.phtml", $params);
})->setName('test');

// Xdebug маршру
$app->get('/xdebug', function ($request, $response) use ($router) {
    // в функцию передаётся имя маршрута, а она возвращает url
    $router->urlFor('xdebug'); // /users
    //$router->urlFor('user', ['id' => 4]); // /users/4
    $params = [
        'router' => $router,
    ];
    return $this->get('renderer')->render($response, "debug/index.phtml", $params);
})->setName('xdebug');


$app->run();
