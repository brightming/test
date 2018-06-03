<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace QCloud_WeApp_SDK\Model;
use QCloud_WeApp_SDK\Mysql\Mysql as DB;
/**
 * Description of Store
 *
 * @author gumh
 */
class Store {
    //put your code here
    public static function getAllStores(){
        
        $sql="select * from Store where status=1";
        return DB::raw_select($sql);
        
    }
    
    public static function addStore($store){
        //TODO
        
        return 0;
    }
    
    public static function getStoreById($id){
        return DB::row("Store", ['*'], " id=$id");
    }
}
