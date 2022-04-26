<?php

namespace MyProject\Models;

use MyProject\Services\Db;

abstract class ActiveRecordEntity implements \JsonSerializable//создание объектов этого класса нам не нужно,делаем его абстрактным.
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

    public function __set($name, $value)
    {
        $camelCaseName = $this->undescoreToCamelCase($name);
        $this->$camelCaseName = $value;
    }

    private function undescoreToCamelCase(string $source): string
    {  
        return lcfirst(str_replace('_', '', ucwords($source, '_')));
    }

    /**
     * @return static
     */
    //нужно обратиться к сущности, не создавая её, но чтобы она при этом вернула нам созданные сущности.
    public static function findAll(): array//добавим в Article статический метод, возвращающий нам все статьи.
    {
        $db = Db::getInstance();
        return $db->query('SELECT * FROM `' . static::getTableName() . '`;', [], static::class);
    }

    /**
     * @param int $id
     * @return static|null
     */
    public static function getById(int $id): ?self
    {
        $db = Db::getInstance();
        $entities = $db->query(
            'SELECT * FROM `' . static::getTableName() . '` WHERE id=:id;',
            [':id' => $id],
            static::class
        );

        return $entities ? $entities[0] : null;
    }

    public function save(): void//метод save()? который будет сохранять текущее состояние объекта в базе.
    {
        $mappedProperties = $this->mapPropertiesToDbFormat();

        if ($this->id !== null) {
            $this->update($mappedProperties);//update(), если id у объекта есть;
        } else {
            $this->insert($mappedProperties);//insert(), если это свойство у объекта равно null.
        }
    }

    abstract protected static function getTableName(): string;//метод getTableName(),который должен вернуть строку – имя таблицы.
    // Так как метод абстрактный, то все сущности,которые будут наследоваться от этого класса, должны будут его реализовать.

    private function update(array $mappedProperties): void
    {
        //здесь мы обновляем существующую запись в базе
        $columns2params = [];
        $params2values = [];
        $index = 1;

        foreach ($mappedProperties as $column => $value) {//Составляем результирующий запрос на обновление записи в базе данных.
            $param = ':param' . $index; 
            $columns2params[] = $column . ' = ' . $param; // column1 = :param1
            $params2values[$param] = $value; 
            $index++;
        }
        
        $sql = 'UPDATE ' . static::getTableName() . ' SET ' . implode(', ', $columns2params) . ' WHERE id = ' . $this->id;//формируем запрос
        $db = Db::getInstance();
        $db->query($sql, $params2values, static::class);
    }

    private function insert(array $mappedProperties): void
    {
        $filteredProperties = array_filter($mappedProperties);//Фильтрует элементы массива с помощью callback-функции
        $columns = [];
        $paramsNames = [];
        $params2values = [];

        foreach ($filteredProperties as $columnName => $value) {
            $columns[] = '`' . $columnName . '`';
            $paramName = ':' . $columnName;
            $paramsNames[] = $paramName;
            $params2values[$paramName] = $value;
        }

        $columnsViaSemicolon = implode(', ', $columns);
        $paramsNamesViaSemicolon = implode(', ', $paramsNames);
        //запрос должкн быть таким INSERT INTO `articles` (`author_id`, `name`, `text`) VALUES (:author_id, :name, :text)
        $sql = 'INSERT INTO ' . static::getTableName() . ' (' . $columnsViaSemicolon . ') VALUES (' . $paramsNamesViaSemicolon . ');';
        $db = Db::getInstance();
        $db->query($sql, $params2values, static::class);
        $this->id = $db->getLastInsertId();//чтобы получить id последней вставленной записи в базе исп-м метод lastInsertId() у объекта PDO.
        $this->refresh();
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

        return $mappedProperties;
    }

    private function camelCaseToUnderscore(string $source): string
    {
        return strtolower(preg_replace('~(?<!^)[A-Z]~', '_$0', $source));
    }

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
