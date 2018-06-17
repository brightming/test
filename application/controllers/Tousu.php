<?php

defined('BASEPATH') OR exit('No direct script access allowed');

use QCloud_WeApp_SDK\Constants as Constants;
use \QCloud_WeApp_SDK\Model\Tousu as TousuModel;
use \QCloud_WeApp_SDK\Model\User as userModel;
use \QCloud_WeApp_SDK\Model\FileRecord as fileModel;
use QCloud_WeApp_SDK\FunctionCodeConstants as funCodeConst;

/**
  投诉相关

 */
class Tousu extends CI_Controller {

    public function getTousuTemplate() {
        //获取投诉模板
        $result = TousuModel::getTousuTemplateInfo();
        $this->json(['data' => $result]);
    }

    public function addTousuNoPict() {

        $rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
        $mypost = json_decode($rws_post);
        $openId = $mypost->openId;

        $userinfo = userModel::findUserByOpenId($openId);
        $res = TousuModel::addUserTousu($userinfo->id, $mypost);

        if ($res == NULL) {
            $this->json(['code' => -1, 'desc' => 'fail']);
        } else {
            $this->json(['code' => 1, 'desc' => 'success', 'tousuid' => $res->id]);
        }
    }

    /**
     * 带图片投诉
     */
    public function addTousu() {

        //$uri = $_SERVER['REQUEST_URI']; 
        //$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];

        $met = $this->input->method();
        if (strcasecmp($met, "post") != 0) {
            $this->json(["code" => 600, "msg" => __FUNCTION__ . ".expected post method"]);
            return;
        }

        $unionId = commonModel::get_obj_value($inputs, 'unionId');
        $tableId = commonModel::get_obj_value($inputs, 'tableId');
        $storeId = commonModel::get_obj_value($inputs, 'storeId');
        $tousuIds = commonModel::get_obj_value($inputs, 'tousuIds');
        $extraDesc = commonModel::get_obj_value($inputs, 'extraDesc');

        $pictureCnt = commonModel::get_obj_value($inputs, 'pictureCnt');
        $pictureSeq = commonModel::get_obj_value($inputs, 'pictureSeq');
        $name = commonModel::get_obj_value($inputs, 'name');
        $token = commonModel::get_obj_value($inputs, 'token');


        if ($unionId == NULL || $storeId == NULL || $tousuIds == NULL) {
            $this->json(["code" => funCodeConst::NOT_ENOUGH_PARAM['code'], "msg" => __FUNCTION__ . "." . funCodeConst::NOT_ENOUGH_PARAM['msg']]);
            return;
        }

        $userinfo = userModel::findUserByUnionId($unionId);
        if ($userinfo == NULL) {
            $this->json(["code" => funCodeConst::INVALID_USER['code'], "msg" => __FUNCTION__ . "." . funCodeConst::INVALID_USER['msg']]);
            return;
        }

       $staff= \QCloud_WeApp_SDK\Model\Store::getStuffByStoreAndTable($storeId,$tableId);
       if($staff==NULL ){
            //该桌子无人管理
            $staff=(object)["id"=>-1,"name"=>'nobody'];
        }

        if ($token == NULL) {
            if ($pictureSeq != NULL && $pictureSeq > 0) {
                $this->json(["code" => funCodeConst::NOT_ENOUGH_PARAM['code'], "msg" => __FUNCTION__ . "." . funCodeConst::NOT_ENOUGH_PARAM['msg']]);
                return;
            }

            $res = TousuModel::addUserTousu2($userinfo->id, $extraDesc, $tableId, $storeId, 1, '', $tousuIds,$staff);

            if ($res == NULL) {
                $this->json(['code' => -1, 'msg' => 'fail']);
                return;
            }

            $token = $res->id;
        }

        if ($pictureCnt > 0 && $name != NULL) {
            $file = $_FILES['name']; // 
            $tmpPath = $file['tmp_name'];
            $size = $file['size'];
            if ($size > 0) {
                $dir = './uploads/';
                //按照年/月/日创建文件夹
                $file_path = "$dir" . '/' . date("Y") . '/' . date("m") . '/' . date("d");
                $dir_ok = true;
                if (!is_dir($file_path)) {
                    if (mkdir($file_path, 755, true)) {
                        
                    } else {
                        $dir_ok = false;
                    }
                }
                if ($dir_ok == false) {
                    $this->json(['code' => -8, 'msg' => 'folder fail']);
                    return;
                }
                $originalName = $file['name'];
                $arr = explode(".", $originalName);
                $dest_name = "tousu-" . $token . '-1.' . $arr[count($arr) - 1];
                $destination = $file_path . '/' . $dest_name;
                if (move_uploaded_file($tmpPath, $destination)) {
                    $ok = true;
                }
                //-----save to file record---//
                $res2 = fileModel::storeFileRecord($user->id, 1, $token, $destination, $file['size']);
            }
        }

        $this->json([
            'code' => 1,
            'msg' => '',
            'data' => [
                'token' => $token
            ]
        ]);
    }

    public function AddTousuDingdan() {
        
        
        $met = $this->input->method();
        if (strcasecmp($met, "post") != 0) {
            $this->json(["code" => 600, "msg" => __FUNCTION__ . ".expected post method"]);
            return;
        }

        $unionId = commonModel::get_obj_value($inputs, 'unionId');
        $tableId = commonModel::get_obj_value($inputs, 'tableId');
        $storeId = commonModel::get_obj_value($inputs, 'storeId');
        $cellphone = commonModel::get_obj_value($inputs, 'cellphone');
        $tousuToken = commonModel::get_obj_value($inputs, 'tousuToken');

        $pictureCnt = commonModel::get_obj_value($inputs, 'pictureCnt');
        $pictureSeq = commonModel::get_obj_value($inputs, 'pictureSeq');
        $name = commonModel::get_obj_value($inputs, 'name');
        $token = commonModel::get_obj_value($inputs, 'token');


        if ($unionId == NULL || $cellphone == NULL || $tousuToken == NULL || $pictureCnt!=NULL && $pictureCnt<0 || $pictureSeq!=NULL && $pictureSeq<0) {
            $this->json(["code" => funCodeConst::NOT_ENOUGH_PARAM['code'], "msg" => __FUNCTION__ . "." . funCodeConst::NOT_ENOUGH_PARAM['msg']]);
            return;
        }

        $userinfo = userModel::findUserByUnionId($unionId);
        if ($userinfo == NULL) {
            $this->json(["code" => funCodeConst::INVALID_USER['code'], "msg" => __FUNCTION__ . "." . funCodeConst::INVALID_USER['msg']]);
            return;
        }


        if ($token == NULL) {
            if ($pictureSeq != NULL && $pictureSeq > 0) {
                $this->json(["code" => funCodeConst::NOT_ENOUGH_PARAM['code'], "msg" => __FUNCTION__ . "." . funCodeConst::NOT_ENOUGH_PARAM['msg']]);
                return;
            }
            $res = TousuModel::addTousuDingdan($tousuToken, $cellphone, $userinfo->id, '', $pictureCnt);
            if ($res == NULL) {
                $this->json(['code' => -1, 'msg' => 'fail']);
                return;
            }

            $token = $res->id;
        }

        if ($pictureCnt!=NULL  && $name != NULL) {
            $file = $_FILES['name']; // 
            $tmpPath = $file['tmp_name'];
            $size = $file['size'];
            if ($size > 0) {
                $dir = './uploads/';
                //按照年/月/日创建文件夹
                $file_path = "$dir" . '/' . date("Y") . '/' . date("m") . '/' . date("d");
                $dir_ok = true;
                if (!is_dir($file_path)) {
                    if (mkdir($file_path, 755, true)) {
                        
                    } else {
                        $dir_ok = false;
                    }
                }
                if ($dir_ok == false) {
                    $this->json(['code' => -8, 'msg' => 'folder fail']);
                    return;
                }
                $originalName = $file['name'];
                $arr = explode(".", $originalName);
                $dest_name = "tousudingdan-" . $token . '-'.$pictureSeq.'.' . $arr[count($arr) - 1];
                $destination = $file_path . '/' . $dest_name;
                if (move_uploaded_file($tmpPath, $destination)) {
                    $ok = true;
                }
                //-----save to file record---//
                $res2 = fileModel::storeFileRecord($userinfo->id,2, $token, $destination, $file['size']);
            }
        }

        $this->json([
            'code' => 1,
            'msg' => '',
            'data' => [
                'token' => $token
            ]
        ]);

    }

}
