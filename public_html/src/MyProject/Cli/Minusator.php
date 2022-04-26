<?php

namespace MyProject\Cli;

class Minusator extends AbstractCommand
{
    protected function checkParams()
    {
        $this->ensureParamExists('x');//передача обяз-х параметров
        $this->ensureParamExists('y');
    }

    public function execute()
    {
        echo $this->getParam('x') - $this->getParam('y');
    }
}