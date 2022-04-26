<?php

//Если нам понадобится в другом контроллере или другом экшне добавить логику для работы с шаблонами,
// нам снова придется перечислять список переменных, а затем писать include с указанием полного пути для шаблона.
// выносим логику с подключением нужного шаблона в отдельный класс.
//Создадим класс View.php

namespace MyProject\View;

//Давайте повторим последовательность шагов, которые необходимо сделать для добавления новой странички:
//
//Добавляем экшн в контроллер (либо создаём ещё и новый контроллер);
//Добавляем для него роут в routes.php;
//Описываем логику внутри экшена и в конце вызываем у компонента view метод renderHtml();
//Создаём шаблон для вывода результата.

class View
{
    private $templatesPath;

    private $extraVars = [];

    public function __construct(string $templatesPath)
    {
        $this->templatesPath = $templatesPath;//в $this приходит __DIR__ . '/../../../templates из MainController
    }

    public function setVar(string $name, $value): void//сделаем во View возможность добавлять переменные от пользоватяля еще перед рендерингом
    {
        $this->extraVars['name'] = $value;
    }

    public function displayJson($data, int $code = 200)
    {
        //когда сервер отвечает в фомате JSON, стоит отправлять соответствующий заголовок клиенту
        header('Content-type: application/json; charset=utf-8');
        http_response_code($code);//Получает или устанавливает код ответа HTTP
        echo json_encode($data);//позволяет представить какую-то сущность в json-формате
    }

    public function renderHtml(string $templateName, array $vars = [], int $code = 200)
    {
        http_response_code($code);//Задать код ответа можно при помощи функции http_response_code()
        //Функция extract извлекает массив в переменные.
        //в неё передаётся массив ['key1' => 1, 'key2' => 2], а после её вызова у нас имеются переменные $key1 = 1 и $key2 = 2.

        extract($this->extraVars);//теперь мы можем в контроллерах прямо в конструкторах задать нужные переменные.
        extract($vars);

        ob_start();
        include $this->templatesPath . '/' . $templateName;//в $this приходит renderHtml('main/main.php', ['articles' => $articles]); из MC
        $buffer = ob_get_contents();//все данные, которые должны были быть переданы в поток вывода, оказались в переменной $buffer.
        ob_end_clean();

        echo $buffer;
    }
}