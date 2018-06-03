<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use \QCloud_WeApp_SDK\Auth\LoginService as LoginService;
use QCloud_WeApp_SDK\Constants as Constants;
use QCloud_WeApp_SDK\Model\Share as shareModel;
use \QCloud_WeApp_SDK\Model\User as UserModel;
use QCloud_WeApp_SDK\Model\Coupon as couponModel;
use QCloud_WeApp_SDK\Model\Store as storeModel;
use QCloud_WeApp_SDK\FunctionCodeConstants as funCodeConst;

class Store extends CI_Controller {
    
    public function getStoreCnt(){
        $met = $this->input->method();
        if (strcasecmp($met, "get") != 0) {
            $this->json(["code" => funCodeConst::NEED_GET_METHOD['code'], "msg" => __FUNCTION__ . ".".funCodeConst::NEED_GET_METHOD['msg']]);
            return;
        }
        
        $cnt=storeModel::getStoreCnt();
        
        $this->json([
            'code'=>0,
            'msg'=>'',
            'data'=>[
                'cnt'=>$cnt
            ]
        ]);
    }
    
    public function getStoreByPage(){
        $met = $this->input->method();
        if (strcasecmp($met, "get") != 0) {
            $this->json(["code" => funCodeConst::NEED_GET_METHOD['code'], "msg" => __FUNCTION__ . ".".funCodeConst::NEED_GET_METHOD['msg']]);
            return;
        }

        $offset = $this->input->get("offset");
        $cnt= $this->input->get("cnt");
        
        if($offset==NULL || $offset<0){
            $offset=0;
        }
        if($cnt==NULL || $cnt<=0){
            $cnt=5;
        }
        
        $res=storeModel::getStoresByPage($offset, $cnt);
        
         $this->json([
            'code'=>0,
            'msg'=>'',
            'data'=>[
                'cnt'=>count($res),
                'items'=>$res
            ]
        ]);
        
    }
}
