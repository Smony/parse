<?php

use Illuminate\Database\Capsule\Manager as Capsule;
require "phpQuery.php";

class Parse
{

    public $tables = ['parse_log', 'sites'];


    public function __construct()
    {


    }


    //for testing
    public function dropTable()
    {
        Capsule::schema()->dropIfExists($this->tables[0]);
        Capsule::schema()->dropIfExists($this->tables[1]);
    }

    public function createTable()
    {
        Capsule::schema()->create($this->tables[0], function ($table){
            $table->increments('id');
            $table->integer('item')->index();
            $table->string('token');
            $table->integer('site_id')->index();
            $table->timestamps();

        });

        Capsule::schema()->create($this->tables[1], function ($table){
            $table->increments('id');
            $table->string('name');
            $table->string('link')->nullable();
            $table->string('cat_name')->nullable();
            $table->boolean('status')->default(false);
            $table->timestamps();

        });
    }

//


}