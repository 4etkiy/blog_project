<?php

namespace MyProject\Models\Articles;

use MyProject\Exceptions\InvalidArgumentException;
use MyProject\Models\ActiveRecordEntity;
use MyProject\Models\Users\User;

class Article extends ActiveRecordEntity
{
    /**@var string */
    protected $name;

    /**@var string */
    protected $text;

    /**@var int */
    protected $authorId;

    /**@var string */
    protected $createdAt;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @return User
     */
    public function getAuthor(): User
    {
        return User::getById($this->authorId);
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param string $text
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }

//    /**
//     * @param string $int
//     */
//    public function setAuthor(User $author): void
//    {
//        $this->authorId = $author->getId();
//    }
    /**
     * @param User $user
     */
    public function setAuthor(User $user): void
    {
        $this->authorId = $user->getId();
    }

    public function getParserText(): string
    {//метод, который будет прогонять текст статьи через парсер, прежде чем его вернуть
        $parser = new \Parsedown();//библтотека разметки которую подключили с помощью composer
        return $parser->text($this->getText());
    }

    public static function createFromArray(array $fields, User $author): Article
    {
        if (empty($fields['name'])) {
            throw new InvalidArgumentException('Не передано название статьи');
        }

        if (empty($fields['text'])) {
            throw new InvalidArgumentException('Не передан текст статьи');
        }

        $article = new Article();

        $article->setAuthor($author);
        $article->setName($fields['name']);
        $article->setText($fields['text']);

        $article->save();

        return $article;
    }

    public function updateFromArray(array $fields): Article//блок редактирования статьи
    {
        if (empty($fields['name'])) {
            throw new InvalidArgumentException('Не передано название статьи');
        }

        if (empty($fields['text'])) {
            throw new InvalidArgumentException('Не передан текст статьи');
        }

        $this->setName($fields['name']);
        $this->setText($fields['text']);

        $this->save();

        return $this;
    }

    //LazyLoad (ленивая загрузка) – это когда данные не подгружаются до тех пор, пока их не запросят.
    protected static function getTableName(): string
    {
        return 'articless';
    }
}

//class Article
//{
//    private $title;
//    private $text;
//    private $author;
//
//    public function __construct(string $title, string $text, User $author)
//    {
//        $this->title = $title;
//        $this->text = $text;
//        $this->author = $author;
//    }
//
//    public function getTitle(): string
//    {
//        return $this->title;
//    }
//
//    public function getText(): string
//    {
//        return $this->text;
//    }
//
//    public function getAuthor(): User
//    {
//        return $this->author;
//    }
//}