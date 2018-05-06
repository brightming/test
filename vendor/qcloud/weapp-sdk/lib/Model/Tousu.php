<?php
namespace QCloud_WeApp_SDK\Model;

use QCloud_WeApp_SDK\Mysql\Mysql as DB;
use QCloud_WeApp_SDK\Constants;
use \Exception;

class Tousu
{
	/**
	  * 获取全部的点评模板信息，以seq升序来排
	  *
	  */
	public static function getTousuTemplateInfo(){
		return DB::select('ComplaintTemplate',['*'],'status=1','','order by id asc');	
	}
	
	
	public static function addUserTousu($input_customer_id,$tousuData){
		//插入CustomerRemarkRecord
		$customer_id=$input_customer_id;
		$create_time=date('Y-m-d H:i:s');
		$extra_comment=$tousuData->extraDesc;
		$table_id=$tousuData->tableId;
		$store_id=$tousuData->storeId;
		$picture_cnt=$tousuData->picture_cnt;
		$picture_dir=$tousuData->picture_dir;
		$order_id=strtotime($create_time);
		
		$complaint_ids=implode(",",$tousuData->tousu);
		
		DB::insert('ComplaintRecord', compact('order_id','extra_comment','customer_id', 'complaint_ids', 'create_time', 'table_id', 'store_id','picture_cnt','picture_dir'));
		$res = DB::row('ComplaintRecord', ['*'], compact('order_id','table_id'));
		
		if($res==NULL){
			
		}else{
			//把投诉的模板记录记下  TODO
			
		}
		//$customer_complain_id=$res->id;
		
		//插入CustomerRemarkDetail
		//foreach($remarkData->scores as $scoreItem){
		//	$remark_template_id=$scoreItem->remarkTempId;
		//	$remark_score=$scoreItem->score;
		//	DB::insert('CustomerRemarkDetail', compact('customer_remark_rec_id','remark_template_id', 'remark_score'));		
		//}
		
		return $res;
		
	}
	
}