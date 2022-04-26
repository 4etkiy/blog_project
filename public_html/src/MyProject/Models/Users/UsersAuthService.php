<?php
//сервис, который будет работать с пользовательскими сессиями через Cookie

namespace MyProject\Models\Users;

class UsersAuthService
{
    public static function createToken(User $user): void
    {
        $token = $user->getId() . ':' . $user->getAuthToken();
        setcookie('token', $token, 0, '/', '', false, true);
        //setcookie(
        //    string $name, Название cookie.
        //    string $value = "", Значение cookie. Это значение будет сохранено на клиентском компьютере;
        //    int $expires = 0, Время действия , если 0 или пропустить , действия cookie истечёт с окончанием сессии (при закрытии браузера).
        //    string $path = "", Путь к директории на сервере,из которой будут доступны cookie.Если задать '/', cookie будут доступны во всем домене.
        //    string $domain = "", (Под)домен, которому доступны cookie.
        //    bool $secure = false, TRUE указывает на то, что значение cookie должно передаваться от клиента по защищённому соединению HTTPS.
        //    bool $httponly = false Если true,cookie будут доступны только через HTTP-протокол.В этом случае они не доступны скриптовым языкам.
        //): bool
    }

    public static function getUserByToken(): ?User
    {
        $token = $_COOKIE['token'] ?? '';

        if (empty($token)) {
            return null;
        }

        [$userId, $authToken] = explode(':', $token, 2);

        $user = User::getById((int) $userId);

        if ($user === null) {
            return null;
        }

        if ($user->getAuthToken() !== $authToken) {
            return null;
        }

        return $user;
    }
}