<?php

require __DIR__ . '/../vendor/autoload.php';

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
