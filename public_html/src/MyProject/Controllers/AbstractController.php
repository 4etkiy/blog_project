<?php

namespace MyProject\Controllers;

use MyProject\Models\Users\User;
use MyProject\Models\Users\UsersAuthService;
use MyProject\View\View;

//удалили конструкторы в MainController и в ArticlesController и отнаследовались от абстрактного контраллера,
// свойства user и view теперь с типом protected – они будут доступны в наследниках
abstract class AbstractController
{
    /** @var View */
    protected $view;

    /** @var User|null */
    protected $user;

    public function __construct()
    {
        $this->user = UsersAuthService::getUserByToken();
        $this->view = new View(__DIR__ . '/../../../templates');
        $this->view->setVar('user', $this->user);//теперь можем в контрол-х прямо в конструкторах задать нужные переменные благодаря setVar
        //И добавить в шапке сайта (в шаблонах) вывод пользователя, если он был передан во View:
    }

    protected function getInputData()
    {
        return json_decode(
            file_get_contents('php://input'),
            true
        );
    }
}