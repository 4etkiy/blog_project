<?php include __DIR__ . '/../header.php'; ?>
    <h1>Добавление комментария к статье</h1>
<?php if (!empty($error)): ?>
    <div style="color: red;"><?= $error ?></div>
<?php endif; ?>
    <form action="comment_add" method="post">
        <label for="text">Текст статьи</label><br>
        <textarea name="text" id="text" rows="10" cols="80"><?= $_POST['text'] ?? '' ?></textarea><br>
        <br>
        <input type="submit" value="Создать">
    </form>
<?php include __DIR__ . '/../footer.php'; ?>