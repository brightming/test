<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use \QCloud_WeApp_SDK\Auth\LoginService as LoginService;
use QCloud_WeApp_SDK\Constants as Constants;
use \QCloud_WeApp_SDK\Model\Coupon as CouponModel;

class User extends CI_Controller {
    public function index() {
        $result = LoginService::check();

        if ($result['loginState'] === Constants::S_AUTH) {
            $this->json([
                'code' => 0,
                'data' => $result['userinfo']
            ]);
        } else {
            $this->json([
                'code' => -1,
                'data' => []
            ]);
        }
    }
	
	public function getMyCoupon(){
		
		$openId=$_POST["openId"];	
		$totalCouponCnt=$_POST["totalCouponCnt"];//总数量，如果是第一次获取，此参数是负数，那么就需要先获取总数量
		$startIdx=$_POST["startIdx"];//分页查询的起始编号
		$needCnt=$_POST["needCnt"];//获取多少条
		
		if($totalCouponCnt<0){
			
		}
		
		
		
	}
	
	
	public function getUserDrawMoneyRec(){
		$customer_id=2;
		$store_id=1;
		$res=CouponModel::getUseDrawCacheRecToday($customer_id,$store_id);
		if($res==NULL){
			echo 'no rec';
		}else{
			echo "cnt=".count($res);
			foreach($res as $row){  
			 echo 'store_id='.$row['store_id'].'<br/>';  
			}  
			//$res2=json_encode($res);
            //echo $res2;
			
		}
		//$res2=json_encode($res);
        //echo $res2;		
		
		
		
	}

	/**
	* 判断某用户此时是否能抽奖
	*/
    public function canDrawMoney(){
         //token是判断的依据
		$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
		$mypost = json_decode($rws_post);
		
		
		$token=$mypost->token;
		$storeId=$mypost->storeId;
		$tableId=$mypost->tableId;
		$openId=$mypost->openId;
		
		$userinfo = User::findUserByOpenId($openId);
		
		//获取最新的token
		$setting=CouponModel::getDrawCash($storeId);
		if($setting==NULL){
			//此店未设置抽奖
			 $this->json([
                'code' => -1,
                'desc' =>'此店未设置抽奖'
            ]);
			return;
		}
		
		//对比token
		if($token!=$setting->token){
			//用了过时的token
			 $this->json([
                'code' => -2,
                'desc' =>'无效凭证'
            ]);
			return;
			
		}
		
		//用户抽奖的记录
		
		


    }	

}
