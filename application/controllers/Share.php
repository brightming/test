<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use QCloud_WeApp_SDK\Model\Share as shareModel;
use \QCloud_WeApp_SDK\Model\User as UserModel;
use \QCloud_WeApp_SDK\Model\FileRecord as fileModel;
use \QCloud_WeApp_SDK\Model\Store as storeModel;
use QCloud_WeApp_SDK\Model\Coupon as couponModel;
use QCloud_WeApp_SDK\FunctionCodeConstants as funCodeConst;

/**
 * Description of Share
 * 分享相关的类
 * @author gumh
 */
class Share extends CI_Controller {

    //put your code here

    public function getShareTemplate() {
        $met = $this->input->method();
        if (strcasecmp($met, "post") != 0) {
            $this->json(["code" => funCodeConst::NEED_POST_METHOD['code'], "msg" => __FUNCTION__ . ".".funCodeConst::NEED_POST_METHOD['msg']]);
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
        $offset = commonModel::get_obj_value($inputs, 'offset');
        $cnt = commonModel::get_obj_value($inputs, 'cnt');


        if ($offset == NULL) {
            $offset = 0;
        }
        if ($cnt == NULL) {
            $cnt = 10;
        }

        $res = shareModel::getShareTempsByPages($offset, $cnt);
        $data = array();
        foreach ($res as $one) {
            $d = ['pictUrl' => $one->pict_url, 'content' => $one->content];
            $data[] = (object) $d;
        }

        $this->json(["code" => 0, 'msg' => 'getShareTemps.offset=' . $offset . ',cnt=' . $cnt, 'data' => $data]);
    }

    /**
     * 进行模板分享
     * @return type
     */
    public function addTemplateShare() {
        $met = $this->input->method();
        if (strcasecmp($met, "post") != 0) {
            $this->json(["code" => 600, "msg" => __FUNCTION__ . ".expected post method"]);
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
        $templateId = commonModel::get_obj_value($inputs, 'templateId');

        if ($templateId == NULL || $unionId == NULL || $storeId==NULL) {
            $this->json(["code" => -1, "msg" => __FUNCTION__ . ".not enough params"]);
            return;
        }

        $user = UserModel::findUserByUnionId($unionId);
        if ($user == NULL) {
            $this->json(["code" => -2, "msg" => __FUNCTION__ . ".user not found "]);
            return;
        }

        //检查分享模板是否有效
        $sharetmp = shareModel::getShareTempById($templateId);
        if ($sharetmp == NULL) {
            $this->json(["code" => -3, "msg" => __FUNCTION__ . ". share template not found "]);
            return;
        }

        $data = ['customer_id' => $user->id, 'store_id' => $storeId, 'share_temp_id' => $templateId,
            'share_type' => 1, 'picture_cnt' => 1, 'first_picture_url' => $sharetmp->pict_url, 'comment' => $sharetmp->content];

        $res = shareModel::addShareRec($data);
        if ($res == 0) {
            $this->json(["code" => -4, "msg" => 'add rec fail']);
            return;
        }

        $this->json(["code" => 0, "msg" => 'ok']);
    }

    /**
     * 用户自定义分享
     */
    public function addUserDefineShare() {
        /*
          lng	用户手机获取经度	如果用户需提交多张图片，这些信息是在用户第一次提交的时候提供。第二次及后面的提交则不再需要
          lat	用户手机获取纬度
          unionID	用户unionid
          storeId	店面id
          extraDesc	用户额外输入的文字描述，不要超过200个字符。
          pictureCnt	上传的图片数量	若有图片的话，则需要提供此类信息。多张图片提交，则需多次提供
          pictureSeq	当前上传图片的序号。从0开始。
          name	图片对应的名称
          token	若有多张图片，服务端会返回一个token给客户端，客户端在传下一张图片的时候，需要带上这个token
         */
        $met = $this->input->method();
        if (strcasecmp($met, "post") != 0) {
            $this->json(["code" => 600, "msg" => __FUNCTION__ . ".expected post method"]);
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
        $extraDesc = commonModel::get_obj_value($inputs, 'extraDesc');

        $pictureCnt = commonModel::get_obj_value($inputs, 'pictureCnt');
        $pictureSeq = commonModel::get_obj_value($inputs, 'pictureSeq');
        $name = commonModel::get_obj_value($inputs, 'name');
        $token = commonModel::get_obj_value($inputs, 'token');


        //check user
        $user = userModel::findUserByUnionId($unionId);
        if ($user == NULL) {
            $this->json(["code" => -2, "msg" => __FUNCTION__ . ".user not found "]);
            return;
        }

        if ($token != NULL) {
            //查询是否存在
            $shareinfo = shareModel::getShareById($token);
            if ($shareInfo == NULL) {
                $this->json(["code" => -3, "msg" => __FUNCTION__ . ".token is invalid "]);
                return;
            }
        } else if ($pictureCnt > 0 && $pictureSeq != NULL && $pictureSeq > 0) {
            $this->json(["code" => -4, "msg" => __FUNCTION__ . ".expected token! "]);
            return;
        }


        //---TODO  事务----//
        if ($token == NULL) {
            $storeInfo = storeModel::getStoreById($storeId);
            if ($storeInfo == NULL) {
                $this->json(["code" => -5, "msg" => __FUNCTION__ . ". invalid storeId! "]);
                return;
            }
            $nowtime = date('Y-m-d H:i:s', time());
            $data = ['customer_id' => $user->id, 'store_id' => $storeId, 'table_id' => $tableId, 'share_temp_id' => 0,
                'share_type' => 2, 'picture_cnt' => $pictureCnt, 'first_picture_url' => '', 'comment' => $extraDesc, 'create_time' => $nowtime, 'store_name' => $storeInfo->name];

            $cnt = shareModel::addShareRec($data);
            if ($cnt <= 0) {
                $this->json(["code" => -6, "msg" => __FUNCTION__ . ".save fail! "]);
                return;
            }

            ///拿回id作为token
            $getrec = shareModel::getUserShareByTimestrAndCustomerId($user->id, $nowtime);
            if ($getrec == NULL) {
                $this->json(["code" => -7, "msg" => __FUNCTION__ . ".save fail 2! "]);
                return;
            }

            $token = $getrec->id;
        }

        if ($pictureCnt > 0 && $name != NULL) {
            $file = $_FILES['name']; // 
            $tmpPath = $file['tmp_name'];
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
            $dest_name = "share-" . $token . '-' . $pictureSeq . $arr[count($arr) - 1];
            $destination = $file_path . '/' . $dest_name;
            if (move_uploaded_file($tmpPath, $destination)) {
                $ok = true;
            }
            //-----save to file record---//
            $res2 = fileModel::storeFileRecord($user->id, 3, $token, $destination, $file['size']);

            //--更新记录表的第一张图片的信息---//
            if ($pictureSeq == 0) {
                $updates = ["first_picture_url" => $destination];
                shareModel::updateUserShareById($token, $updates);
            }
        }

        $this->json(['code' => 0, 'msg' => 'ok', 'token' => $token]);
    }

    /**
     * 用户获取券。
     * 参数：
     * 分享类型
     * 分享id
     * 用户unionid
     */
    public function takeCoupon() {
        $met = $this->input->method();
        if (strcasecmp($met, "post") != 0) {
            $this->json(["code" => 600, "msg" => __FUNCTION__ . ".expected post method"]);
            return;
        }

        $cont = $this->input->get_request_header('Content-Type', TRUE);
        $inputs = $this->input;
        if (strcasecmp($cont, "application/json") == 0) {
            $raw = $GLOBALS['HTTP_RAW_POST_DATA'];
            $inputs = json_decode($raw);
        }
        $type = commonModel::get_obj_value($inputs, 'type');
        $shareId = commonModel::get_obj_value($inputs, 'shareId');
        $unionId = commonModel::get_obj_value($inputs, 'unionId');

        
        //check shareid 
        if ($type == 1) {//模板分享
            $shareinfo = shareModel::getShareTempById($shareId);
            if ($shareinfo == NULL) {
                $this->json(["code" => -1, "msg" => __FUNCTION__ . ".invalid template shareid"]);
                return;
            }
        } else if ($type == 2) {//自定义分享
            $shareinfo = shareModel::getShareById($shareId);
            if ($shareinfo == NULL) {
                $this->json(["code" => -2, "msg" => __FUNCTION__ . ".invalid user define shareid"]);
                return;
            }
        }

        //check unionId
        $user = UserModel::findUserByUnionId($unionId);
        if ($user == NULL) {
            $this->json(["code" => -3, "msg" => __FUNCTION__ . ".invalid user define shareid"]);
            return;
        }

        //是否领取自己分享的券
        if ($user->id == $shareinfo->customer_id) {
            $this->json(["code" => -4, "msg" => __FUNCTION__ . ".not allow to take coupon shared by himself"]);
            return;
        }

        //创建优惠券
        $get_from_where = 1;
        $create_reason = 2; //分享产生的
        $coupon_type = 1;//满多少减多少
        $coupon = shareModel::makeCoupon($shareinfo->id, $shareinfo->customer_id, $user->id, $create_reason, $coupon_type, $get_from_where);

        //保存
        shareModel::saveCoupon($coupon);

        $this->json(['code' => 0, 'data' => $coupon, 'msg' => 'success']);
    }

    /**
     * 分页读取用户的分享数据
     */
    public function getMyShareBrief() {
        $met = $this->input->method();
        if (strcasecmp($met, "get") != 0) {
            $this->json(["code" => 600, "msg" => __FUNCTION__ . ".expected get method"]);
            return;
        }

        $unionId = $this->input->get("unionId");
        $offset = $this->input->get("offset");
        $cnt = $this->input->get("cnt");
        $totalCnt = $this->input->get("totalCnt");

        //check user
        $user = UserModel::findUserByUnionId($unionId);
        if ($user == NULL) {
            $this->json(["code" => -1, "msg" => __FUNCTION__ . ".invalid user"]);
            return;
        }
        if ($offset == NULL) {
            $offset = 0;
        }
        if ($cnt == NULL) {
            $cnt = 5;
        }
        //if has totalCnt
        if ($totalCnt == NULL) {
            $totalCnt = shareModel::countUesrShareRecNumberByUserId($user->id);
        }
        if ($totalCnt == 0) {
            //直接返回了
            $data = ['totalCnt' => 0, 'cnt' => 0, 'items' => []];
            $this->json(['code' => 0, 'data' => $data, 'msg' => '']);
            return;
        }

        //分页获取简要信息
        $orderby = ' order by create_time desc';
        $datas = shareModel::getUserShareByPage($user->id, $offset, $cnt, $orderby);
        if (count($datas) == 0) {
            $data = ['totalCnt' => $totalCnt, 'cnt' => 0, 'items' => []];
            $this->json(['code' => 0, 'data' => $data, 'msg' => '']);
        } else {
            $items = [];
            foreach ($datas as $one) {
                $conditions = "related_id=" . $one->id;
                $takeCnt = shareModel::countCouponByConditions($conditions); //查询此分享被多少人领取
                $item = ['id' => $one->id, 'storeName' => $one->store_name, 'createTime' => $one->create_time, 'extraDesc' => $one->comment, 'pictureUrl' => $one->first_picture_url, 'takeCnt' => $takeCnt];
                array_push($items, (object) $item);
            }
            $data = ['totalCnt' => $totalCnt, 'cnt' => count($datas), 'items' => $items];
            $this->json(['code' => 0, 'data' => $data, 'msg' => '']);
        }
    }

    /**
     * 获取指定的分享的详情
     */
    public function getShareDetail() {
        $met = $this->input->method();
        if (strcasecmp($met, "get") != 0) {
            $this->json(["code" => 600, "msg" => __FUNCTION__ . ".expected get method"]);
            return;
        }

        $unionId = $this->input->get("unionId");
        $id = $this->input->get("id");

        if ($unionId == NULL || $id == NULL) {
            $this->json(["code" => -1, "msg" => __FUNCTION__ . ".not enough params"]);
            return;
        }

        //check user
        $user = UserModel::findUserByUnionId($unionId);
        if ($user == NULL) {
            $this->json(["code" => -2, "msg" => __FUNCTION__ . ".invalid user"]);
            return;
        }

        //--get share rec--//
        $share = shareModel::getShareById($id);
        if ($share == NULL) {
            $this->json(["code" => -3, "msg" => __FUNCTION__ . ".invalid share id"]);
            return;
        }

        //---获取分享的图片信息---//
        $files = [];
        if ($share->picture_cnt > 0) {
            $filerecs = shareModel::getSharePictureListByShareId($share->id);
            if (count($filerecs) > 0) {
                foreach ($filerecs as $one) {
                    $a = ['url' => $one->name];
                    array_push($files, (object) $a);
                }
            }
        }

        //对应此分享，优惠券领取数量
        $conditions = "related_id=" . $share->id . " and create_reason=2";
        $takeCnt = shareModel::countCouponByConditions($conditions);
        
        //具体的信息,连通返现的信息
        $details= shareModel::getCouponDetailByShareid($share->id, true);
        
        $this-json([
            'storeName'=>$share->store_name,
            'createTime'=>$share->create_time,
            'extraDesc'=>$share->comment,
            'pictureUrls'=>$files,
            'takeCnt'=>$takeCnt,
            'takeDetails'=>$details      
        ]);
    }

    /**
     * 获取返现的统计信息
     */
    public function getUserCashbackStaticsData  (){
         $met = $this->input->method();
        if (strcasecmp($met, "get") != 0) {
            $this->json(["code" => 600, "msg" => __FUNCTION__ . ".expected get method"]);
            return;
        }
         $unionId = $this->input->get("unionId");
         if ($unionId == NULL ) {
            $this->json(["code" => funCodeConst::NOT_ENOUGH_PARAM['code'], "msg" => __FUNCTION__ . ".".funCodeConst::NOT_ENOUGH_PARAM['msg']]);
            return;
        }
        
         //check user
        $user = UserModel::findUserByUnionId($unionId);
        if ($user == NULL) {
            $this->json(["code" => funCodeConst::INVALID_USER['code'], "msg" => __FUNCTION__ . ".".funCodeConst::INVALID_USER['msg']]);
            return;
        }
        
        //总的统计信息
        $statisinfo= shareModel::getTotalCashbackByCustomer($user->id);
        if($statisinfo==NULL || $statisinfo->cnt==0){
            //无返现
            $this->json([
                'code'=>0,
                'msg'=>'',
                'data'=>['totalRebate'=>0,'rebateCnt'=>0]
            ]);
            return;
        }else{
            $this->json([
                'code'=>0,
                'msg'=>'',
                'data'=>['totalCashback'=>$statisinfo->total_cashback,'cnt'=>$statisinfo->cnt]
            ]);
        }
    }
    
    /**
     * 分页获取用户的返现记录
     */
    public function getMyRebate (){
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

        if ($unionId == NULL ) {
            $this->json(["code" => funCodeConst::NOT_ENOUGH_PARAM['code'], "msg" => __FUNCTION__ . ".".funCodeConst::NOT_ENOUGH_PARAM['msg']]);
            return;
        }
        
         //check user
        $user = UserModel::findUserByUnionId($unionId);
        if ($user == NULL) {
            $this->json(["code" => funCodeConst::INVALID_USER['code'], "msg" => __FUNCTION__ . ".".funCodeConst::INVALID_USER['msg']]);
            return;
        }
        
        /*
        //总的统计信息----此操作不需要每次都获取
        $statisinfo= shareModel::getTotalCashbackByCustomer($user->id);
        if($statisinfo==NULL || $statisinfo->cnt==0){
            //无返现
            $this->json([
                'code'=>0,
                'msg'=>'',
                'data'=>['totalRebate'=>0,'rebateCnt'=>0,"details"=>[]]
            ]);
            return;
        }
         */
         
        
        //进一步分页获取详情
        $details= shareModel::getCashbackDataByCustomerByPage($user->id, $offset, $cnt);
        if($details==NULL || count($details)==0){
            $this->json([
                'code'=>0,
                'msg'=>'',
                'data'=>['totalRebate'=>0,'rebateCnt'=>0,'cnt'=>0,"details"=>[]]
            ]);
        }else{
            $items=[];
            foreach($details as $deta){
                $one=['rebateTime'=>$deta->create_time,'nickname'=>$deta->name,'rebate'=>$deta->cashback,'storeName'=>''];
                array_push($items,(object)$one);
            }
            $this->json([
                'code'=>0,
                'msg'=>'',
                'data'=>['totalRebate'=>0,'rebateCnt'=>0,'cnt'=>count($details),"details"=>$items]
            ]);
        }
        
    }
    
    /**
     * 获取用户优惠券的统计信息
     */
    public function getUserCouponStatistic (){
        $met = $this->input->method();
        if (strcasecmp($met, "get") != 0) {
            $this->json(["code" => funCodeConst::NEED_GET_METHOD['code'], "msg" => __FUNCTION__ . ".".funCodeConst::NEED_GET_METHOD['msg']]);
            return;
        }

        $unionId = $this->input->get("unionId");
        $type=$this->input->get("type");
        
      

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
        
        //组装条件
        $conditions="customer_id=".$user->id;
        if($type==NULL || strcasecmp($type, "all")){
        }else if(strcasecmp($type, "valid")){
            $conditions=$conditions." and is_used=0 and expired_time>='".date("Y-m-d H:i:s",time())."'";
        }else if(strcasecmp($type, "used")){
            $conditions=$conditions." and is_used=1 ";
        }else if(strcasecmp($type, "expired")){
            $conditions=$conditions." and is_used=0 and expired_time<'".date("Y-m-d H:i:s",time())."'";
        }
        
        $stat= shareModel::countCouponByConditions($conditions);
        
        $this->json(
         [
            'code'=>0,
             'msg'=>'',
             'data'=>[
                 'totalCnt'=>$stat
                ]
         ]   
        );
    }
    
     /**
     * 
     */
    public function getUserCouponList (){
         $met = $this->input->method();
        if (strcasecmp($met, "get") != 0) {
            $this->json(["code" => funCodeConst::NEED_GET_METHOD['code'], "msg" => __FUNCTION__ . ".".funCodeConst::NEED_GET_METHOD['msg']]);
            return;
        }

        $unionId = $this->input->get("unionId");
        $offset = $this->input->get("offset");
        $cnt=$this->input->get("cnt");
        $type=$this->input->get("type");
        
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
        
        $conditions="customer_id=".$user->id;
        if($type==NULL || strcasecmp($type, "all")){
        }else if(strcasecmp($type, "valid")){
            $conditions=$conditions." and is_used=0 and expired_time>='".date("Y-m-d H:i:s",time())."'";
        }else if(strcasecmp($type, "used")){
            $conditions=$conditions." and is_used=1 ";
        }else if(strcasecmp($type, "expired")){
            $conditions=$conditions." and is_used=0 and expired_time<'".date("Y-m-d H:i:s",time())."'";
        }
        
        $order=" order by create_time desc";
        $items=shareModel::getCouponByConditionsByPage($conditions, $order, $offset, $cnt);
        
        if(count($items)==0){
            $this->json([
                'code'=>0,
                'msg'=>'',
                'data'=>[
                    'cnt'=>0,
                    'items'=>[]
                ]
            ]);
        }else{
            $detas=[];
            foreach($items as $item){
                array_push($detas, (object)$item);
            }
            $this->json([
                'code'=>0,
                'msg'=>'',
                'data'=>[
                    'cnt'=>count($items),
                    'items'=>$detas
                ]
            ]);
        }
        
    }
    
    


}
