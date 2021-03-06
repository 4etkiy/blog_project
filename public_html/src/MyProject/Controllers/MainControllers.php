<?php

namespace MyProject\Controllers;
use MyProject\Models\Articles\Article;
use MyProject\Models\Users\UsersAuthService;
use MyProject\Controllers\AbstractController;
use MyProject\View\View;

class MainController
{
    /** @var View */
    private $view;

    /** @var User|null */
    private $user;

    public function __construct()
    {
        $this->user = UsersAuthService::getUserByToken();
        $this->view = new View(__DIR__ . '/../../../templates');
        $this->view->setVar('user', $this->user);
    }

    public function main()
    {
        $this->page(1);
    }

    public function page(int $pageNum)
    {
        $this->view->renderHtml('main/main.php', [
            'articles' => Article::getPage($pageNum, 5),
            'pagesCount' => Article::getPagesCount(5),//передадим в шаблон число страниц.
            'user' => UsersAuthService::getUserByToken(),//передаем пользователя,
            // при входе на каждого пользователья создается кука, она нам нужна пока мы серфим по сайту
            'currentPageNum' => $pageNum,//номер страницы не ссылкой, а просто текстом, передадим номер текущей странички в шаблон.
        ]);
    }

    public function sayHello(string $name)
    {
        $this->view->renderHtml('main/hello.php', ['name' => $name, 'title' => 'Страница приветствия']);
    }

    public function sayBye(string $name)
    {
        echo 'Пока, ' . $name;
    }
}
