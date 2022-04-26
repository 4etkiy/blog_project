<?php
require __DIR__ . '/../../vendor/autoload.php';
//JSON Formatter расширение в браузере добавляет форматирование, чтобы ответ было легче воспринимать человеку.
//$entity = [
//    'kek' => 'cheburek',
//    'lol' => [
//        'foo' => 'bar'
//    ]
//];
//когда сервер отвечает в фомате JSON, стоит отправлять соответствующий заголовок клиенту
//header('Content-type: application/json; charset=utf-8');
//echo json_encode($entity);//позволяет представить какую-то сущность в json-формате
try {//пишем фронт-контроллер для API.
    $route = $_GET['route'] ?? '';
    $routes = require __DIR__ . '/../../src/routes_api.php';

    $isRouteFound = false;

    foreach ($routes as $pattern => $controllerAndAction) {
        preg_match($pattern, $route, $matches);
        if (!empty($matches)) {
            $isRouteFound = true;
            break;
        }
    }

    if (!$isRouteFound) {
        throw new \MyProject\Exceptions\NotFoundException('Route not found');
    }

    unset($matches[0]);

    $controllerName = $controllerAndAction[0];
    $actionName = $controllerAndAction[1];

    $controller = new $controllerName;
    $controller->$actionName(...$matches);
} catch (\MyProject\Exceptions\DbException $e) {
    $view = new \MyProject\View\View(__DIR__ . '/../templates/errors');
    $view->displayJson(['error' => $e->getMessage()], 500);
} catch (\MyProject\Exceptions\NotFoundException $e) {
    $view = new \MyProject\View\View(__DIR__ . '/../templates/errors');
    $view->displayJson(['error' => $e->getMessage()], 404);
} catch (\MyProject\Exceptions\UnauthorizedException $e) {
    $view = new \MyProject\View\View(__DIR__ . '/../templates/errors');
    $view->displayJson(['error' => $e->getMessage()], 401);
}