<?php
namespace QCloud_WeApp_SDK\Model;

use QCloud_WeApp_SDK\Mysql\Mysql as DB;
use QCloud_WeApp_SDK\Constants;
use \Exception;

class FileRecord
{
    public static function storeFileRecord ($customer_id,$type, $related_id, $name,$size) { 
        DB::insert('CustomerUploadFiles', compact('customer_id', 'type', 'related_id', 'name', 'size'));
        return DB::row('CustomerUploadFiles', ['*'], compact('customer_id','related_id'));
    }

   public static function createDir($dir){
       
   }
	
}
