<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use QCloud_WeApp_SDK\Constants as Constants;
use \QCloud_WeApp_SDK\Model\Remark as remarkModel;
use \QCloud_WeApp_SDK\Model\User as User;

/**
点评相关

*/
class Remark extends CI_Controller {
   
public function addRemark(){
		
		$uri = $_SERVER['REQUEST_URI']; 
		
		$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
		$mypost = json_decode($rws_post);
		
		
		$desc=$mypost->extraDesc;
		$scores=$mypost->scores;
		$storeId=$mypost->storeId;
		$tableId=$mypost->tableId;
		$openId=$mypost->openId;
		
		
		//查看这个openid的用户，对于这个店的这个桌子的点评，最近的一次是在什么时候，如果相隔不超过1小时，则拒绝评论
		$userinfo = User::findUserByOpenId($openId);
		$latest=remarkModel::getUserLatestRemark($userinfo->open_id,$storeId,$tableId);
		if($latest==NULL){
			//可以写
			foreach($mypost->scores as $score){
				print_r($score->remarkTempId);
	            //remarkModel::addUserLatestRemark($mypost);
			}
			
		}else{
			//判断时间差是否满足要求，如果时间距离太近，则认为是已经点评过了，不允许再点评
			
			
		}
		/**/
			$this->json([
			'uri'=>$uri,
			'openId'=>$this->input->post('openId'),
			'rws_post'=>$rws_post,
			'latest'=>$latest
			]);	
	}
}
