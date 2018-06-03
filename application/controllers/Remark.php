<?php

defined('BASEPATH') OR exit('No direct script access allowed');

use QCloud_WeApp_SDK\Constants as Constants;
use \QCloud_WeApp_SDK\Model\Remark as remarkModel;
use \QCloud_WeApp_SDK\Model\User as userModel;
use QCloud_WeApp_SDK\Model\Share as shareModel;
use QCloud_WeApp_SDK\FunctionCodeConstants as funCodeConst;

/**
  点评相关

 */
class Remark extends CI_Controller {

    public function getRemarkTemplate() {

        $met = $this->input->method();
        if (strcasecmp($met, "post") != 0) {
            $this->json(["code" => funCodeConst::NEED_POST_METHOD['code'], "msg" => __FUNCTION__ . "." . funCodeConst::NEED_POST_METHOD['msg']]);
            return;
        }

        $cont = $this->input->get_request_header('Content-Type', TRUE);
        $inputs = $this->input;
        if (strcasecmp($cont, "application/json") == 0) {
            $raw = $GLOBALS['HTTP_RAW_POST_DATA'];
            $inputs = json_decode($raw);
        }
        $lng = commonModel::get_obj_value($inputs, 'lng');
        $lat = commonModel::get_obj_value($inputs, 'lat');
        $unionId = commonModel::get_obj_value($inputs, 'unionId');
        $storeId = commonModel::get_obj_value($inputs, 'storeId');
        $tableId = commonModel::get_obj_value($inputs, 'tableId');

        $result = remarkModel::getRemarkTemplateInfo();
        $this->json(['data' => $result, 'code' => 0, 'msg' => 'getRemarkTemplate']);
    }

    public function addRemark() {

        $met = $this->input->method();
        if (strcasecmp($met, "post") != 0) {
            $this->json(["code" => funCodeConst::NEED_POST_METHOD['code'], "msg" => __FUNCTION__ . "." . funCodeConst::NEED_POST_METHOD['msg']]);
            return;
        }

        $cont = $this->input->get_request_header('Content-Type', TRUE);
        $inputs = $this->input;
        if (strcasecmp($cont, "application/json") == 0) {
            $raw = $GLOBALS['HTTP_RAW_POST_DATA'];
            $inputs = json_decode($raw);
        }
        $lng = commonModel::get_obj_value($inputs, 'lng');
        $lat = commonModel::get_obj_value($inputs, 'lat');
        $unionId = commonModel::get_obj_value($inputs, 'unionId');
        $storeId = commonModel::get_obj_value($inputs, 'storeId');
        $tableId = commonModel::get_obj_value($inputs, 'tableId');
        $scores = commonModel::get_obj_value($inputs, 'scores');
        $desc = commonModel::get_obj_value($inputs, 'extraDesc');


        $uri = $_SERVER['REQUEST_URI'];

        //查看这个openid的用户，对于这个店的这个桌子的点评，最近的一次是在什么时候，如果相隔不超过1小时，则拒绝评论
        $userinfo = userModel::findUserByUnionId($unionId);
        $latest = remarkModel::getUserLatestRemark($userinfo->id, $storeId, $tableId);
        $can_add = false;
        if ($latest == NULL) {
            //可以写
            $can_add = true;
        } else {
            //判断时间差是否满足要求，如果时间距离太近，则认为是已经点评过了，不允许再点评
            $latest_time = $latest[0]->remark_time;
            //比较时间
            //$now=date('Y-m-d H:i:s',time());
            $now = strtotime(date('Y-m-d H:i:s', time()));
            $time_pre = strtotime($latest_time);
            $min = ($now - $time_pre) / 60;
            if ($min < 10) {
                $can_add = false;
            } else {
                $can_add = true;
            }
        }

        if ($can_add == true) {
            $result = remarkModel::addUserRemark($userinfo->id, $mypost);
        }
        /**/
        $this->json([
            'token'=>$result->id
        ]);
    }

    /**
     * 统计点评总数
     */
    public function getUesrRemarkStatistics(){
        $met = $this->input->method();
        if (strcasecmp($met, "get") != 0) {
            $this->json(["code" => funCodeConst::NEED_GET_METHOD['code'], "msg" => __FUNCTION__ . ".".funCodeConst::NEED_GET_METHOD['msg']]);
            return;
        }

        $unionId = $this->input->get("unionId");
      

        if ($unionId == NULL  ) {
            $this->json(["code" => funCodeConst::NOT_ENOUGH_PARAM['code'], "msg" => __FUNCTION__ . ".".funCodeConst::NOT_ENOUGH_PARAM['msg']]);
            return;
        }
        
         //check user
        $user = UserModel::findUserByUnionId($unionId);
        if ($user == NULL) {
            $this->json(["code" => funCodeConst::INVALID_USER['code'], "msg" => __FUNCTION__ . ".".funCodeConst::INVALID_USER['msg']]);
            return;
        }
        
        $cnt= remarkModel::getUerRemarkCnt($user->id);
        $this->json([
            'code'=>0,
            'msg'=>'',
            'data'=>['totalCnt'=>$cnt]
        ]);
    }
    
    /**
     * 分页获取点评
     */
    public function getUesrRemark(){
         $met = $this->input->method();
        if (strcasecmp($met, "get") != 0) {
            $this->json(["code" => funCodeConst::NEED_GET_METHOD['code'], "msg" => __FUNCTION__ . ".".funCodeConst::NEED_GET_METHOD['msg']]);
            return;
        }

        $unionId = $this->input->get("unionId");
        $offset = $this->input->get("offset");
        $cnt=$this->input->get("cnt");
        
        if($offset==NULL){
            $offset=0;
        }
        if($cnt==NULL){
            $cnt=5;
        }

        if ($unionId == NULL  ) {
            $this->json(["code" => funCodeConst::NOT_ENOUGH_PARAM['code'], "msg" => __FUNCTION__ . ".".funCodeConst::NOT_ENOUGH_PARAM['msg']]);
            return;
        }
        
         //check user
        $user = UserModel::findUserByUnionId($unionId);
        if ($user == NULL) {
            $this->json(["code" => funCodeConst::INVALID_USER['code'], "msg" => __FUNCTION__ . ".".funCodeConst::INVALID_USER['msg']]);
            return;
        }
        
        $res= remarkModel::getUserRemarkByPages($user->id,$offset,$cnt);
        
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
