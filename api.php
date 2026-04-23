<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Response.php';
require_once __DIR__ . '/Router.php';
require_once __DIR__ . '/models/firstsolo.php';
require_once __DIR__ . '/models/secondsolo.php';
require_once __DIR__ . '/controllers/firstsoloController.php';
require_once __DIR__ . '/controllers/secondsoloController.php';

try {
    $pdo = connectDatabase(DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS);
    $firstsoloModel = new Firstsolo($pdo);
    $secondsoloModel = new Secondsolo($pdo);

    $firstsoloController = new FirstsoloController($firstsoloModel);
    $secondsoloController = new SecondsoloController($secondsoloModel, $firstsoloModel);

    $router = new Router();
    $router->add('GET', '/zapocet/api/firstmany', fn() => $firstsoloController->getAll());
    $router->add('GET', '/zapocet/api/firstmany/{id}', fn(array $params) => $firstsoloController->getOne($params));
    $router->add('POST', '/zapocet/api/firstmany', fn() => $firstsoloController->post());
    $router->add('PUT', '/zapocet/api/firstmany/{id}', fn(array $params) => $firstsoloController->put($params));
    $router->add('DELETE', '/zapocet/api/firstmany/{id}', fn(array $params) => $firstsoloController->delete($params));

    $router->add('GET', '/zapocet/api/secondmany', fn() => $secondsoloController->getAll());
    $router->add('GET', '/zapocet/api/secondmany/types', fn() => $secondsoloController->types());
    $router->add('GET', '/zapocet/api/secondmany/{id}', fn(array $params) => $secondsoloController->getOne($params));
    $router->add('POST', '/zapocet/api/secondmany', fn() => $secondsoloController->post());
    $router->add('PUT', '/zapocet/api/secondmany/{id}', fn(array $params) => $secondsoloController->put($params));
    $router->add('DELETE', '/zapocet/api/secondmany/{id}', fn(array $params) => $secondsoloController->delete($params));

    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';


    $basePath = '/oprava2/misha/palkozp';

    if (str_starts_with($requestPath, $basePath)) {
        $requestPath = substr($requestPath, strlen($basePath));
    }

    $router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $requestPath);
} catch (Throwable) {
    // 500 Internal Server Error - непредвиденная ошибка на сервере, например исключение PHP или ошибка выполнения запроса.
    Response::json(['error' => 'Internal server error'], 500);
}
