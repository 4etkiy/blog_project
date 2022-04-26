<?php

namespace MyProject\Controllers;

use MyProject\Exceptions\Forbidden;
use MyProject\Exceptions\InvalidArgumentException;
use MyProject\Exceptions\NotFoundException;
use MyProject\Exceptions\UnauthorizedException;
use MyProject\Models\Users\UsersAuthService;
use MyProject\Models\Articles\Article;

use MyProject\Models\Users\User;

class ArticlesController extends AbstractController
{
    public function view(int $articleId): void
    {
        $article = Article::getById($articleId);

        if ($article === null) {
            throw new NotFoundException();
        }

        $this->view->renderHtml('articles/view.php', [
            'article' => $article,
            'user' => UsersAuthService::getUserByToken()//передаем пользователя,
            // при входе на каждого пользователья создается кука, она нам нужна пока мы серфим по сайту
        ]);
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
