<?php

use Illuminate\Database\Capsule\Manager as Capsule;

require "phpQuery.php";

class Parse
{

    public $tables = ['parse_log', 'sites'];


    public function __construct()
    {


    }

    /**
     * @param $title
     * @param $status
     * @param $categories
     * @param $author
     * @param $content
     * @param $featured
     * @return mixed
     */
    public function crated($title, $date, $date_gmt, $status, $categories, $author, $content, $featured)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://thetop10news.com/wp-json/wp/v2/posts",
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",

            CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"title\"\r\n\r\n$title\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"date\"\r\n\r\n$date\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"date_gmt\"\r\n\r\n$date_gmt\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"status\"\r\n\r\n$status\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"categories\"\r\n\r\n$categories\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"author\"\r\n\r\n$author\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"content\"\r\n\r\n$content\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"featured_media\"\r\n\r\n$featured\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",

            CURLOPT_HTTPHEADER => array(
                "Accept: */*",
                "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwczpcL1wvdGhldG9wMTBuZXdzLmNvbSIsImlhdCI6MTU2MDg4NDU0MSwibmJmIjoxNTYwODg0NTQxLCJleHAiOjE1NjE0ODkzNDEsImRhdGEiOnsidXNlciI6eyJpZCI6IjEifX19.umbiV5dlCxRm9t3K3GvqCOhwoRR2o3drEjNYLdAnqug",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Host: thetop10news.com",
                "accept-encoding: gzip, deflate",
                "cache-control: no-cache",
                "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW"
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($response);
        return $result->id;

    }

    /**
     * @param $image
     * @return mixed
     */
    public function uploadImage($image)
    {
        $file = file_get_contents($image);
        $url = 'https://thetop10news.com/wp-json/wp/v2/media';
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $file);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwczpcL1wvdGhldG9wMTBuZXdzLmNvbSIsImlhdCI6MTU2MDg4NDU0MSwibmJmIjoxNTYwODg0NTQxLCJleHAiOjE1NjE0ODkzNDEsImRhdGEiOnsidXNlciI6eyJpZCI6IjEifX19.umbiV5dlCxRm9t3K3GvqCOhwoRR2o3drEjNYLdAnqug",
            "Host: thetop10news.com",
            'Content-Disposition: attachment; filename="' . $image . '"',
            "content-type: image/png; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW"
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result);
        return $result->id;

    }

    //for testing
    public function dropTable()
    {
        Capsule::schema()->dropIfExists($this->tables[0]);
        Capsule::schema()->dropIfExists($this->tables[1]);
    }

    public function createTable()
    {
        Capsule::schema()->create($this->tables[0], function ($table) {
            $table->increments('id');
            $table->integer('item')->index();
            $table->string('token');
            $table->integer('site_id')->index();
            $table->timestamps();

        });

        Capsule::schema()->create($this->tables[1], function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->string('link')->nullable();
            $table->string('cat_name')->nullable();
            $table->boolean('status')->default(false);
            $table->timestamps();

        });
    }

    /**
     * @param $m
     * @return string
     */
    public function getMonth($m): string
    {
        $month = array(
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December'
        );

        return array_search($m, $month);
    }


//


}