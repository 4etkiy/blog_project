<?php

namespace MyProject\Models;

use MyProject\Services\Db;

//нужно чтобы класс реализовывал специальный интерфейс – JsonSerializable и содержал метод jsonSerialize().
// Этот метод должен возвращать представление объекта в виде массива. все наследники ActiveRecordEntity автоматически могли преобразовываться в JSON.
abstract class ActiveRecordEntity implements \JsonSerializable//создание объектов этого класса нам не нужно, то делаем его абстрактным.
//переносём в него универсальный код из класса Article.
{
    /** @var int */
    protected $id;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    public function jsonSerialize()
    {//метод, который представит объект в виде массива:
        return $this->mapPropertiesToDbFormat();
    }

    public function __set($name, $value)//если этот метод добавить в класс и попытаться задать ему несуществующее свойство,
        //то вместо динамического добавления такого свойства, будет вызван этот метод. При этом в первый аргумент $name,
    {// попадёт имя свойства, а во второй аргумент $value – его значение.
        $camelCaseName = $this->undescoreToCamelCase($name);
        $this->$camelCaseName = $value;
        //echo 'Пытаюсь задать для свойства ' . $name . ' значение ' . $value . '<br>';
        //$this->$name = $value;
    }

    private function undescoreToCamelCase(string $source): string
    {   //lcfirst — Преобразует первый символ строки в нижний регистр
        return lcfirst(str_replace('_', '', ucwords($source, '_')));
        // ucwords — Преобразует в верхний регистр первый символ каждого слова в строке
    }

    /**
     * @return static
     */
    //нужно обратиться к сущности, не создавая её, но чтобы она при этом вернула нам созданные сущности.
    public static function findAll(): array//добавим в Article статический метод, возвращающий нам все статьи.
    {
        $db = Db::getInstance();
        return $db->query('SELECT * FROM `' . static::getTableName() . '`;', [], static::class);
        //можно заменить Article::class на self::class – и сюда автоматически подставится класс, в котором этот метод определен.
        // А можно заменить его и вовсе на static::class – тогда будет подставлено имя класса, у которого этот метод был вызван.
        // В чём разница? Если мы создадим класс-наследник SuperArticle, он унаследует этот метод от родителя.
        // Если будет использоваться self:class, то там будет значение “Article”, а если мы напишем static::class,
        // то там уже будет значение “SuperArticle”. Это называется поздним статическим связыванием
        // – благодаря нему мы можем писать код, который будет зависеть от класса, в котором он вызывается, а не в котором он описан.
    }

    /**
     * @param int $id
     * @return static|null
     */
    public static function getById(int $id): ?self
    {
        $db = Db::getInstance();//Паттерн Singleton (синглтон) см в Db getInstance()
        $entities = $db->query(
            'SELECT * FROM `' . static::getTableName() . '` WHERE id=:id;',
            [':id' => $id],
            static::class
        );

        return $entities ? $entities[0] : null;
    }

    public function save(): void//метод save()? который будет сохранять текущее состояние объекта в базе.
    {//он в зависимости от того, есть ли у объекта id решает – обновить запись или создать новую. Он вызывает др методы
        $mappedProperties = $this->mapPropertiesToDbFormat();
        //var_dump($mappedProperties);

        if ($this->id !== null) {
            $this->update($mappedProperties);//update(), если id у объекта есть;
        } else {
            $this->insert($mappedProperties);//insert(), если это свойство у объекта равно null.
        }
    }

    abstract protected static function getTableName(): string;//метод getTableName(),который должен вернуть строку – имя таблицы.
    // Так как метод абстрактный, то все сущности,которые будут наследоваться от этого класса, должны будут его реализовать.
    //Благодаря этому мы не забудем его добавить в классах-наследниках.

    private function update(array $mappedProperties): void
    {
        //здесь мы обновляем существующую запись в базе
        $columns2params = [];
        $params2values = [];
        $index = 1;

        foreach ($mappedProperties as $column => $value) {//Составляем результирующий запрос на обновление записи в базе данных.
            $param = ':param' . $index; // :param1
            $columns2params[] = $column . ' = ' . $param; // column1 = :param1
            $params2values[$param] = $value; // [:param1 => value1]
            $index++;
        }
        //implode -- Объединяет элементы массива в строку.
        $sql = 'UPDATE ' . static::getTableName() . ' SET ' . implode(', ', $columns2params) . ' WHERE id = ' . $this->id;//формируем запрос
        $db = Db::getInstance();//Паттерн Singleton (синглтон)
        $db->query($sql, $params2values, static::class);
        //var_dump($sql);
        //var_dump($columns2params);
        //var_dump($params2values);
    }

    private function insert(array $mappedProperties): void
    {
        // callback-функция не передана, все пустые значения массива array будут удалены.
        $filteredProperties = array_filter($mappedProperties);//Фильтрует элементы массива с помощью callback-функции
        //var_dump($mappedProperties);
        $columns = [];
        $paramsNames = [];
        $params2values = [];

        foreach ($filteredProperties as $columnName => $value) {
            $columns[] = '`' . $columnName . '`';
            $paramName = ':' . $columnName;
            $paramsNames[] = $paramName;
            $params2values[$paramName] = $value;
        }
//        var_dump($columns);
//        var_dump($paramsNames);
//        var_dump($params2values);

        $columnsViaSemicolon = implode(', ', $columns);
        $paramsNamesViaSemicolon = implode(', ', $paramsNames);
        //запрос должкн быть таким INSERT INTO `articles` (`author_id`, `name`, `text`) VALUES (:author_id, :name, :text)
        $sql = 'INSERT INTO ' . static::getTableName() . ' (' . $columnsViaSemicolon . ') VALUES (' . $paramsNamesViaSemicolon . ');';
        $db = Db::getInstance();
        $db->query($sql, $params2values, static::class);
        $this->id = $db->getLastInsertId();//тобы получить id последней вставленной записи в базе исп-м метод lastInsertId() у объекта PDO.
        $this->refresh();
        //var_dump($sql);
    }

    public function refresh(): void//обновление полей данных значениями из бд
    {
        $objFromDb = static::getById($this->id);

        $properties = get_object_vars($objFromDb);

        foreach ($properties as $key => $value) {
            $this->$key = $value;
        }
    }

    public function delete(): void
    {
        $db = Db::getInstance();
        //DELETE FROM `название таблицы` WHERE id = :id;
        $db->query('DELETE FROM `' . static::getTableName() . '` WHERE id =:id', [':id' => $this->id]);
        $this->id = null;
    }

    public static function findOneByColumn(string $columnName, $value): ?self
    {//$columnName - имя столбца, по которому искать, $value - значение, которое мы ищем в этом столбце
        $db = Db::getInstance();
        $result = $db->query(
            'SELECT * FROM `' . static::getTableName() . '` WHERE `' . $columnName . '` = :value LIMIT 1;',
            [':value' => $value],
            static::class
        );

        if ($result === []) {
            return null;
        }

        return $result[0];// Если же что-то нашлось – вернётся первая запись.
    }

    private function mapPropertiesToDbFormat(): array
    {//с помощью рефлексии, у разных наследников класса ActiveRecordEntity, получаем разные свойства, не привязываясь к конкретному классу
        $reflector = new \ReflectionObject($this);
        $properties = $reflector->getProperties();

        $mappedProperties = [];

        foreach ($properties as $property) {
            $propertyName = $property->getName();
            $propertyNameAsUnderscore = $this->camelCaseToUnderscore($propertyName);
            $mappedProperties[$propertyNameAsUnderscore] = $this->$propertyName;//добавляем элементы с ключами «имя_свойства» и со значениями этих свойств.
        }

        return $mappedProperties; //на выходе ['название_свойства1' => значение свойства1, 'название_свойства2' => значение свойства2]
    }

    private function camelCaseToUnderscore(string $source): string
    {//перед каждой заглавной буквой мы добавляем символ подчеркушки «_», а затем приводим все буквы к нижнему регистру:
        return strtolower(preg_replace('~(?<!^)[A-Z]~', '_$0', $source));//strtolower — Преобразует строку в нижний регистр
        //(?<!^) - а это означает, что при этом самую первую букву в начале строки мы не берем, даже если она большая
        //_$0 - это знак подчеркивания, за которым следует нулевое совпадение в регулярке (нулевое - это вся строка, попавшая под регулярку.
        // В нашем случае - это одна большая буква).с помощью preg_replace, мы заменяем все большие буквы A - Z на _A - _Z. \\
    }

    //Исключение – это такой объект специального класса. Этот класс является встроенным в PHP и называется Exception.
    //$expection = new Expection('Сообщение об ошибке', 123);
    //объекты этого класса можно «бросать». Для этого используется оператор throw.
    //throw $expection;

    //метод для получения количества страниц. Метод будет принимать на вход количество записей на одной странице
    public static function getPagesCount(int $itemsPerPage): int
    {
        $db = Db::getInstance();
        $result = $db->query('SELECT COUNT(*) AS cnt FROM ' . static::getTableName() . ';');
        return ceil($result[0]->cnt / $itemsPerPage);
    }

    //getPage()метод для получения записей на n-ой страничке, принимает на вход количество записей на одной странице и номер страницы.

    /**
     * @return static[]
     */
    /**
     * @return static[]
     */
    public static function getPage(int $pageNum, int $itemsPerPage): array
    {
        $db = Db::getInstance();
        return $db->query(
            sprintf(
                'SELECT * FROM `%s` ORDER BY id DESC LIMIT %d OFFSET %d;',
                static::getTableName(),
                $itemsPerPage,
                ($pageNum - 1) * $itemsPerPage
            ),
            [],
            static::class
        );
    }
}
