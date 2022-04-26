<?php

namespace MyProject\Controllers;

use MyProject\Exceptions\Forbidden;
use MyProject\Exceptions\InvalidArgumentException;
use MyProject\Exceptions\NotFoundException;
use MyProject\Exceptions\UnauthorizedException;
use MyProject\Models\Users\UsersAuthService;
use MyProject\Models\Articles\Article;

//use MyProject\View\View;
use MyProject\Models\Users\User;

class ArticlesController extends AbstractController
{
//    //удалили конструктор и отнаследовались от абстрактного контраллера,
//    // свойства user и view теперь с типом protected – они будут доступны в наследниках

//
//    /** @var View */
//    private $view;
//
//    /** @var User|null */
//    private $user;
//
//    public function __construct()
//    {
//        $this->user = UsersAuthService::getUserByToken();
//        $this->view = new View(__DIR__ . '/../../../templates');
//        $this->view->setVar('user', $this->user);//теперь можем в контрол-х прямо в конструкторах задать нужные переменные благодаря setVar
//        //И добавить в шапке сайта (в шаблонах) вывод пользователя, если он был передан во View:
//    }

//    public function setName(): string
//    {
//        $this->name = $name;
//    }
//
//    public function setText(): string
//    {
//        $this->text = $text;
//    }

    public function view(int $articleId): void
    {
        $article = Article::getById($articleId);
//        $result = $this->db->query('SELECT * FROM `articless` WHERE id= :id;',
//            [':id' => $articleId], Article::class
//        );
        //var_dump($result);

//"Рефлексия означает процесс, во время которого программа может отслеживать и модифицировать собственную структуру и поведение во время выполнения."
        //PHP Reflection API - набор классов-рефлекторов. С помощью них мы можем создавать объекты-рефлекторы для разных типов данных в PHP,
        // которые позволят творить с ними всё что только вздумается.
        //Получить все методы:      ->getMethods()
        //Получить все константы:    ->getConstants()
        //Создание нового объекта (даже с непубличным конструктором)  ->newInstance()
        //Создание нового объекта без вызова конструктора (o_O)   ->newInstanceWithoutConstructor()

//        $reflector = new \ReflectionObject($article);
//        $properties = $reflector->getProperties();
//        $propertiesNames = [];
//
//        foreach ($properties as $property) {
//            $propertiesNames[] = $property->getName();
//        }
//
//        var_dump($propertiesNames);
//        if ($article === null) {
//            $this->view->renderHtml('errors/404.php', [], $code = 404);
//            return;
//        }

// второй аргумент - это класс, объект которого будет создаваться и, со свойствами которого будут ассоциироваться данные,
// получаемые посредством fetchAll()
//        $author = $this->db->query('SELECT nickname FROM `ussers` WHERE id =:id;',
//            //[':id' => $result[0]['authorId']], User::class
//            [':id' => $result[0]->getAuthorId()], User::class
//        );
        //$articleAuthor = User::getById($article->getAuthorId());
        if ($article === null) {
            throw new NotFoundException();
        }

        $this->view->renderHtml('articles/view.php', [
            'article' => $article,
            'user' => UsersAuthService::getUserByToken()//передаем пользователя,
            // при входе на каждого пользователья создается кука, она нам нужна пока мы серфим по сайту
        ]);
        //$this->view->renderHtml('articles/view.php', ['article' => $article]);//, 'author' => $author[0]
    }

    public function comment()
    {

    }

    public function edit(int $articleId): void
    {
        /** @var Article $article */
        $article = Article::getById($articleId);

        if ($article === null) {
            throw new NotFoundException();//кидаем исключение об ошибке
            //$this->view->renderHtml('errors/404.php', [], $code = 404);
            //return;
        }

        if ($this->user === null) {
            throw new UnauthorizedException();
        }

        if ($this->user !== null && !$this->user->isAdmin()) {
            throw new Forbidden('Для редактирования статьи нужно обладать правами администратора');
        }

        if (!empty($_POST)) {
            try {
                $article->updateFromArray($_POST);
            } catch (InvalidArgumentException $e) {
                $this->view->renderHtml('articles/edit.php', ['error' => $e->getMessage(), 'article' => $article]);
                return;
            }

            header('Location: /www/articles/' . $article->getId(), true, 302);
            exit();
        }

        $this->view->renderHtml('articles/edit.php', ['article' => $article, 'user' => UsersAuthService::getUserByToken()]);

        //$article->setName('Новое название статьи');
        //$article->setText('Новый текст статьи');

        //$article->save();//метод save() может быть вызван как у объекта, который уже есть в базе данных,
        // так и у нового (если мы создали его с помощью new Article и заполнили ему свойства).
    }

    public function add(): void
    {//Обрабатываем исключение во фронт-контроллере. Добавляем в конце еще один catch. в index.php
        if ($this->user === null) {
            throw new UnauthorizedException();
        }

        if ($this->user !== null && !$this->user->isAdmin()) {
            throw new Forbidden('Для добавления статьи нужно обладать правами администратора');
        }

        //Здесь мы пытаемся создать новую статью, и если возникают ошибки – то мы показываем их в шаблоне.
        // Если же все проходит хорошо – то мы переадресовываем пользователя на страничку с новой статьёй.
        if (!empty($_POST)) {
            try {
                $article = Article::createFromArray($_POST, $this->user);
            } catch (InvalidArgumentException $e) {
                $this->view->renderHtml('articles/add.php', ['error' => $e->getMessage()]);
                return;
            }

            header('Location: ' . $article->getId(), true, 302);
            exit();
        }

        $this->view->renderHtml('articles/add.php', [
            'user' => UsersAuthService::getUserByToken()]);
//        $author = User::getById(1);
//        $article = new Article();
//        $article->setAuthor($author);
//        $article->setName('Новое название статьи');
//        $article->setText('Новый текст статьи');
//        $article->save();
    }

    public function delete(int $id)
    {
        if ($this->user === null) {
            throw new UnauthorizedException();
        }

        if ($this->user !== null && !$this->user->isAdmin()) {
            throw new Forbidden('Для удаления статьи нужно обладать правами администратора');
        }

        $article = Article::getById($id);

        if ($article !== null) {
            $article->delete();
            $this->view->renderHtml('articles/delete.php', [
                'user' => UsersAuthService::getUserByToken()]);
        } else {
            //$this->view->renderHtml('errors/404.php', [], 404);
            throw new NotFoundException();
        }
    }
}
