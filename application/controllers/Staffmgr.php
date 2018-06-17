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
    
    /**
     * 获取某分店的员工的满意度（来自点评数据）
     */
    public function getStoreStaffSatisfaction(){
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
        $store_id = commonModel::get_obj_value($inputs, 'store_id');
        if ($store_id == NULL) {
            $this->json(["code" => funCodeConst::NOT_ENOUGH_PARAM['code'], "msg" => __FUNCTION__ . "." . funCodeConst::NOT_ENOUGH_PARAM['msg']]);
            return;
        }
        
        $begtime=commonModel::get_obj_value($inputs, 'begtime');
        $endtime=commonModel::get_obj_value($inputs, 'endtime');
        
        if($begtime==NULL){
           $begtime= date('Y-m-d H:i:s',time()-3600*24);
           $endtime=date('Y-m-d H:i:s');
        }
        
        //--获取点评的分数设置情况
        $remarkTemp= remarkModel::getRemarkTemplateInfo();
        $remark_full_scores_info=[];
        foreach($remarkTemp as $rem){
            $remark_full_scores_info[$rem->id]=$rem->level;
        }
        
        $res=storeModel::getStoreStaffSatisfaction($store_id, $begtime, $endtime);
        $ret_data=[]; //实际得分数
        $tmp_full_score=[];//对应的应该的满分数目
        
        if($res!=NULL && count($res)>0){
            foreach($res as $one){ 
                $sc= json_decode($one->scores);//解析里面的每一项的评分
                $tot=0;
                $full_sc=0;
                foreach($sc as $s){ //累加评分情况
                    $tot+=$s->score;
                    if(array_key_exists($s->remarkTempId, $remark_full_scores_info)){
                        $full_sc+=$remark_full_scores_info[$s->remarkTempId];
                    }else{
                        $full_sc=10;//如果找不到，则取满分为10分
                    }
                }
                if(array_key_exists($one->name,$ret_data)==true){
                   $ret_data[$one->name]=$ret_data[$one->name]+$tot;  
                   $tmp_full_score[$one->name]=$tmp_full_score[$one->name]+$full_sc;
                }else{
                    $ret_data[$one->name]=$tot;
                    $tmp_full_score[$one->name]=$full_sc;
                }
            }
        }
        
        $final_data=[];
        foreach($ret_data as $d){
            $final_data[$d->key]=["name"=>$d->key,"get_score"=>$d->value,"full_score"=>$tmp_full_score[$d->key],"ratio"=>$d->value/$tmp_full_score[$d->key]];
        }
        
        $this->json([
            'code'=>0,
            'msg'=>'',
            'data'=>[
                'cnt'=>count($final_data),
                'data'=>$final_data
            ]
        ]);
    }

    
    /**
     * 获取某个分店的员工的投诉信息
     */
    public function getStoreStaffComplain(){
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
        $store_id = commonModel::get_obj_value($inputs, 'store_id');
        if ($store_id == NULL) {
            $this->json(["code" => funCodeConst::NOT_ENOUGH_PARAM['code'], "msg" => __FUNCTION__ . "." . funCodeConst::NOT_ENOUGH_PARAM['msg']]);
            return;
        }
        
        $begtime=commonModel::get_obj_value($inputs, 'begtime');
        $endtime=commonModel::get_obj_value($inputs, 'endtime');
        
        if($begtime==NULL){
           $begtime= date('Y-m-d H:i:s',time()-3600*24);
           $endtime=date('Y-m-d H:i:s');
        }
        
        $res=storeModel::gertStoreStaffComplain($store_id,$begtime,$endtime);
        $this->json([
           'code'=>0,
            'msg'=>'',
            'data'=>[
                'cnt'=>count($res),
                'data'=>$res
            ]
        ]);
    }
}
