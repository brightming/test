<?php
namespace QCloud_WeApp_SDK\Model;

use QCloud_WeApp_SDK\Mysql\Mysql as DB;
use QCloud_WeApp_SDK\Constants;
use \Exception;

class Common
{
   
    public static function raw_sql_select ($sql) {
        return DB::raw_select($sql);
    }
	
	
}
