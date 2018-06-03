<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use QCloud_WeApp_SDK\Model\Share as shareModel;
use \QCloud_WeApp_SDK\Model\User as UserModel;
use QCloud_WeApp_SDK\Model\Coupon as couponModel;
use QCloud_WeApp_SDK\Model\Payment as paymentModel;
use QCloud_WeApp_SDK\FunctionCodeConstants as funCodeConst;

/**
 * Description of Payment
 *
 * @author gumh
 */
class Payment extends CI_Controller {
    //put your code here

    /**
     * 支付
     */
    public function pay() {
        /**
         * 使用优惠券
         * 检测存在性，检测是否已经使用
         * 更改状态，
         * 记录其他关联数据
         */
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
        $couponId = commonModel::get_obj_value($inputs, 'couponId');
        $totalAmount = commonModel::get_obj_value($inputs, 'totalAmount');
        $needPayAmount = commonModel::get_obj_value($inputs, 'needPayAmount');


        if ($needPayAmount <= 0 || $totalAmount <= 0) {
            $this->json(["code" => funCodeConst::INVALID_PARAM['code'], "msg" => __FUNCTION__ . "." . funCodeConst::INVALID_PARAM['msg']]);
            return;
        }
        if ($needPayAmount > $totalAmount) {
            $this->json(["code" => funCodeConst::INVALID_PARAM['code'], "msg" => __FUNCTION__ . "." . funCodeConst::INVALID_PARAM['msg']]);
            return;
        }
        if ($couponId == NULL && $needPayAmount != $totalAmount) {//没有用优惠券时，应该一致
            $this->json(["code" => funCodeConst::INVALID_PARAM['code'], "msg" => __FUNCTION__ . "." . funCodeConst::INVALID_PARAM['msg']]);
            return;
        }

        if ($unionId == NULL) {
            $this->json(["code" => funCodeConst::NOT_ENOUGH_PARAM['code'], "msg" => __FUNCTION__ . "." . funCodeConst::NOT_ENOUGH_PARAM['msg']]);
            return;
        }

        //check user
        $user = UserModel::findUserByUnionId($unionId);
        if ($user == NULL) {
            $this->json(["code" => funCodeConst::INVALID_USER['code'], "msg" => __FUNCTION__ . "." . funCodeConst::INVALID_USER['msg']]);
            return;
        }


        if ($couponId != NULL) {
            //check coupon
            $coupon = couponModel::getCouponById($couponId);
            if ($coupon == NULL) {
                $this->json(["code" => funCodeConst::INVALID_COUPON['code'], "msg" => __FUNCTION__ . "." . funCodeConst::INVALID_COUPON['msg']]);
                return;
            }
            if ($coupon->status == 1) {//已经使用的优惠券
                $this->json(["code" => funCodeConst::INVALID_COUPON_STATE['code'], "msg" => __FUNCTION__ . "." . funCodeConst::INVALID_COUPON_STATE['msg']]);
                return;
            }
            //检测是不是你的优惠券
            if ($coupon->customer_id != $user->id) {//别人的优惠券
                $this->json(["code" => funCodeConst::INVALID_COUPON['code'], "msg" => __FUNCTION__ . "." . funCodeConst::INVALID_COUPON['msg']]);
                return;
            }

            //1：满多少减多少；
            //2：满多少折扣多少；
            //3：无门槛减多少；
            //4：代金券
            if ($coupon->type == 1) {
                if ($totalAmount < $coupon->limit_price) {
                    //对于有门槛的消费，要检测此次总金额是否满足使用要求
                    $this->json(["code" => funCodeConst::COUPON_NOT_MEET_CONDITION['code'], "msg" => __FUNCTION__ . "." . funCodeConst::COUPON_NOT_MEET_CONDITION['msg']]);
                    return;
                }
            }  
        }//有优惠券的情况
        //
        //
        //--------------------------------------开始操作数据------------------------------------//
        //1 支付记录
        //2 计算返现并保存
        //3 更新优惠券

        
        //保存支付记录
        $paytime = date("Y-m-d H:i:s", time());
        $data = ['total_price' => $totalAmount, 'discount_type' => 1, 'final_price' => $needPayAmount, 'payer' => $user->id, 'pay_time' => $paytime];
        $res = paymentModel::addPayRecord($data);
        
        //计算返现情况
        if ($couponId != NULL) {
            $cashback = couponModel::calculateCashback($totalAmount,$coupon);
            $create_t=date("Y-m-d H:i:s",time());
            $cashbackRec = [
                'customer_id' => $coupon->create_customer_id,//返现的钱，归分享的用户
                'use_coupon_id' => $couponId,
                'use_coupon_customer_id' => $user->id,
                'share_id'=>$coupon->related_id,
                'cashback'=>$cashback,
                'create_time'=>$create_t];
            $res=shareModel::addCashbackRec($cashbackRec);
                       
            if($res==0){
                //rollback
                $this->json(["code" => funCodeConst::ERR_DB_OPER['code'], "msg" => __FUNCTION__ . "." . funCodeConst::ERR_DB_OPER['msg']]);
                return;
            }
            $conds=['customer_id'=>$coupon->create_customer_id,'use_coupon_id' => $couponId,'create_time'=>$create_t];
            $cashbackrec= paymentModel::getOnePayrecByConditions($conds);
            if($cashbackrec==NULL){
                $this->json(["code" => funCodeConst::ERR_DB_OPER['code'], "msg" => __FUNCTION__ . "." . funCodeConst::ERR_DB_OPER['msg']]);
                return;
            }
            
            //更新状态为使用并关联支付
            $res = shareModel::useCouponInPay($coupon->id, $user->id,$cashbackrec->id);
            if ($res == 0) {
                $this->json(["code" => funCodeConst::ERR_DB_OPER['code'], "msg" => __FUNCTION__ . "." . funCodeConst::ERR_DB_OPER['msg']]);
                return;
            }
            
        }
        
        
        //------------------------真正的支付调用-----------------//
        //TODO
        
        $this->json([
            'code'=>0,
            'msg'=>'success',
        ]);
    }
    
    public function getUserPayStatistic(){
        $met = $this->input->method();
        if (strcasecmp($met, "get") != 0) {
            $this->json(["code" => funCodeConst::NEED_GET_METHOD['code'], "msg" => __FUNCTION__ . "." . funCodeConst::NEED_GET_METHOD['msg']]);
            return;
        }

        $unionId = $this->input->get("unionId");
        //check user
        $user = UserModel::findUserByUnionId($unionId);
        if ($user == NULL) {
            $this->json(["code" => funCodeConst::INVALID_USER['code'], "msg" => __FUNCTION__ . "." . funCodeConst::INVALID_USER['msg']]);
            return;
        }

        $res=paymentModel::getUserPayStatistic($user->id);
        $this->json([
            'code'=>0,
            'msg'=>'',
            'data'=>$res
        ]);
    }

    /**
     * 查询买单记录
     */
    public function queryPayHistory(){
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
        
        $res= paymentModel::getPaymentRecsByPage($user->id,$offset,$cnt);
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
