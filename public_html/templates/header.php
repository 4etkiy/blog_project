<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'Мой блог' ?></title>
    <link rel="stylesheet" href="/www/style.css">
</head>
<body>

<table class="layout">
    <tr>
        <td colspan="2" class="header">
            Мой блог
        </td>
    </tr>
    <tr>
        <td colspan="2" style="text-align: right">
            <!-- !empty($user) ? 'Привет, ' . $user->getNickName() : 'Войдите на сайт'-->
            <?php if(!empty($user)): ?>
                Привет, <?= $user->getNickname() ?>  | <a href="http://phpzone/www/users/logOut">Выйти</a>
            <?php else: ?>
                <a href="http://phpzone/www/users/login">Войти</a> | <a href="http://phpzone/www/users/register">Зарегистрироваться</a>
            <? endif; ?>
        </td>
    </tr>
    <tr>
        <td>
