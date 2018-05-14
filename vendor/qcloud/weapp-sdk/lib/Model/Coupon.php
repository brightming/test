<?php
namespace QCloud_WeApp_SDK\Model;

use QCloud_WeApp_SDK\Mysql\Mysql as DB;
use QCloud_WeApp_SDK\Constants;
use \Exception;

class Coupon
{
    public static function countCoupon ($customer_id) {
       // 拼接 SQL 语句 ，统计各种状态的优惠券的数量
	   $conditions=['customer_id'=>$customer_id];
	   $operator='and';
	   // 处理条件
        list($condition, $execValues) = array_values(self::conditionProcess($conditions, $operator));

        // 拼接 SQL 语句
        $sql = "select * FROM `CustomerCoupon` WHERE $condition ";

        // 执行 SQL 语句
        $query = self::raw($sql, $execValues);
        return $query->rowCount();
    }

	/**
	*  分页获取获取某用户，某种状态的优惠券
	*/
	public static function getCouponByPage($customer_id,$coupon_status,$startIdx,$pageCnt){
		
		
		
		
	}
	
		
	/**
	* 分配优惠券给用户
	*/
	public static function assignConpon($customer_id,$coupon_data){
		
		
		
	}
	
		
	/**
	* 使用优惠券
	*
	*/
	public static function useCouponStatus($customer_id,$couponId,$options){
		
		
		
	}
	
	/**
	*  记录用户的抽奖行为
	*/
	public static function noteDownUserDrawCashRec($customer_id,$other_data){
		$create_time=date('Y-m-d H:i:s');
		DB::insert('CacheDrawRecord', compact('customer_id', 'create_time'));
	}
	
	/**
	* 将用户抽中的现金的记录进行记录
	*/
	public static function assignCacheToUser($customer_id,$money_amount,$sent,$store_id){
		
		$create_time=date('Y-m-d H:i:s');
		DB::insert('CashAssignRecord', compact('customer_id', 'create_time','sent','store_id','money_amount'));
		
	}
  
    //获取用户今天日期的现金抽奖记录
    public static function getUseDrawCacheRecToday($customer_id,$store_id){
		$sql="SELECT * FROM CacheDrawRecord WHERE TO_DAYS(`create_time`) = TO_DAYS(NOW())";
		$res=DB::getInstance()->query($sql);
		return $res;
		
	}
  
    
    


}
