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
 * Description of Share
 *
 * @author gumh
 */
class Share {
    //put your code here
    /**
     * 分页获取分享模板信息
     * @param type $offset
     * @param type $cnt
     * @return type
     */
    public static function getShareTempsByPages($offset,$cnt){
        $sql="select a.id,a.content ,b.pict_url from (select id,content from ShareTemplate where status=1 limit $offset,$cnt)as a inner join ShareTemplatePicts as b on a.id=b.share_temp_id";
        return DB::raw_select($sql);
    }
    
    /**
     * 按照id查询分享模板的详情
     * @param type $id
     * @return type
     */
    public static function getShareTempById($id){
        return DB::row("ShareTemplate", ['*'], "id=".$id);    
    }
    
    /**
     * 做一个模板分享
     * @param type $data
     */
    public static function addShareRec($data){      
        return DB::insert("UserShareRec", $data);
    }
    
    
    public static function getShareById($id){
         return DB::row("UserShareRec", ['*'], "id=".$id);    
    }
    
    public static function getUserShareByTimestrAndCustomerId($customer_id,$timestr){
        return DB::row("UserShareRec", ['*'], "customer_id=".$customer_id.' and create_time="'.$timestr.'"');
    }
    
    public static function updateUserShareById($id,$data){
        return DB::update("UserShareRec", $data,'id='.$id);
    }
    
    
    
    /**
     * 创建一张限时的满多少减多少的优惠券
     * @param type $related_id
     * 对应操作的id，如分享记录的id
     * @param type $create_customer_id
     * 源用户id
     * @param type $take_customer_id
     * 优惠券分配的用户id
     * @param type $create_reason
     * @param type $type
     * @param type $get_from_where
     * @return type
     */
    public static function makeCoupon($related_id,$create_customer_id,$take_customer_id,$create_reason,$type,$get_from_where){
        $limit_price=mt_rand(100,500);
        $minus_price=mt_rand(10,$limit_price/2);   
        $create_time=date('Y-m-d H:i:s',time());
        $expired_time=date('Y-m-d H:i:s',strtotime('+60 day'));
        
        $coupon=["limit_price"=>$limit_price,'minus_price'=>$minus_price,'create_time'=>$create_time,'expired_time'=>$expired_time
                ,'related_id'=>$related_id,
            'create_customer_id'=>$create_customer_id,
            'customer_id'=>$take_customer_id ,'create_reason'=>$create_reason, 'type'=>$type,'is_used'=>0,'get_from_where'=>$get_from_where];
        
        return $coupon;
        
    }
    
    /**
     * 保存优惠券信息到数据库
     * @param type $coupon
     */
    public static function saveCoupon($coupon){
        return DB::insert("Coupon", $coupon);
    }
    
    /**
     * 使用一张优惠券,同时，关联对应的支付记录id
     * @param type $coupon_id
     * @param type $use_customer_id
     * @param type $pay_id
     * @return type
     */
    public static function useCouponInPay($coupon_id,$use_customer_id,$pay_id){
        $tableName="Coupon";    
        $updates=["is_used"=>1,'use_time'=>date("Y-m-d H:i:s",time()),'use_customer_id'=>$use_customer_id,'related_pay_id'=>$pay_id];
        $conditions="id=$coupon_id and is_used=0";
        return DB::update($tableName, $updates, $conditions);
    }
    
    /**
     * 增加返现的记录
     * @param type $cashbackRec
     * @return type
     */
    public static function addCashbackRec($cashbackRec){
        return DB::insert("CashbacRec", $cashbackRec);
    }
    /**
     * 查询满足条件的返现情况
     * @param type $conditions
     */
    public static function getCashbackRecByCondition($conditions){
        return DB::row("CashbacRec", ['*'], $conditions);
    }
    
    /**
     * 统计符合条件的优化券的数量
     * @param type $conditions
     */
    public static function countCouponByConditions($conditions){
       $sql="select count(id) as cnt from Coupon where ".$conditions;
       $res=DB::raw_select($sql);
       if(count($res)==0){
           return 0;
       }else{
           $one=$res[0];
           return $one->cnt;
       }
    }
    
    
    public static function countUesrShareRecNumberByUserId($customer_id){
        $sql="select count(id) as cnt from UserShareRec where customer_id=".$customer_id;
        $res=DB::raw_select($sql);
        $one=$res[0];
        return $one->cnt;
        
    }
    
    /**
     * 分页获取用户的分享信息
     * 
     * @param type $customer_id
     * @param int $offset
     * @param type $cnt
     * @param type $orderby
     * @return type
     */
    public static function getUserShareByPage($customer_id,$offset,$cnt,$orderby=''){
        if($offset<0){
            $offset=0;
        }
        if($cnt<=0){
            return [];
        }
        $sql="select * from UserShareRec where customer_id=".$customer_id.' limit '.$offset.','.$cnt;
        if($orderby!=''){
            $sql=$sql.' order by '.$orderby;
        }
        return DB::raw_select($sql);
    }
    
    /**
     * 通过分享id获取图片列表
     * @param type $share_id
     */
    public static function getSharePictureListByShareId($share_id){
        return DB::select("CustomerUploadFiles", ['name'], ['related_id'=>$share_id]);
    }
    
    /**
     * 通过指定的分享id，获取对应的优惠券信息
     * @param type $share_id
     * @param type $contain_cash_back
     * 是否包含返现的信息
     */
    public static function getCouponDetailByShareid($share_id,$contain_cash_back=false){
        if($contain_cash_back==false){
            return DB::select("Coupon", ['*'], ['related_id'=>$share_id,'create_reason'=>2]);
        }else{
            $sql="select a.* ,b.cashback from (select * from Coupon where related_id=$share_id and create_reason=2) as a left join (select cashback,use_coupon_id from CashbacRec where share_id=$share_id) as b on a.id=b.use_coupon_id";
            return DB::raw_select($sql);
        }
    }
    
    /**
     * 获取某用户得到的总返现信息，返现次数
     * @param type $customer_id
     */
    public static function getTotalCashbackByCustomer($customer_id){
        $sql="select count(id) as cnt,sum(cashback) as total_cashback from CashbacRec where customer_id=$customer_id ";
        $rec=DB::raw_select($sql);
        if(count($rec)==0){
            return NULL;
        }else{
            return $rec[0];
        }
    }
    
    /**
     * 分页获取返现信息
     * @param type $customer_id
     * @param type $offset
     * @param type $cnt
     * @return type
     */
    public static function getCashbackDataByCustomerByPage($customer_id,$offset,$cnt){
        if($offset<0 || $cnt<0){
            return [];
        }
        $sql="select a.*,b.name from (select * from CashbacRec where customer_id=$customer_id order by create_time desc limit $offset,$cnt  ) a left join cSessionInfo as b on a.use_coupon_customer_id=b.id; ";
        return DB::raw_select($sql);
    }
    
    /**
     * 分页获取符合条件的优惠券
     * @param type $conditions
     * @param type $order
     * @param type $offset
     * @param type $cnt
     */
    public static function getCouponByConditionsByPage($conditions,$order='',$offset=0,$cnt=5){
        if($offset<0 || $cnt<0){
            return [];
        }
        $sql="select * from Coupon where ".$conditions;
        if($order!=""){
            $sql=$sql.$order." limit $offset,".$cnt;
        }else{
            $sql=$sql." limit $offset,".$cnt;
        }
        return DB::raw_select($sql);
    }
}
