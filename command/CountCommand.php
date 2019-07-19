<?php

namespace Command;

use Exception;
use Model\ParseLog\ParseLog;
use Parse;
use ParseHelper;
use phpQuery;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Carbon\Carbon;

class CountCommand extends Command
{
    protected static $defaultName = 'app:count';

    public $categories = "http://thetop10news.com/wp-json/wp/v2/categories";
    public $authors = "http://thetop10news.com/wp-json/wp/v2/users";

    protected function configure()
    {
        $this
            ->setDescription('Count a news.')
            ->setHelp('This command allows you to parse a news...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Count:');
        $parse = new Parse();

        $categories = $this->getCounts($this->categories);
        $authors = $this->getAuthors($this->authors);

        dump($categories);
        dump($authors);

//        $text = "
//        *" . new Carbon() . "*
//        ``` " . implode(",", $categories) . " ```
//        ``` " . implode(",", $authors) . " ```
//        ";
//
//        $parse->message_to_telegram($text);
    }

    protected function getCounts($categories)
    {
        $VARS = [];

        $string = file_get_contents($categories);
        $data = json_decode($string);

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

        if ($data_error != '') echo $data_error;

        foreach ($data as $item) {
            $VARS[$item->name] = $item->count;
        }

        return $VARS;
    }

    protected function getAuthors($authors)
    {
        $VARS = [];

        $string = file_get_contents($authors);
        $data = json_decode($string);

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

        if ($data_error != '') echo $data_error;

        foreach ($data as $item) {
            $VARS[$item->id]['name'] = $item->name;
            $VARS[$item->id]['link'] = $item->link;
        }

        return $VARS;
    }
}