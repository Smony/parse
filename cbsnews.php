<?php

require __DIR__ . '/vendor/autoload.php';

$url = "latest/politics/1/";

$file = file_get_contents($url);

$new = phpQuery::newDocument($file);

foreach ($new->find('#component-politics .component__item-wrapper article') as $item) {
    $news = pq($item);

    $title = $news->find('h4')->html();
    $link = $news->find('a')->attr('href');
    $thumb = $news->find('.item__thumb img')->attr('src');

    if (!empty($title)) {

        echo $title;
        echo "<br/>";
        echo $link;
        echo "<br/>";

//        echo "<img src='". $thumb ."' width='60px'><br/>";
        echo "<br/>";
        $content = $link;
        $con = phpQuery::newDocument(file_get_contents($content));
        foreach ($con->find('.content-article') as $item2) {
            $content2 = pq($item2);

            if (strpos($link, '/news/'))
            {
                $video = $content2->find('figure iframe')->attr('src');
                $text = $content2->find('section')->html();
                $text = preg_replace('/\<figure class\="content-recirculation"\>(.+)\<\/figure\>/isU', '', $text);
            }else{
                dump(12);
            }



        }

//        echo "<iframe src='". $video ."' width=468 height='60' align='left'></iframe>";

        echo $text;
//        dump(strpos($link, '/news/'));

        echo "<br/>";
        echo "<br/>";
        echo "<br/>";
        echo "<br/>";
        echo "<br/>";
        echo "<br/>";

        echo "<hr>";
    }

}

