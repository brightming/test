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
		$customer_id=1;
		$store_id=2;
		$res=CouponModel::getUseDrawCacheRecToday($customer_id,$store_id);
		if($res==NULL){
			echo 'no rec';
		}else{
			echo "cnt=".count($res);
			foreach($res as $row){  
			 echo 'store_id='.$row->store_id.' create_time='.$row->create_time.'<br/>';  
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
		$setting=CouponModel::getDrawCashSetting($storeId);
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
		
		//记录允许的时间段
		$oneset=$setting[0];
		$valid_time_rgs=$oneset->valid_time_ranges;//9:00-12:00,17:00-19:00
		$subrgs=explode(",",$valid_time);
		
		//判断当前属于哪个时间段
		$inwhich=-1;
		$idx=0;
		$checkDayStr = date('Y-m-d ', time());  
		foreach($subrgs as $rg){//每个允许时间段
		    $rrgg=explode("-",$rg);
			if(count($rrgg)!=2){
				//配置数据有误，全部禁止参与
				 $this->json([
					'code' => -3,
					'desc' =>'配置数据有误！'
				]);
				return;
			}
			$timeBegin1 = strtotime($checkDayStr . "$rrgg[0]" . ":00");  
			$timeEnd1 = strtotime($checkDayStr . "$rrgg[1]" . ":00");  
			$curr_time = time();  
			if ($curr_time >= $timeBegin1 && $curr_time <= $timeEnd1) { 
				$inwhich=$idx;
				break;  
			}  	  
			$idx=$idx+1;
		}
		
		//用户抽奖的记录
		$customer_id=$userinfo.id;
		$res=CouponModel::getUseDrawCacheRecToday($customer_id,$storeId);
		if(count($res)>0){		
			//判断时间段
			foreach($res as $row){
				$idx=0;				
				$histtime=$row->create_time;
			  //如果发现已有记录的时间段，有包含在inwhich指向的时间段，则说明当前时间段已经操作过一次了，这次不允许再抽奖了
			    foreach($subrgs as $rg){//每个允许时间段
					$rrgg=explode("-",$rg);
					$timeBegin1 = strtotime($checkDayStr . "$rrgg[0]" . ":00");  
					$timeEnd1 = strtotime($checkDayStr . "$rrgg[1]" . ":00");   
					if ($histtime >= $timeBegin1 && $histtime <= $timeEnd1 && $idx==$inwhich) { 
						$this->json([
							'code' => -4,
							'desc' =>'同一段时间只允许一次抽奖！'
						]);
						return;  
					}  	  
					$idx=$idx+1;
			    }		  
			} 
		}
		
		$this->json([
			'code' => 1,
			'desc' =>'可以抽奖！'
		]);
		
		


    }	

}
