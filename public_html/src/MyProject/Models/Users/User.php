<?php

namespace MyProject\Models\Users;

use MyProject\Exceptions\InvalidArgumentException;
use MyProject\Models\ActiveRecordEntity;

class User extends ActiveRecordEntity
{
    /**@var string */
    protected $nickname;

    /**@var string */
    protected $email;

    /**@var int */
    protected $isConfirmed;

    /** @var string */
    protected $role;

    /**@var string */
    protected $passwordHash;

    /**@var string */
    protected $authToken;

    /**@var string */
    protected $createdAt;

    /**
     * @return string
     */
    public function getNickName(): string
    {
        return $this->nickname;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    public function getIsConfirmed(): int
    {
        return $this->isConfirmed;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function getAuthToken(): string
    {
        return $this->authToken;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getRole(): int
    {
        return $this->role;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public static function signUp(array $userData): User//проверки на то что все данные были переданы
    {
        if (empty($userData['nickname'])) {
            throw new InvalidArgumentException('Не передан nickname');
        }

        if (!preg_match('/^[a-zA-Z0-9]+$/', $userData['nickname'])) {
            throw new InvalidArgumentException('Nickname может состоять только из символов латинского алфавита и цифр');
        }

        if (empty($userData['email'])) {
            throw new InvalidArgumentException('Не передан email');
        }

        if (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Email некорректен');
        }

        if (empty($userData['password'])) {
            throw new InvalidArgumentException('Не передан password');
        }

        if (mb_strlen($userData['password']) < 8) {
            throw new InvalidArgumentException('Пароль должен быть не менее 8 символов');
        }

        if (static::findOneByColumn('nickname', $userData['nickname']) !== null) {//findOneByColumn в ActiveRecordEntity
            throw new InvalidArgumentException('Пользователь с таким nickname уже существует');
        }

        if (static::findOneByColumn('email', $userData['email']) !== null) {//findOneByColumn в ActiveRecordEntity
            throw new InvalidArgumentException('Пользователь с таким email уже существует');
        }

        //если все эти проверки пройдены , создаем нового пользователя
        $user = new User();
        $user->nickname = $userData['nickname'];
        $user->email = $userData['email'];
        $user->passwordHash = password_hash($userData['password'], PASSWORD_DEFAULT);//Создаёт хеш пароля
        $user->isConfirmed = false;
        $user->role = 'user';
        //authToken – это специально случайным образом сгенерированный параметр, с помощью которого пользователь будет авторизовываться.
        $user->authToken = sha1(random_bytes(100)) . sha1(random_bytes(100));
        $user->save();

        return $user;
    }

    public function activate(): void
    {
        $this->isConfirmed = true;
        $this->save();
    }

    public static function login(array $loginData): User
    {
        if (empty($loginData['email'])) {
            throw new InvalidArgumentException('Не передан email');
        }

        if (empty($loginData['password'])) {
            throw new InvalidArgumentException('Не передан password');
        }

        $user = User::findOneByColumn('email', $loginData['email']);

        if ($user === null) {
            throw new InvalidArgumentException('Нет пользователя с таким email');
        }

        if (!password_verify($loginData['password'], $user->getPasswordHash())) {//password_verify — Проверяет, соответствует ли пароль хешу
            //password_verify(string $password, string $hash): bool password-Пользовательский пароль, hash-Хеш,созданный функцией password_hash()
            throw new InvalidArgumentException('Неправильный пароль');
        }

        if (!$user->isConfirmed) {
            throw new InvalidArgumentException('Пользователь не подтверждён');
        }

        $user->refreshAuthToken();
        $user->save();

        return $user;
    }

    private function refreshAuthToken()
    {//при успешном входе auth token пользователя в базе обновляется - все его предыдущие сессии станут недействительными.
        $this->authToken = sha1(random_bytes(100)) . sha1(random_bytes(100));
    }

    protected static function getTableName(): string
    {
        return 'ussers';
    }
}

//class User
//{
//    private $name;
//
//    public function __construct(string $name)
//    {
//        $this->name = $name;
//    }
//
//    public function getName(): string
//    {
//        return $this->name;
//    }
//}