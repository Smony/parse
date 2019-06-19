<?php
namespace Model\ParseLog;

use Illuminate\Database\Eloquent\Model;
use Model\Sites\Sites;

class ParseLog extends Model{

    public $table = 'parse_log';

    protected $guarded = [];

    public $timestamps = true;

    protected $fillable = ['item', 'token', 'site_id'];


}