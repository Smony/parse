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

class CbsWorldCommand extends Command
{
    protected static $defaultName = 'app:cbs-world';

    public $url = "https://www.cbsnews.com/latest/world/";
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
        $io->title('Parse Cbs World');
        $parse = new Parse();

        $text = "
        *Parse CBS World*
         _парсер был запущен_
         [Open this link](https://thetop10news.com/author/cbs-news/)
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
            $file = file_get_contents($url . $start . '/');

            $new = phpQuery::newDocument($file);

            foreach ($new->find('#component-world .component__item-wrapper article') as $item) {
                $news = pq($item);

                $title = $news->find('h4')->html();
                $link = $news->find('a')->attr('href');
                $thumb = $news->find('.item__thumb img')->attr('src');

                if (!empty($title)) {
                    $content = $link;
                    $con = phpQuery::newDocument(file_get_contents($content));
                    foreach ($con->find('.content-article') as $item2) {
                        $content2 = pq($item2);

                        if (strpos($link, '/news/')) {
                            $video = $content2->find('figure iframe')->attr('src');

                            if (!empty($video)) {
                                $online = "<p><iframe src=\"$video\" width=\"100%\" height=\"550px\" frameborder=\"0\"></iframe></p>";
                            } else {
                                $online = '';
                            }

                            $text = $content2->find('section')->html();
                            $text = preg_replace('/\<figure class\="content-recirculation"\>(.+)\<\/figure\>/isU', '', $text);
                            $text .= "<p><a target=\"_blank\" rel=\"nofollow\" href=\"$link\">Source</a></p>";

                            $published = $content2->find('.content__published-on small')->html();
                        }
                    }

                    try {
                        $ex = explode("on", $published);
                        $space = explode(" ", $ex[1]);
                        $dd = explode(":", $space[5]);
                        $m = $space[1];
                        $d = trim($space[2], ",");
                        $y = $space[3];
                        $h = $dd[0];
                        $mm = $dd[1];
                        $dd_post = date('c', mktime($h, $mm, 0, $parse->getMonth($m), $d, $y));

                        $text_out = $online . $text;

                        $check = ParseLog::where('token', $link)->first();

                        if (empty($check)) {
                            $thumb_id = $parse->uploadImage(substr($thumb, 0, -1));
                            $parse_id = $parse->crated($title, $dd_post, $dd_post, "publish", 13, 2, $text_out, $thumb_id);

                            $output->writeln('<comment>' . $parse_id . '</comment>');
                            if (!empty($parse_id)) {
                                $VARS['item'] = $parse_id;
                                $VARS['token'] = $link;
                                $VARS['site_id'] = 1;

                                ParseLog::create($VARS);
                            }
                            $style->success('добавлено..');
                        } else {
                            $style->warning('есть в базе..');
                        }

                        $style->newLine();
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