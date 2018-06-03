<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace QCloud_WeApp_SDK\Model;
use QCloud_WeApp_SDK\Mysql\Mysql as DB;
/**
 * Description of Setting
 *
 * @author gumh
 */
class Setting {
    //put your code here
    
    
    public static function getAllSettingByType($type){
        $sql="select * from SettingData where status=1";
        return DB::raw_select($sql);
    }
    
    
    public static function getDrawCashSetting($storeId){
        $sql="select * from DrawCashSetting where use_store_id=".$storeId ." and status=1";
        $arr= DB::raw_select($sql);
        if(count($arr)>=1){
            return $arr[0];
        }else{
            return NULL;
        }
    }
}
