<?php

namespace MyProject\Services;

use MyProject\Exceptions\DbException;

class Db
{
    private static $instance;

    /** @var \PDO */
    private $pdo;

    private function __construct()
    {
        //установим соединение с базой данных
        $dbOptions = (require __DIR__ . '/../../settings.php')['db'];

        try {
            $this->pdo = new \PDO(
                'mysql:host=' . $dbOptions['host'] . ';dbname=' . $dbOptions['dbname'],
                $dbOptions['user'],
                $dbOptions['password']
            );
            $this->pdo->exec('SET NAMES UTF8');
        } catch (\PDOException $e) {
            throw new DbException('Ошибка при подключении к базе данных: ' . $e->getMessage());
        }
    }

    public function query(string $sql, $params = [], string $className = 'stdClass'): ?array//3 аргумент - имя класса, объекты которого нужно создавать.
    {//По умолчанию это будут объекты класса stdClass – это такой встроенный класс в PHP, у которого нет никаких свойств и методов.
        $sth = $this->pdo->prepare($sql);
        $result = $sth->execute($params);

        if (false === $result) {
            return null;
        }

        return $sth->fetchAll(\PDO::FETCH_CLASS, $className);//Возвращает массив, содержащий все строки результирующего набора
        // \PDO::FETCH_CLASS говорит о том, что нужно вернуть результат в виде объектов какого-то класса
        //Второй аргумент – это имя класса, которое мы можем передать в метод query()
    }
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();//если null создаем новый объект класса Db, а затем помещён в это свойство
        }

        return self::$instance;
    }

//чтобы получить id последней вставленной записи в базе (в рамках текущей сессии работы с БД) можно использовать метод lastInsertId() у объекта PDO
    public function getLastInsertId(): int
    {
        return (int) $this->pdo->lastInsertId();
    }
}
