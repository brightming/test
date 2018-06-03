<?php

defined('BASEPATH') OR exit('No direct script access allowed');

use QCloud_WeApp_SDK\Constants as Constants;
use QCloud_WeApp_SDK\Model\Setting as settingModel;
use QCloud_WeApp_SDK\Model\Common as commonModel;
use \QCloud_WeApp_SDK\Model\Store as storeModel;
use QCloud_WeApp_SDK\Model\User as userModel;
use QCloud_WeApp_SDK\Model\Remark as remarkModel;

/**
  点评相关

 */
class Setting extends CI_Controller {

    /**
     * post的方式接收用户的lng,lat,unionId,storId
     */
    public function check_remark_setting() {
        $met = $this->input->method();
        if (strcasecmp($met, "post") != 0) {
            $this->json(["code" => 600, "msg" => "check_remark_setting.expected post method"]);
            return;
        }
        $cont=$this->input->get_request_header('Content-Type', TRUE);
        $inputs=$this->input;
        if(strcasecmp($cont, "application/json")==0){
            $raw=$GLOBALS['HTTP_RAW_POST_DATA'];
            $inputs= json_decode($raw);
        }
        $lng = commonModel::get_obj_value($inputs,'lng');
        $lat = commonModel::get_obj_value($inputs,'lat');
        $unionId = commonModel::get_obj_value($inputs,'unionId');
        $storeId = commonModel::get_obj_value($inputs,'storeId');

        if ($lng == NULL || $lat == NULL || $unionId == NULL || $storeId == NULL) {
            $this->json(["code" => -6, "msg" => "check_remark_setting.param not enough"]);
            return;
        }

        //---check if this user is allowed to do it
        $userinfo = userModel::findUserByUnionId($unionId);
        if ($userinfo == NULL) {
            $this->json(["code" => -4, "msg" => "check_remark_setting.user not register in system "]);
            return;
        }
        //---TODO check if this user is allowed
        

        //get all stores 
        $all_stores = storeModel::getAllStores();
        if (count($all_stores) == 0) {
            $this->json(["code" => -2, "msg" => "check_remark_setting.no stores"]);
            return;
        }

        //check if any nearby store 
        $allow_dist = 100;
        $find_nearby = false;
        $store_ids = array();
        foreach ($all_stores as $store) {
            if ($store->id == $storeId) {
                $dist = commonModel::get_distance($lng, $lat, $store->longitude, $store->latitude);
                if ($dist <= $allow_dist) {
                    $find_nearby = true; //find any one
                }
                break;
            }
        }

        if ($find_nearby == false) {
            $this->json(["code" => -2, "msg" => "check_remark_setting.no nearby store"]);
            return;
        }


        //check time
        $curdate = date("Y/m/d");
        $curtime = date('H:i:s', time());
        $type = 1;
        $setss = settingModel::getAllSettingByType($type);
        $time_allow = false;
        $rng_str_all = "";
        $rng_for_this_store = "";
        $this_store_has_set = false;
        $get_beg_date = "";
        $get_end_date = "";
        if (count($setss) == 0) {
            $this->json(["code" => -1, "msg" => "check_remark_setting.no activity"]);
            return;
        }
        foreach ($setss as $st) {
            $beg_date = $st->valid_date_beg;
            $end_date = $st->valid_date_end;
            $rngs = $st->time_rngs;
            $one_store_id = $st->store_id;

            if ($one_store_id != -1 && $one_store_id != $storeId) {
                continue;
            }
            if ($one_store_id == $storeId) {//找到唯一对应的店面，直接处理
                $this_store_has_set = true;
                $rng_for_this_store = $rngs;
                $get_beg_date = $beg_date;
                $get_end_date = $end_date;
                //check date first
                if (commonModel::between_date($beg_date, $end_date, $curdate)) {
                    if ($rngs != "") {
                        //split 
                        $time_allow = commonModel::between_time_ranges($rngs, $curtime);
                    } else {
                        //if not set,allow all
                        $time_allow = true;
                        break;
                    }
                }
                break;
            } else if ($one_store_id == -1) {//对于适用于全部门店的，需要逐个进行处理
                $rng_str_all = $rngs;
                if (commonModel::between_date($beg_date, $end_date, $curdate)) {
                    if ($rngs != "") {
                        //split 
                        if ($time_allow == false) {
                            $time_allow = commonModel::between_time_ranges($rngs, $curtime);
                            if ($time_allow == true) {
                                $get_beg_date = $beg_date;
                                $get_end_date = $end_date;
                            }
                        }
                    } else {
                        //if not set,allow all
                        $time_allow = true;
                        $get_beg_date = $beg_date;
                        $get_end_date = $end_date;
                    }
                }
            }
        }
        if ($time_allow == false) {
            $this->json(["code" => -3, "msg" => "check_remark_setting.not in valid time"]);
            return;
        }

        //--whether this user has done it before within this time period ---//
        $rec = remarkModel::getUserLatestRemark($userinfo->id, $storeId);
        if ($rec != NULL) {
            $rectime = $rec->remark_time;
            $arr = explode(" ", $rectime);
            if (count($arr) != 2) {
                
            } else {
                $recdate = $arr[0];
                $arr2 = $arr[1];
                $rectime2 = strtotime($arr2);
                if ($this_store_has_set == true) {//该门店有专门的设置
                    if (commonModel::is_today($recdate) && commonModel::same_with_curtime_range($rng_for_this_store, $rectime2) == true) {
                        //当前时间段已经点评过一次了
                        $this->json(["code" => -5, "msg" => "check_remark_setting.has dobe it "]);
                        return;
                    }
                }else{
                    if (commonModel::is_today($recdate) && commonModel::same_with_curtime_range($rng_str_all, $rectime2) == true) {
                        //当前时间段已经点评过一次了
                        $this->json(["code" => -5, "msg" => "check_remark_setting.has dobe it "]);
                        return;
                    }
                }
            }
        }

        $this->json(["code" => 0, "msg" => "check_remark_setting.go"]);
        return;
    }

    
    /**
     * 抽现金的设置
     * @return type
     */
    public function check_drawcash_setting(){
        $met = $this->input->method();
        if (strcasecmp($met, "post") != 0) {
            $this->json(["code" => 600, "msg" => "check_drawcash_setting.expected post method"]);
            return;
        }
        $cont=$this->input->get_request_header('Content-Type', TRUE);
        $inputs=$this->input;
        if(strcasecmp($cont, "application/json")==0){
            $raw=$GLOBALS['HTTP_RAW_POST_DATA'];
            $inputs= json_decode($raw);
        }
        $lng = commonModel::get_obj_value($inputs,'lng');
        $lat = commonModel::get_obj_value($inputs,'lat');
        $unionId = commonModel::get_obj_value($inputs,'unionId');
        $token = commonModel::get_obj_value($inputs,'token');
        $storeId = commonModel::get_obj_value($inputs,'storeId');
        
        
        //--check lng lat and storeid
        //get all stores 
        $all_stores = storeModel::getAllStores();
        if (count($all_stores) == 0) {
            $this->json(["code" => -2, "msg" => "check_drawcash_setting.no stores"]);
            return;
        }

        //check if any nearby store 
        $allow_dist = 100;
        $find_nearby = false;
        foreach ($all_stores as $store) {
            if ($store->id == $storeId) {
                $dist = commonModel::get_distance($lng, $lat, $store->longitude, $store->latitude);
                if ($dist <= $allow_dist ) {
                    $find_nearby = true; //find any one
                }
                break;
            }
        }

        if ($find_nearby == false) {
            $this->json(["code" => -2, "msg" => "check_drawcash_setting.no nearby store"]);
            return;
        }
        
        //----check token --//
        $setinfo=commonModel::getDrawCashSetting($storeId);
        if($setinfo==NULL){
            $this->json(["code" => -2, "msg" => "check_drawcash_setting.not start yet"]);
            return;
        }
        if(strcmp($token, commonModel::get_obj_value($setinfo, "token"))!=0){
            $this->json(["code" => -4, "msg" => "check_drawcash_setting.invalid token!"]);
            return;
        }
        
        //---is within valid time range---//
        $in_time=commonModel::is_curtime_in_time_range($rngs_str);
        if($in_time==false){
            $this->json(["code" => -1, "msg" => "check_drawcash_setting.activity finished!"]);
            return;
        }
        
        //---user has do it before---//

    }
    
    /**
     * 获取抽奖的设置情况
     */
    public function getVoucherSetting(){
        
    }
}
