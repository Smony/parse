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

class DWCommand extends Command
{
    protected static $defaultName = 'app:dw-news';

    public $url = "https://www.dw.com/en/top-stories/world/s-1429";

    protected function configure()
    {
        $this
            ->setDescription('Parse a news.')
            ->setHelp('This command allows you to parse a news...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Parse DW News');
        $parse = new Parse();

        $text = "
        *DW News*
         _парсер был запущен_
         [Open this link](https://thetop10news.com/author/dw/)
        ";

        $parse->message_to_telegram($text);

        $this->parser($input, $output, $this->url, $io);
    }

    public function parser(InputInterface $input, OutputInterface $output, $url, SymfonyStyle $style)
    {
        $parse = new Parse();
        $VARS = [];

        $file = file_get_contents($url);
        $new = phpQuery::newDocument($file);

        foreach ($new->find('#bodyContent .left .basicTeaser') as $item) {
            $news = pq($item);
            $title = $news->find('.group .news a h2')->html();
            $title = trim($title);
            $link = $news->find('.group .news a')->attr('href');
            $link = "https://www.dw.com" . $link;
            $thumb = $news->find('.group .news a .teaserImg img')->attr('src');
            $thumb = str_replace("_301", "_304", $thumb);;
            $thumb = "https://www.dw.com" . $thumb;

            if (!empty($title)) {
                $content = $link;
                $con = phpQuery::newDocument(file_get_contents($content));
                foreach ($con->find('#bodyContent') as $item2) {
                    $content2 = pq($item2);
                    $published = $content2->find('.col3 .group .smallList li:first')->html();
                    $published = trim(substr($published, 22));
                    $text = $content2->find('.col3 .group .longText')->html();
                    $text = str_replace("/image/", "https://www.dw.com/image/", $text);
                    $text = str_replace("/en/", "https://www.dw.com/en/", $text);
                    $text .= "<p><a target=\"_blank\" rel=\"nofollow\" href=\"$link\">Source</a></p>";
                }

            }

            try {
                $dd_post = explode(".", $published);
                $m = $dd_post[1];
                $y = $dd_post[2];
                $d = $dd_post[0];
                $dd_post = $y . '-' . $m . '-' . $d . 'T00:00:00';

                $check = ParseLog::where('token', $link)->first();

                if (empty($check)) {
                    if (!empty($title)) {
                    $thumb_id = $parse->uploadImage($thumb);
                        $parse_id = $parse->crated($title, $dd_post, $dd_post, "publish", 13, 15, $text, $thumb_id);
                    }
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
}