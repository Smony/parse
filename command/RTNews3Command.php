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

class RTNews3Command extends Command
{
    protected static $defaultName = 'app:rt-news-russia';

    public $url = "https://www.rt.com/russia/";

    protected function configure()
    {
        $this
            ->setDescription('Parse a news.')
            ->setHelp('This command allows you to parse a news...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title("Parse RT's");
        $parse = new Parse();

        $text = "
        *Parse RT (russia)*
         _парсер был запущен_
         [Open this link](https://thetop10news.com/author/rt/)
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

        foreach ($new->find('.js-listing ul li') as $item) {
            $news = pq($item);

            $title = $news->find('.card strong a')->html();
            $link = $news->find('.card strong a')->attr('href');
            $link = "https://www.rt.com" . $link;
            $thumb = $news->find('.card .card__cover noscript img')->attr('src');
            $thumb = str_replace("xxs", "xxl", $thumb);
            $published = $news->find('.card .card__date span')->html();
            $published = trim($published);

            if (!empty($title)) {

                $content = $link;
                $con = phpQuery::newDocument(file_get_contents($content));
                foreach ($con->find('.article ') as $item2) {
                    $content2 = pq($item2);

                    $text = $content2->find('.article__text')->html();
                    $text = preg_replace('/\<a class\="read-more-big"\ href=\"(.+)\"\>(.+)\<\/a\>/isU', '', $text);
                    $text = preg_replace('/\<div class\="read-more"\>(.+)\<\/div\>/isU', '', $text);
                    $text = preg_replace('/\<a class\="read-more__link"\ href=\"(.+)\"\>(.+)\<\/a\>/isU', '', $text);

                    $text .= "<p><a target=\"_blank\" rel=\"nofollow\" href=\"$link\">Source</a></p>";
                }
            }

            try {
                $text_out = $text;
                $dd_post = explode(" ", $published);

                $m = "0" . $parse->getMonths($dd_post[0]);
                $y = $dd_post[2];
                $d = str_replace(',', '', $dd_post[1]);
                $t = $dd_post[3];

                $dd_post = $y . '-' . $m . '-' . $d . 'T' . $t . ':00';

                $check = ParseLog::where('token', $link)->first();

                if (empty($check)) {
                    $thumb_id = $parse->uploadImage($thumb);

                    $parse_id = $parse->crated($title, $dd_post, $dd_post, "publish", 13, 8, $text_out, $thumb_id);
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