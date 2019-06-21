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


class ChinaPlusCommand extends Command
{

    protected static $defaultName = 'app:china-plus-news';

    public $url = "http://chinaplus.cri.cn/news/world/index.html";
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
        $io->title('Parse China Plus');

        $this->parser($input, $output, $this->url, $this->start, $this->end, $io);

//        $this->telegramMessage("CBS NEWS инициализирован - " . Carbon::now());
//
//        $io->progressStart();
//        $io->newLine();
//
//        $this->telegramMessage("CBS NEWS Запущен - " . Carbon::now());
//
//        $return = $this->parser($input, $output, $this->url, $this->start, $this->end, $io);
//
//        $io->progressFinish();
//
//        $this->telegramMessage("CBS NEWS - Выполнен " . Carbon::now());
//        $this->telegramMessage("Проверено/Добавлено - " . $return);
    }

    public function parser(InputInterface $input, OutputInterface $output, $url, $start, $end, SymfonyStyle $style)
    {
        $parse = new Parse();
        $VARS = [];

        $count_out = 0;
        $count_check = 0;

//        if ($start < $end) {
//            $file = file_get_contents($url . $start . '/');
            $file = file_get_contents($url);

            $new = phpQuery::newDocument($file);

            foreach ($new->find('.newsList-con .news-item') as $item) {
                $news = pq($item);

                $title = $news->find('.news-item-text h3 a')->html();
                $link = $news->find('.news-item-text h3 a')->attr('href');
                $thumb = $news->find('.news-item-photo img')->attr('src');

                if (!empty($title)) {

                    $content = $link;
                    $con = phpQuery::newDocument(file_get_contents($content));
                    foreach ($con->find('.article-left') as $item2) {
                        $content2 = pq($item2);
                        $published = $content2->find('.article-type .article-type-item-time')->html();
                        $text = $content2->find('.article-con')->html();
                    }


                    try {
                        $ex = explode(" ", $published);
                        $dd_post = $ex[1] . 'T' . $ex[2];
                        $text_out = $text;

                        $check = ParseLog::where('token', $link)->first();

                        if (empty($check)) {

                            dump($thumb);
                         $parse->uploadImage("http://img3.zhytuku.meldingcloud.com/images/zhycms_chinaplus/20190622/d9ff00c6-9834-4463-b8af-baedee0de323.jpg?x-oss-process=image%2Fresize%2Cw_720%2Ch_405");
//                            $thumb_id = $parse->uploadImage($thumb);
//                            $parse_id = $parse->crated($title, $dd_post, $dd_post, "publish", 4, 6, $text_out, $thumb_id);
//                            $parse->crated($title, $dd_post, $dd_post, "publish", 4, 6, $text_out, $thumb_id);

                            //$output->writeln('<comment>' . $parse_id . '</comment>');
//                            if (!empty($parse_id)) {
//                                $VARS['item'] = $parse_id;
//                                $VARS['token'] = $link;
//                                $VARS['site_id'] = 2;
//
//                                ParseLog::create($VARS);
//                            }
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

//                        $style->progressAdvance(10);
//                        $style->newLine();
                    } catch (Exception $e) {
                        $output->writeln('<error>error..</error>');
                        exit;
                    }
                }

            }

//            $start++;
//            $this->parser($input, $output, $url, $start, $end, $style);
//        }

        return $count_check . '/' . $count_out;
    }


    public function telegramMessage($message)
    {
            $token = $_ENV['API_TELEGRAM'];
            $chatid = $_ENV['CHAT_ID'];
            $mess = $message;
            $tbot = file_get_contents("https://api.telegram.org/bot".$token."/sendMessage?chat_id=".$chatid."&text=".urlencode($mess)); //Если нашли ошибку отправляем  сообщение в телеграмм

    }
}