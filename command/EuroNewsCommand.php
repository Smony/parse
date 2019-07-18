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

class EuroNewsCommand extends Command
{
    protected static $defaultName = 'app:euro-news';

    public $url = "https://www.euronews.com/programs/world";

    protected function configure()
    {
        $this
            ->setDescription('Parse a news.')
            ->setHelp('This command allows you to parse a news...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Parse Euro News');
        $parse = new Parse();

        $text = "
        *Euro News*
         _парсер был запущен_
         [Open this link](https://thetop10news.com/author/euronews/)
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

        foreach ($new->find('#enw-program-articles .media-section article .media__main') as $item) {
            $news = pq($item);

            $title = $news->find('.media__body .media__body__wrapper .media__body__title a')->html();
            $title = trim($title);
            $link = $news->find('.media__body .media__body__wrapper .media__body__title a')->attr('href');
            $link = "https://www.euronews.com" . $link;
            $published = $news->find('.media__body .media__body__wrapper .media__body__label time')->html();
            $published = trim($published);
//            $thumb = $news->find('.media__img .media__img__wrapper a img')->attr('data-src');

            if (!empty($title)) {
                $content = $link;
                $con = phpQuery::newDocument(file_get_contents($content));
                foreach ($con->find('#enw-main-content section article') as $item2) {
                    $content2 = pq($item2);
                    $text = $content2->find('section .js-responsive-iframes-container')->html();
                    $text = preg_replace('/\<div class\="c-font-size-switcher medium-order-4 js-font-size-switch u-float-end u-padding-top-0 t-font-size-switcher--blue"\>(.+)\<\/div\>/isU', '', $text);
                    $text = preg_replace('/\<div class\="widget widget--type-freeform
widget--size-fullwidth
widget--align-center"\>(.+)\<\/div\>/isU', '', $text);
                    $text .= "<p><a target=\"_blank\" rel=\"nofollow\" href=\"$link\">Source</a></p>";
                }
            }

            try {
                $dd_post = explode('/', $published);
                $m = $dd_post[1];
                $y = $dd_post[2];
                $d = $dd_post[0];
                $t = '00:00';

                $dd_post = $y . '-' . $m . '-' . $d . 'T' . $t . ':00';

                $check = ParseLog::where('token', $link)->first();

                if (empty($check)) {
//                    $thumb_id = $parse->uploadMedia($thumb);
                    $parse_id = $parse->crated($title, $dd_post, $dd_post, "publish", 13, 9, $text, 1);

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