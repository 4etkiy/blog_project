<?php include __DIR__ . '/../header.php'; ?>

<?php /** @var array $article */ ?>
<?php /** @var array $author */ ?>

<h1><?= $article->getName() ?></h1>
<p><?= $article->getParserText() ?></p>
<!--getParsedText() добавляем вывод прошедшего парсинг текста в шаблоне
Теперь при выводе статей содержимое поля text будет предварительно пропущено через markdown-парсер.-->
<p><b>Автор:  <?=$article->getAuthor()->getNickName();?> </b></p>
<!--<p><b>Автор:  //$author['nickname'] </b></p>-->

<?php include __DIR__ . '/../footer.php'; ?>


