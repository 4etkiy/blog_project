<?php

require __DIR__ . '/../src/MyProject/Controllers/MainControllers.php';
require __DIR__ . '/../vendor/autoload.php';

use MyProject\Models\Users\UsersAuthService;

try {
   //Для того, чтобы добавить новый функционал на блог, достаточно создать экшен в контроллере,
   // прописать роутинг, добавить класс для новой модели и создать шаблончик – вся остальная обвязка уже имеется.
    
    $route = $_GET['route'] ?? '';
    $routes = require __DIR__ . '/../src/routes.php';

    $isRouteFound = false;

    foreach ($routes as $pattern => $controllerAndAction) {
        if (preg_match($pattern, $route, $matches)) {
            if (!empty($matches)) {
                unset($matches[0]);
                $isRouteFound = true;
                break;
            }
        }
    }

    if (!$isRouteFound) {
        throw new \MyProject\Exceptions\NotFoundException();
    }

    $controllerName = $controllerAndAction[0];
    $actionName = $controllerAndAction[1];
    
    $controller = new $controllerName();
    $controller->$actionName(...$matches);
} catch (\MyProject\Exceptions\DbException $e) {
    $view = new \MyProject\View\View(__DIR__ . '/../templates/errors');
    $view->renderHtml('500.php', ['error' => $e->getMessage()], 500);
} catch (\MyProject\Exceptions\NotFoundException $e) {
    $view = new \MyProject\View\View(__DIR__ . '/../templates/errors');
    $view->renderHtml('404.php', ['error' => $e->getMessage()], 404);
} catch (\MyProject\Exceptions\UnauthorizedException $e) {
    $view = new \MyProject\View\View(__DIR__ . '/../templates/errors');
    $view->renderHtml('401.php', ['error' => $e->getMessage()], 401);
} catch (\MyProject\Exceptions\Forbidden $e) {
    $view = new \MyProject\View\View(__DIR__ . '/../templates/errors');
    $view->renderHtml('403.php', ['error' => $e->getMessage(), 'user' => UsersAuthService::getUserByToken()], 403);
}



