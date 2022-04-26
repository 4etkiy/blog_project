<?php

namespace MyProject\Controllers;

use MyProject\Exceptions\InvalidArgumentException;
use MyProject\Exceptions\ActivationException;
use MyProject\Models\Users\User;
use MyProject\Models\Users\UserActivationService;
use MyProject\Models\Users\UsersAuthService;
use MyProject\Services\EmailSender;
//use MyProject\View\View;

class UsersController extends AbstractController
{
    //удалили конструктор и отнаследовались от абстрактного контраллера,
    // свойства user и view теперь с типом protected – они будут доступны в наследниках

//    /** @var View */
//    private $view;
//
//    public function __construct()
//    {
//        $this->view = new View(__DIR__ . '/../../../templates');
//    }

    public function signUp()
    {
        if (!empty($_POST)) {
            try {
                $user = User::signUp($_POST);
            } catch (InvalidArgumentException $e) {
                $this->view->renderHtml('users/signUp.php', ['error' => $e->getMessage()]);
                return;
            }

            if ($user instanceof User) {
                $code = UserActivationService::createActivationCode($user);

                EmailSender::send($user, 'Активация', 'userActivation.php', [
                    'userId' => $user->getId(),
                    'code' => $code
                ]);

                $this->view->renderHtml('users/signUpSuccessful.php');
                return;
            }
        }

        $this->view->renderHtml('users/signUp.php');
    }

    public function activate(int $userId, string $activationCode)
    {
        try {
            $user = User::getById($userId);

            if ($user === null) {
                throw new ActivationException('Такого пользователя не существует');
            }

            $isCodeValid = UserActivationService::checkActivationCode($user, $activationCode);

            if (!$isCodeValid) {
                throw new ActivationException('Неверный код активации');
            }

            if ($isCodeValid) {
                $user->activate();
                $this->view->renderHtml('users/successfulActivation.php');
                UserActivationService::deleteActivationCode($user, $activationCode);
                return;
            }

        } catch (ActivationException $e) {
            $this->view->renderHtml('users/activationFailed.php', ['error' => $e->getMessage()]);
        }

//        $user = User::getById($userId);
//        $isCodeValid = UserActivationService::checkActivationCode($user, $activationCode);
//        if ($isCodeValid) {
//            $user->activate();
//            echo 'OK!';
//        }
    }

    public function login()
    {
        if (!empty($_POST)) {
            try {
                $user = User::login($_POST);//используем UsersAuthService для удобного создания нужной Cookie в контроллере.
                UsersAuthService::createToken($user);
                header('Location: /www/');
                exit();

            } catch (InvalidArgumentException $e) {
                $this->view->renderHtml('users/login.php', ['error' => $e->getMessage()]);
                return;
            }
        }

        $this->view->renderHtml('users/login.php');
    }

    public function logOut()//удаляем куку
    {
        setcookie('token', '', -1, '/', '', false, true);
        header('Location: /www/');
    }
}