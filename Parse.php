<?php

use Illuminate\Database\Capsule\Manager as Capsule;
require "phpQuery.php";

class Parse
{

    public $table = 'parse_log';


    public function __construct()
    {


    }

    public function dropTable()
    {
        Capsule::schema()->dropIfExists($this->table);
    }

    public function createTable()
    {
        Capsule::schema()->create($this->table, function ($table){
            $table->increments('id');
            $table->string('site')->nullable();
            $table->integer('item')->index();
            $table->string('token');
            $table->timestamps();

        });
    }

//


}