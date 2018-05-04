<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use QCloud_WeApp_SDK\Constants as Constants;
use \QCloud_WeApp_SDK\Model\Remark as remarkModel;

/**
点评相关

*/
class Remark extends CI_Controller {
   
public function addRemark(){
		
		$uri = $_SERVER['REQUEST_URI']; 
		
		$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
		$mypost = json_decode($rws_post);
		
		/*
		$desc=$mypost['extraDesc'];
		$scores=$mypost['scores'];
		$storeId=$mypost['storeId'];
		$tableId=$mypost['tableId'];
		$openId=$mypost['openId'];
		
		
		//查看这个openid的用户，对于这个店的这个桌子的点评，最近的一次是在什么时候，如果相隔不超过1小时，则拒绝评论
		$latest=remarkModel::getUserLatestRemark($openId,$storeId,$tableId);
		if($latest==NULL){
			//可以写
			
		}else{
			//判断时间差是否满足要求，如果时间距离太近，则认为是已经点评过了，不允许再点评
			
			
		}
		*/
			$this->json([
			'uri'=>$uri,
			'openId'=>$this->input->post('openId'),
			'rws_post'=>$rws_post//,
			//'latest'=>$latest
			]);	
	}
}
