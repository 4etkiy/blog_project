<?php
//контроллер, который позволит работать со статьями через API
namespace MyProject\Controllers\Api;

use MyProject\Controllers\AbstractController;
use MyProject\Controllers\ArticlesController;
use MyProject\Exceptions\NotFoundException;
use MyProject\Models\Articles\Article;
use MyProject\Models\Users\User;

class ArticlesApiController extends AbstractController
{
    public function view(int $articleId)
    {
        $article = Article::getById($articleId);

        if ($articleId === null) {
            throw new NotFoundException();
        }

        $this->view->displayJson([
            'articles' => [$article]
        ]);
    }

//Здесь php://input – это входной поток данных. Именно из него мы и будем получать JSON из запроса.
// file_get_contents – читает данные из указанного места, в нашем случае из входного потока.
// А json_decode декодирует json в структуру массива. После чего мы просто выводим массив с помощью var_dump().
//    public function add()
//    {
//        $input = json_decode(
//            file_get_contents('php://input'),
//            true
//        );
//        var_dump($input);
//    }

    //в postman post http://phpzone/www/api/articles/add в body raw
//{
//"articles":[
//{
//"name": "Измененное название статьи",
//"test": "Измененный текст статьи",
//"author_id": "1"
//}
//]
//}

    public function add()
    {//во всех контроллерах мы сможем получать входные данные вот так
//        $input = json_decode(
//            file_get_contents('php://input'),
//            true
//        );
        $input = $this->getInputData();
        //сделаем функционал, который позволит сохрянять в базу данных статью, пришедшую в формате JSON
        $articleFromRequest = $input['articles'][0];
        $authorId = $articleFromRequest['author_id'];
        $author = User::getById($authorId);

        $article = Article::createFromArray($articleFromRequest, $author);
        $article->save();

        header('Location: /www/api/articles/' . $article->getId(), true, 302);
        //header('Location: /www/articles/' . $article->getId(), true, 302);
        var_dump($input);
    }

}