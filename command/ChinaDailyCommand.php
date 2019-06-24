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


class ChinaDailyCommand extends Command
{

    protected static $defaultName = 'app:china-daily-news';

    public $url = "http://www.chinadaily.com.cn/world/america/page";
    public $start = 1;
    public $end = 2;


    protected function configure()
    {
        $this
            ->setDescription('Parse a news.')
            ->setHelp('This command allows you to parse a news...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Parse China Daily');

        $return = $this->parser($input, $output, $this->url, $this->start, $this->end, $io);

//        $this->telegramMessage("China Daily (world) - Выполнен (" . Carbon::now() . ") Добавлено - " . $return);
    }

    public function parser(InputInterface $input, OutputInterface $output, $url, $start, $end, SymfonyStyle $style)
    {
        $parse = new Parse();
        $VARS = [];

        $count_out = 0;
        $count_check = 0;

        if ($start < $end) {

            $file = file_get_contents($url . '_' . $start . '.html');

            $new = phpQuery::newDocument($file);

            foreach ($new->find('#left .tw3_01_2') as $item) {
                $news = pq($item);

                $title = $news->find('h4 a')->html();
                $published = $news->find('b')->html();
                $link = $news->find('a')->attr('href');
                $link = "http:" . $link;
                $thumb = $news->find('a img')->attr('src');
                $thumb = "http:" . $thumb;

                if (!empty($title)) {

                    $content = $link;
                    $con = phpQuery::newDocument(file_get_contents($content));
                    foreach ($con->find('.lft_art') as $item2) {
                        $content2 = pq($item2);

                        $text = $content2->find('#Content')->html();
                        $text .= "<p><a target=\"_blank\" rel=\"nofollow\" href=\"$link\">Source</a></p>";
                    }

                    try {
                        $dd_post = $published;
                        $text_out = $text;

                        $check = ParseLog::where('token', $link)->first();

                        if (empty($check)) {
                            $thumb_id = $parse->uploadImage($thumb);
                            $parse_id = $parse->crated($title, $dd_post, $dd_post, "publish", 13, 10, $text_out, $thumb_id);

                            $output->writeln('<comment>' . $parse_id . '</comment>');
                            if (!empty($parse_id)) {
                                $VARS['item'] = $parse_id;
                                $VARS['token'] = $link;
                                $VARS['site_id'] = 3;

                                ParseLog::create($VARS);
                            }
                            $style->success('добавлено..');
                            $count_out++;
                        } else {
                            $style->warning('есть в базе..');
                            $count_check++;
                            $style->note(array(
                                $title,
                                $link,
                            ));
                        }

                    } catch (Exception $e) {
                        $output->writeln('<error>error..</error>');
                        exit;
                    }
                }

            }

            $start++;
            $this->parser($input, $output, $url, $start, $end, $style);
        }

        return $count_out;
    }


    public function telegramMessage($message)
    {
        $token = $_ENV['API_TELEGRAM'];
        $chatid = $_ENV['CHAT_ID'];
        $mess = $message;
        $tbot = file_get_contents("https://api.telegram.org/bot".$token."/sendMessage?chat_id=".$chatid."&text=".urlencode($mess)); //Если нашли ошибку отправляем  сообщение в телеграмм

    }
}