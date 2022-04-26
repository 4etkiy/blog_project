<?php
//cli.php можно назвать фронт-контроллером для консольных команд,
// он как index.php в случае с клиент-серверным подходом будет создавать другие объекты и запускать весь процесс.

//Command Line Interface (CLI) – интерфейс командной строки.CLI позволяет запускать программы на PHP не через
// клиент-серверную архитектуру, а как простые программы в командной строке.
//OpenServer->Дополнительно->Консоль
//echo 2 + 2;
//var_dump($argv);
//Переходим в папку с нашим проектом, выполнив:
//cd domains\phpZone\public_html
//php bin/cli.php

//Аргументы, которые мы можем передать в скрипт, указав их после имени скрипта в командной строке.
//php bin/cli.php 3 4
//получить к ним доступ из php-скрипта используется магическая переменная $argv.
//Она представляет собой массив, в котором нулевой элемент – это путь до скрипта, а все последующие – это его аргументы в консоли.
//array(3) {
//  [0] =>
//  string(11) "bin/cli.php"
//  [1] =>
//  string(1) "3"
//  [2] =>
//  string(1) "4"
//}
require __DIR__ . '/../vendor/autoload.php';
//В нашем проекте появилась папка vendor – здесь хранятся все пакеты, которые были скачаны composer-ом.
//autoload.php  нужно подключить в нашем проекте через require,после этого сможем использовать все файлы из библиотек в нашем проекте
//после подключения можно использовать библиотеки
try {
    unset($argv[0]);
    //Регистрируем функцию автозагрузки
    //spl_autoload_register(function (string $className) {//убрали автозагрузку тк ее composer взял на себя
        //require_once __DIR__ . '/../src/' . $className . '.php';
        //require_once __DIR__ . '/../src/' . str_replace('\\', '/', $className) . '.php';
    //});

    // Составляем полное имя класса, добавив нэймспейс
    $className = '\\MyProject\\Cli\\' . array_shift($argv);
    //array_shift извлекает первое значение массива array и возвращает его, сокращая размер array на один элемент

    if (!class_exists($className)) {//class_exists — Проверяет, был ли объявлен класс
        throw new \MyProject\Exceptions\CliException('Class "' . $className . '" not found');
    }

    //проверка, является ли класс, указанный в качестве аргумента, наследником класса AbstractCommand
    $reflectionOfClassName = new ReflectionClass($className);//ReflectionClass::isSubclassOf — Проверяет, является ли класс подклассом

    if (!$reflectionOfClassName->isSubclassOf(MyProject\Cli\AbstractCommand::class)) {
        throw new MyProject\Exceptions\CliException('Сlass ' . $className . ' is not a descendant of the AbstractCommand class ');
    }

    // Подготавливаем список аргументов
    $params = [];

    foreach ($argv as $argument) {
        if (preg_match('~^-(.+)=(.+)$~sui', $argument, $matches)) {
            if (!empty($matches)) {
                $paramName = $matches[1];
                $paramValue = $matches[2];
                $params[$paramName] = $paramValue;
            }
        }
    }

    // Создаём экземпляр класса, передав параметры и вызываем метод execute()
    $class = new $className($params);
    $class->execute();

} catch (\MyProject\Exceptions\CliException $e) {
    echo 'Error: ' . $e->getMessage();
}
//unset($argv[0]);
////$sum = 0;
////
////foreach ($argv as $item) {
////    $sum += $item;
////}
////
////echo $sum;//выдаст 12
////для имен посложнее php bin/cli.php -a=25 -b=10 нужен простейший пасрер
//$params = [];
//
//foreach ($argv as $argument) {
//    if (preg_match('~^-(.+)=(.+)$~sui', $argument, $matches)) {
//        if (!empty($matches)) {
//            $paramName = $matches[1];
//            $paramValue = $matches[2];
//            $params[$paramName] = $paramValue;
//        }
//    }
//}

//var_dump($params);
//array(2) {
//  'a' =>
//  string(2) "25"
//  'b' =>
//  string(2) "10"
//}