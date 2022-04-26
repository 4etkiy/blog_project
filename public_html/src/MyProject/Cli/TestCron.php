<?php

namespace MyProject\Cli;

class TestCron extends AbstractCommand
{
    protected function checkParams()
    {
        $this->ensureParamExists('x');
        $this->ensureParamExists('y');
    }

    public function execute()
    {
        // чтобы проверить работу скрипта, будем записывать в файлик 1.log текущую дату и время
        file_put_contents('C:\\1.log', date(DATE_ISO8601) . PHP_EOL, FILE_APPEND);
//file_put_contents — Пишет данные в файл
//file_put_contents(
//    string $filename, //Путь к записываемому файлу.
//    mixed $data, //Записываемые данные. Может быть типа string, array или ресурсом потока.
//    int $flags = 0, //FILE_APPEND - Если файл filename уже существует, данные будут дописаны в конец файла вместо того, чтобы его перезаписать.
//    resource $context = ?
//): int
// PHP_EOL представляет символ конечной строки для настоящее, может быть "\r\n" (на серверах Windows) или "\n" (на что-либо другое).
    }
}