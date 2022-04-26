<?php
//require __DIR__ . '/../src/MyProject/Models/Articles/Article.php';
//require __DIR__ . '/../src/MyProject/Models/Users/User.php';

//index.php - скрипт, в котором происходит обработка входящих запросов и создаются другие контроллеры, называется фронт-контроллером.
require __DIR__ . '/../src/MyProject/Controllers/MainControllers.php';
require __DIR__ . '/../vendor/autoload.php';
//В нашем проекте появилась папка vendor – здесь хранятся все пакеты, которые были скачаны composer-ом.
//autoload.php  нужно подключить в нашем проекте через require,после этого сможем использовать все файлы из библиотек в нашем проекте
//после подключения можно использовать библиотеки
use MyProject\Models\Users\UsersAuthService;

//function myAutoLoader(string $className)
//{
//    var_dump($className);
//    require_once __DIR__ . '/../src/' . str_replace('\\', '/', $className) . '.php';
//}
//
//spl_autoload_register('myAutoLoader');
try {
    //spl_autoload_register(function (string $className) {//убрали автозагрузку тк ее composer взял на себя
        //require_once __DIR__ . '/../src/' . str_replace('\\', '/', $className) . '.php';
//        require_once __DIR__ . '\..\src\\' . $className . '.php';
//        var_dump($className);
//        var_dump(str_replace('\\', '/', __DIR__) . '/../src/');
//        var_dump(__DIR__ . '/../src/'. $className . '.php');
    //});
//$controller = new \MyProject\Controllers\MainController();
//
//if (!empty($_GET['name'])) {
//    $controller->sayHello($_GET['name']);
//} else {
//    $controller->main();
//}

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

//элементы массива можно передать в аргументы метода с помощью оператора троеточия:
//он передаст элементы массива в качестве аргументов методу в том порядке, в котором они находятся в массиве.

//var_dump($controllerAndAction);// 0 => string 'MyProject\Controllers\MainController'  1 => string 'sayHello'
//var_dump($matches);// 1 => string 'username'
    $controller = new $controllerName();
    $controller->$actionName(...$matches);
} catch (\MyProject\Exceptions\DbException $e) {
    $view = new \MyProject\View\View(__DIR__ . '/../templates/errors');
    $view->renderHtml('500.php', ['error' => $e->getMessage()], 500);
    //echo $e->getMessage();//поймали исключение и просто вывели текст ошибки через echo.
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
//mail('1v1agn1t@gmail.com', 'Тема письма', 'Текст письма', 'From: 1v1agn1t@gmail.com'); //отправка письма

//Исключение – это такой объект специального класса. Этот класс является встроенным в PHP и называется Exception.

//$exception = new Exception();
//$exception = new Exception('Сообщение об ошибке', 123);
//объекты этого класса можно «бросать». Для этого используется оператор throw.
//throw $exception; // выдаст такую ошибку Stack trace:#0 {main}bthrown in <b>[...][...]</b> on line <b>3</b><br />
//на 3 стр было вызвано искл и его можно поймать с помощью try-catch:
//try {
//    throw new Exception('Сообщение об ошибке', 123);
//} catch (Exception $e) {
//    echo 'Было поймано исключение: ' . $e->getMessage() . '. Код: ' . $e->getCode();
//}
//
//
//function func1()
//{
//    try {
//        // какой-то код
//        func2();
//    } catch (Exception $e) {
//        echo 'Было поймано исключение: ' . $e->getMessage();
//    }
//
//    echo 'А теперь выполнится этот код';
//}
//
//function func2()
//{
//    // какой-то код
//    func3();
//}
//
//function func3()
//{
//    // код, в котором возможна исключительная ситуация
//    throw new Exception('Ошибка при подключении к БД');
//
//    echo 'Этот код не выполнится, так как идет после места, где было брошено исключение';
//}
//
//func1();
//Код, который идёт после того, где было брошено исключение, выполнен не будет. Исключение прерывает выполнение кода,
//и только после места, где оно было поймано и обработано, код продолжит выполняться.



