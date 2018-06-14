<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \QCloud_WeApp_SDK\Model\Remark as remarkModel;
use \QCloud_WeApp_SDK\Model\User as userModel;
use QCloud_WeApp_SDK\Model\Store as storeModel;
use QCloud_WeApp_SDK\FunctionCodeConstants as funCodeConst;
use QCloud_WeApp_SDK\Model\Common as commonModel;

/**
 * Description of Stuffmgr
 * 领班的小程序
 * @author gumh
 */
class Stuffmgr {
    //put your code here

    /**
     * 获取某个分店的员工
     */
    public function getAllStaffOfStore() {
        $met = $this->input->method();
        if (strcasecmp($met, "get") != 0) {
            $this->json(["code" => funCodeConst::NEED_GET_METHOD['code'], "msg" => __FUNCTION__ . "." . funCodeConst::NEED_GET_METHOD['msg']]);
            return;
        }

        $cont = $this->input->get_request_header('Content-Type', TRUE);
        $inputs = $this->input;
        if (strcasecmp($cont, "application/json") == 0) {
            $raw = $GLOBALS['HTTP_RAW_POST_DATA'];
            $inputs = json_decode($raw);
        }
        $storeId = commonModel::get_obj_value($inputs, 'storeId');
        if ($storeId == NULL) {
            $this->json(["code" => funCodeConst::NOT_ENOUGH_PARAM['code'], "msg" => __FUNCTION__ . "." . funCodeConst::NOT_ENOUGH_PARAM['msg']]);
            return;
        }

        $res = storeModel::getStoreStaff($storeId);

        $this->json([
            'code' => 0,
            'msg' => '',
            'data' => [
                'cnt' => count($res),
                'data' => $res
            ]
        ]);

        return;
    }

    /**
     * 获取指定分店的桌子信息
     */
    public function getStoreTables() {
        $met = $this->input->method();
        if (strcasecmp($met, "get") != 0) {
            $this->json(["code" => funCodeConst::NEED_GET_METHOD['code'], "msg" => __FUNCTION__ . "." . funCodeConst::NEED_GET_METHOD['msg']]);
            return;
        }

        $cont = $this->input->get_request_header('Content-Type', TRUE);
        $inputs = $this->input;
        if (strcasecmp($cont, "application/json") == 0) {
            $raw = $GLOBALS['HTTP_RAW_POST_DATA'];
            $inputs = json_decode($raw);
        }
        $storeId = commonModel::get_obj_value($inputs, 'storeId');
        if ($storeId == NULL) {
            $this->json(["code" => funCodeConst::NOT_ENOUGH_PARAM['code'], "msg" => __FUNCTION__ . "." . funCodeConst::NOT_ENOUGH_PARAM['msg']]);
            return;
        }

        $res = storeModel::getStoreTables($storeId);

        $this->json([
            'code' => 0,
            'msg' => '',
            'data' => [
                'cnt' => count($res),
                'data' => $res
            ]
        ]);

        return;
    }

    /**
     * 分配桌子给指定服务员管理
     */
    public function assignTableToStaff() {
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
        $storeId = commonModel::get_obj_value($inputs, 'storeId');
        $staff_id= commonModel::get_obj_value($inputs, 'staffId');
        $table_ids= commonModel::get_obj_value($inputs, 'tableIds');
        
        if($staff_id==NULL || $table_ids==NULL){
            $this->json(["code" => funCodeConst::NEED_POST_METHOD['code'], "msg" => __FUNCTION__ . "." . funCodeConst::NEED_POST_METHOD['msg']]);
            return;
        }
        
        if(count($table_ids)==0){
            $this->json(["code" => funCodeConst::NEED_POST_METHOD['code'], "msg" => __FUNCTION__ . "." . funCodeConst::NEED_POST_METHOD['msg'].' select some table!']);
            return;
        }
        
        //去重复
        array_unique($table_ids);
        
        $tids= implode(",", $table_ids);
        storeModel::removeStaffTableRelation($tids);
        $cnt=storeModel::saveStaffTableRelation($staff_id, $table_ids);
        
        $this->json([
           'code'=>0,
            'msg'=>'',
            'data'=>[
                'cnt'=>$cnt
            ]
        ]);
    }
    
    /**
     * 获取指定服务员管理的桌子
     */
    public function getTableOfStaff(){
        $met = $this->input->method();
        if (strcasecmp($met, "get") != 0) {
            $this->json(["code" => funCodeConst::NEED_GET_METHOD['code'], "msg" => __FUNCTION__ . "." . funCodeConst::NEED_GET_METHOD['msg']]);
            return;
        }

        $cont = $this->input->get_request_header('Content-Type', TRUE);
        $inputs = $this->input;
        if (strcasecmp($cont, "application/json") == 0) {
            $raw = $GLOBALS['HTTP_RAW_POST_DATA'];
            $inputs = json_decode($raw);
        }
        $staff_id = commonModel::get_obj_value($inputs, 'staffId');
        if ($staff_id == NULL) {
            $this->json(["code" => funCodeConst::NOT_ENOUGH_PARAM['code'], "msg" => __FUNCTION__ . "." . funCodeConst::NOT_ENOUGH_PARAM['msg']]);
            return;
        }
        
        $tables= storeModel::getTableOfStaff($staff_id);
        $this->json([
           'code'=>0,
            'msg'=>'',
            'data'=>[
                'cnt'=>count($tables),
                'data'=>$tables
            ]
        ]);
    }

}
