<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace QCloud_WeApp_SDK\Model;

use QCloud_WeApp_SDK\Mysql\Mysql as DB;
use QCloud_WeApp_SDK\Constants;
use \Exception;

/**
 * Description of Payment
 *
 * @author gumh
 */
class Payment {
    //put your code here
    
    public static function addPayRecord($data){
        return DB::insert("OrderRecord", $data);
    }
    /**
     * 获取一条记录
     * @param type $conditions
     * @return type
     */
     public static function getOnePayrecByConditions($conditions){
         return DB::row("OrderRecord", ['*'], $conditions);
    }
    
    public static function getPaymentRecsByPage($customer_id,$offset,$cnt){
        if($offset<0){
            $offset=0;
        }
        if($cnt<=0){
            return [];
        }
        $sql="select * from OrderRecord where payer=".$customer_id.' order by pay_time desc limit '.$offset.','.$cnt;
        if($orderby!=''){
            $sql=$sql.' order by '.$orderby;
        }
        return DB::raw_select($sql);
    }
    
    /**
     * 查询用户订单的总数据
     * 返回 ：
     * 订单总数量
     * 订单总数额
     * 订单实际支付金额
     * 
     */
    public static function getUserPayStatistic($customer_id){
        $sql="select count(id) as cnt,sum(total_price) total_amount,sum(final_price) pay_amount from OrderRecord where payer=$customer_id";
        $res=DB::raw_select($sql);
        return $res[0];
    }
}
