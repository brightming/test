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
		return DB::select('CustomerRemarkRecord',['*'],'customer_id=`$customerId` and tableId=`$tableId` and storeId=`$storeId`','','order by remark_time desc limit 1');		
	}
	
}
