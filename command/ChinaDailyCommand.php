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
        $parse = new Parse();

        $text = "
        *Parse China Daily*
         _парсер был запущен_
         [Open this link](https://thetop10news.com/author/china-daily/)
        ";
        $photo = "https://i.gifer.com/NE0C.gif";

        $parse->message_to_telegram($text);
//        $parse->gif_to_telegram($photo);

        $this->parser($input, $output, $this->url, $this->start, $this->end, $io);
    }

    public function parser(InputInterface $input, OutputInterface $output, $url, $start, $end, SymfonyStyle $style)
    {
        $parse = new Parse();
        $VARS = [];

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
                        $dd_post = explode(" ", $dd_post)[0] . "T" . explode(" ", $dd_post)[1] . ":00+00:00";
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
                        } else {
                            $style->warning('есть в базе..');
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
    }
}