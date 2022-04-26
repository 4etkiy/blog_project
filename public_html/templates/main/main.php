<?php include __DIR__ . '/../header.php'; ?>
<?php /** @var array $articles */ ?>

<?php foreach ($articles as $article): ?>
    <h2><a href="/www/articles/<?= $article->getId() ?>"><?= $article->getName() ?></a></h2>
    <p><?= $article->getParserText() ?></p>
    <!--getParsedText() добавляем вывод прошедшего парсинг текста в шаблоне
    Теперь при выводе статей содержимое поля text будет предварительно пропущено через markdown-парсер.-->
    <?php if ($user!==null && $user->isAdmin()):?>
        <button><a href="/www/articles/<?= $article->getId() ?>/edit">редактировать статью</a></button>
    <?php endif; ?>

    <?php if ($user!==null && $article !== null && $user->isAdmin()):?>
        <button><a href="/www/articles/<?= $article->getId() ?>/delete">удалить статью статью</a></button>
    <?php endif; ?>

    <!--было  $article['id']  $article['name']   $article['text'] -->
<?php endforeach; ?>

<div style="text-align: center">
    <?php for ($pageNum = 1; $pageNum <= $pagesCount; $pageNum++): ?>
        <?php if ($currentPageNum === $pageNum): ?>
            <b><?= $pageNum ?></b>
        <?php else: ?>
            <a href="<?= $pageNum === 1 ? '' : $pageNum ?>"><?= $pageNum ?></a>
        <?php endif; ?>
    <?php endfor; ?>
</div>
<?php include __DIR__ . '/../footer.php'; ?>

