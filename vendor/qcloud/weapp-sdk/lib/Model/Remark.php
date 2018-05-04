<?php
namespace QCloud_WeApp_SDK\Model;

use QCloud_WeApp_SDK\Mysql\Mysql as DB;
use QCloud_WeApp_SDK\Constants;
use \Exception;

class Remark
{
	/**
	  * 获取全部的点评模板信息，以seq升序来排
	  *
	  */
	public static function getRemarkTemplateInfo(){
		return DB::select('RemarkTemplate',['*'],'status=1','','order by seq asc');	
	}
	
	/* 获取用户最新的点评 */
	public static function getUserLatestRemark($customerId,$storeId,$tableId){
		return DB::select('CustomerRemarkRecord',['*'],['customer_id'=>$customerId ,'tableId'=>$tableId ,'storeId'=>$storeId],'and','order by remark_time desc limit 1');		
	}
	
	/**
	 * customer_id是用户的数据库id
	 * remarkData是stdclass,包含有：desc：字符串，scores：数组，storeId：数字，tableId：数字
	 *
	*/
	public static function addUserRemark($input_customer_id,$remarkData){
		//插入CustomerRemarkRecord
		$customer_id=input_customer_id;
		$remark_time=date('Y-m-d H:i:s');
		$order_id=-1;
		$extra_remark_desc=$remarkData->extraDesc;
		$tableId=$remarkData->tableId;
		$storeId=$remarkData->storeId;
		
		DB::insert('CustomerRemarkRecord', compact('customer_id', 'remark_time', 'order_id', 'extra_remark_desc', 'tableId', 'storeId'));
		$res = DB::row('CustomerRemarkRecord', ['*'], compact('remark_time'));
		
		$customer_remark_rec_id=$res->customer_remark_rec_id;
		
		//插入CustomerRemarkDetail
		foreach($remarkData as $scoreItem){
			$remark_template_id=$scoreItem->remarkTempId;
			$remark_score=$scoreItem->score;
			DB::insert('CustomerRemarkDetail', compact('customer_remark_rec_id','remark_template_id', 'remark_score'));		
		}
		
		return $res;
		
	}
	
}
