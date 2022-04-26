<?php

namespace MyProject\Cli;

//класс, который будет заниматься тем, что считает сумму переданных в него аргументов: -a и -b.
class Summator extends AbstractCommand
{
    protected function checkParams()////передача обяз-х параметров
    {
        $this->ensureParamExists('a');
        $this->ensureParamExists('b');
    }

    public function execute()
    {
        echo $this->getParam('a') + $this->getParam('b');
    }
}