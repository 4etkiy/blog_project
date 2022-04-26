<?php

namespace MyProject\Services;

use MyProject\Models\Users\User;

class EmailSender
{//cервис, который будет предназначен для отправки email-сообщений.
    public static function send(
        User $receiver,
        string $subject,
        string $templateName,
        array $templateVars = []
    ): void {
        extract($templateVars);//Импортирует переменные из массива в текущую таблицу символов

        ob_start();
        require __DIR__ . '/../../../templates/mail/' . $templateName;
        $body = ob_get_contents();
        ob_end_clean();

        mail($receiver->getEmail(), $subject, $body, 'Content-Type: text/html; charset=UTF-8');
    }
}


