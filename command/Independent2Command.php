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

class Independent2Command extends Command
{
    protected static $defaultName = 'app:independent-world';

//    public $url = "https://www.independent.com/wp-json/wp/v2/posts?categories=1813&per_page=100";
    public $media = "https://www.independent.com/wp-json/wp/v2/media";

    protected function configure()
    {
        $this
            ->setDescription('Parse a news.')
            ->setHelp('This command allows you to parse a news...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Independent (world)');
        $parse = new Parse();

        $text = "
        *Independent (world)*
         _парсер был запущен_
         [Open this link](https://thetop10news.com/author/independent/)
        ";

//        $parse->message_to_telegram($text);

        $this->parser($input, $output, $this->url, $io, $this->media);
    }

    public function parser(InputInterface $input, OutputInterface $output, $url, SymfonyStyle $style, $media)
    {
        $parse = new Parse();
        $VARS = [];

        $string = file_get_contents($url);
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

        dump($data);

//        foreach ($data as $item) {
//            $title = $item->title->rendered;
//            $date = $item->date;
//            $status = $item->status;
//            $featured_media = $item->featured_media;
//            $link = $item->link;
//            $content = $item->content->rendered;
//            $content .= "<p><a target=\"_blank\" rel=\"nofollow\" href=\"$link\">Source</a></p>";
//            try {
//                $check = ParseLog::where('token', $link)->first();
//
//                if (empty($check)) {
//                    if (!empty($featured_media)) {
//                        $featured = file_get_contents($media . '/' . $featured_media);
//                        $featured_date = json_decode($featured);
//                        $thumb_id = $parse->uploadImage($featured_date->guid->rendered);
//                    } else {
//                        $thumb_id = 1;
//                    }
//
//                    $parse_id = $parse->crated($title, $date, $date, $status, 12, 14, $content, $thumb_id);
//                    $output->writeln('<comment>' . $parse_id . '</comment>');
//                    if (!empty($parse_id)) {
//                        $VARS['item'] = $parse_id;
//                        $VARS['token'] = $link;
//                        $VARS['site_id'] = 8;
//
//                        ParseLog::create($VARS);
//                    }
//
//                    $style->success('добавлено..');
//                } else {
//                    $style->warning('есть в базе..');
//                }
//
//                $style->newLine();
//            } catch (Exception $e) {
//                $output->writeln('<error>error..</error>');
//                exit;
//            }
//
//        }

    }

}