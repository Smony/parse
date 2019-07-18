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

class ChinaPlusPoliticsCommand extends Command
{

    protected static $defaultName = 'app:china-plus-politics';

    public $url = "http://chinaplus.cri.cn/news/politics/index";
    public $start = 0;
    public $end = 1;

    protected function configure()
    {
        $this
            ->setDescription('Parse a news.')
            ->setHelp('This command allows you to parse a news...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Parse China Plus Politics');
        $parse = new Parse();

        $text = "
        *Parse China Plus Politics*
         _парсер был запущен_
         [Open this link](https://thetop10news.com/author/china-plus/)
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
            if ($start == 0) {
                $file = file_get_contents($url . '.html');
            } else {
                $file = file_get_contents($url . '_' . $start . '.html');
            }

            $new = phpQuery::newDocument($file);

            foreach ($new->find('.newsList-con .news-item') as $item) {
                $news = pq($item);

                $title = $news->find('.news-item-text h3 a')->html();
                $link = $news->find('.news-item-text h3 a')->attr('href');
                $thumb = $news->find('.news-item-photo img')->attr('src');
                $thumb = explode("?", $thumb)[0];

                if (!empty($title)) {
                    $content = $link;
                    $con = phpQuery::newDocument(file_get_contents($content));
                    foreach ($con->find('.article-left') as $item2) {
                        $content2 = pq($item2);
                        $published = $content2->find('.article-type .article-type-item-time')->html();
                        $text = $content2->find('.article-con')->html();
                        $text .= "<p><a target=\"_blank\" rel=\"nofollow\" href=\"$link\">Source</a></p>";
                    }

                    try {
                        $ex = explode(" ", $published);
                        $dd_post = $ex[1] . 'T' . $ex[2];
                        $text_out = $text;

                        $check = ParseLog::where('token', $link)->first();

                        if (empty($check)) {
                            $thumb_id = $parse->uploadImage($thumb);
                            $parse_id = $parse->crated($title, $dd_post, $dd_post, "publish", 12, 11, $text_out, $thumb_id);

                            $output->writeln('<comment>' . $parse_id . '</comment>');
                            if (!empty($parse_id)) {
                                $VARS['item'] = $parse_id;
                                $VARS['token'] = $link;
                                $VARS['site_id'] = 2;

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