<?php

// Загружаем данные из файла в строку
$string = file_get_contents("https://www.independent.com/wp-json/wp/v2/posts?categories=1236&per_page=100");
// Превращаем строку в объект
$data = json_decode($string);

//var_dump($data);
//die;

switch (json_last_error()) {
    case JSON_ERROR_NONE:
        $data_error = '';
        break;
    case JSON_ERROR_DEPTH:
        $data_error = 'Достигнута максимальная глубина стека';
        break;
    case JSON_ERROR_STATE_MISMATCH:
        $data_error = 'Неверный или не корректный JSON';
        break;
    case JSON_ERROR_CTRL_CHAR:
        $data_error = 'Ошибка управляющего символа, возможно верная кодировка';
        break;
    case JSON_ERROR_SYNTAX:
        $data_error = 'Синтаксическая ошибка';
        break;
    case JSON_ERROR_UTF8:
        $data_error = 'Некорректные символы UTF-8, возможно неверная кодировка';
        break;
    default:
        $data_error = 'Неизвестная ошибка';
        break;
}

if($data_error !='') echo $data_error;

foreach ($data as $item)
{
    var_dump($item->title->rendered);
    echo "<br>";
    var_dump($item->content->rendered);
    echo "<br>";
    var_dump($item->date);
    echo "<hr>";
}

$title = $data->title;
$content = $data->content;

?>
