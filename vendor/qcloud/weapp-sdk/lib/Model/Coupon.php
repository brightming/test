<?php

namespace QCloud_WeApp_SDK\Model;

use QCloud_WeApp_SDK\Mysql\Mysql as DB;
use QCloud_WeApp_SDK\Constants;
use \Exception;
use QCloud_WeApp_SDK\Model\Common as commonModel;

class Coupon {

  
    public static function getCouponById($id){
        $conditions="id=$id";
        return DB::row("Coupon", ['*'], $conditions);
    }
    //获取store_id对应抽奖设置
    public static function getDrawCashSetting($use_store_id) {
        $sql = "SELECT * FROM DrawCashSetting WHERE use_store_id=" . $use_store_id;
        return DB::raw_select($sql);
    }
    
    /**
     * 计算该返现多少给分享者
     * @param type $totalAmount
     * @param type $coupon
     */
    public static function calculateCashback($totalAmount,$coupon){
        //TODO 
        $res= $totalAmount/80;
        return $res;
    }
    
    
    

    /**
     *  记录用户的抽奖行为
     */
    public static function noteDownUserDrawCashRec($customer_id, $other_data) {
        $create_time = date('Y-m-d H:i:s');
        DB::insert('CacheDrawRecord', compact('customer_id', 'create_time'));
    }

    /**
     * 将用户抽中的现金的记录进行记录
     */
    public static function assignCacheToUser($customer_id, $money_amount, $sent, $store_id) {

        $create_time = date('Y-m-d H:i:s');
        DB::insert('CashAssignRecord', compact('customer_id', 'create_time', 'sent', 'store_id', 'money_amount'));
    }

    //获取用户今天日期的现金抽奖记录
    public static function getUseDrawCacheRecToday($customer_id, $store_id) {
        $sql = "SELECT * FROM CacheDrawRecord WHERE customer_id=" . $customer_id . " and store_id=" . $store_id . " and TO_DAYS(`create_time`) = TO_DAYS(NOW())";
        return DB::raw_select($sql);
    }

}
