<?php

require __DIR__ . '/vendor/autoload.php';

use Model\ParseLog\ParseLog;

$parse = new Parse();

$start = 1;
$end = 2;

$url = "https://www.cbsnews.com/latest/politics/";

parser($url, $start, $end);

function parser($url, $start, $end)
{
    global $parse;
    $VARS = [];

    if ($start < $end) {
        $file = file_get_contents($url . $start . '/');

        $new = phpQuery::newDocument($file);

        foreach ($new->find('#component-politics .component__item-wrapper article') as $item) {
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

                        if (!empty($video)){
                            $online = "<p><iframe src=\"$video\" width=\"100%\" height=\"350px\" frameborder=\"0\"></iframe></p>";
                        }else{
                            $online = '';
                        }

                        $text = $content2->find('section')->html();
                        $text = preg_replace('/\<figure class\="content-recirculation"\>(.+)\<\/figure\>/isU', '', $text);
                        $text .= "<p><a target=\"_blank\" rel=\"nofollow\" href=\"$link\">Source</a></p>";

                        $published = $content2->find('.content__published-on small')->html();
                    }


                }

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

                if (empty($check))
                {
                    $thumb_id = $parse->uploadImage(substr($thumb, 0, -1));

                    $parse_id = $parse->crated($title, $dd_post, $dd_post, "publish", 12, 2, $text_out, $thumb_id);

                    dump($parse_id);
                    if (!empty($parse_id)){
                        $VARS['item'] = $parse_id;
                        $VARS['token'] = $link;
                        $VARS['site_id'] = 1;

                        ParseLog::create($VARS);
                    }
                    dump('добавлено..');
                }else {
                    dump('есть в базе..');
                }

            }

        }

        $start++;
        parser($url, $start, $end);
    }
}
