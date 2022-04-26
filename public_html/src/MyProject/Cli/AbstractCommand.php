<?php

namespace MyProject\Cli;

use MyProject\Exceptions\CliException;

abstract class AbstractCommand
{
    /** @var array */
    private $params;

    public function __construct(array $params)
    {//В конструкторе класса мы принимаем список параметров, сохраняем их
        $this->params = $params;
        $this->checkParams();
    }

    abstract public function execute();//используется метод getParam(), который вернет параметр (при его наличии), либо вернет null (при его отсутствии).

    abstract protected function checkParams();//проверяет наличие обязательных параметров для этого скрипта

    protected function getParam(string $paramName)
    {
        return $this->params[$paramName] ?? null;
    }

    protected function ensureParamExists(string $paramName)
    {//поочередно вызывается метод для проверки в массиве нужных ключей.
        if (!isset($this->params[$paramName])) {//Если их нет – метод кинет исключение.
            throw new CliException('Param with name "' . $paramName . '" is not set!');
        }
    }
}