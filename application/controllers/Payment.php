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
use QCloud_WeApp_SDK\Model\WeixinPay as weixinPay;
use QCloud_WeApp_SDK\Helper\Util as utils;

/**
 * Description of Payment
 *
 * @author gumh
 */
class Payment extends CI_Controller {
    //put your code here

    /**
     * 由微信服务器返回的支付结果通知
     * 参考：https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=9_7&index=8
     */
    public function pay_callback() {
        $postXml = $GLOBALS["HTTP_RAW_POST_DATA"]; //接收微信参数  
        if (empty($postXml)) {
            return false;
        }

        $post_data = utils::xmlToArray($postXml);
        if($post_data["return_code"]!="SUCCESS"){
            
            return false;
        }
        
        
        $postSign = $post_data['sign']; 
        unset($post_data['sign']); 
        ksort($post_data);// 对数据进行排序  
        $str = utils::formatBizQueryParaMap($post_data,false);//对数组数据拼接成key=value字符串  
        $user_sign = strtoupper(md5($postdata));//再次生成签名，与postSign比较  
       if($postSign!=$user_sign){
           
           return false;
       }
       

        //        $payParams->attach = json_encode(["total_fee"=>$totalAmount,"couponId" => $couponId, "out_trade_no" => $tradeNumber, "trade_id" => $order->id,"time_end"=>$time_end
//                ,"payer_id"=>$user->id]);
        $total_fee = $post_data[total_fee];
        $openId = $post_data[openid];
        $out_trade_no = $post_data[out_trade_no];
        $time = $post_data[time_end];
        $couponId=$post_data["couponId"];
        $trade_id=$post_data["trade_id"];
        $payerId=$post_data["payer_id"];
        
        //check 
        $res=paymentModel::getPayrecById($trade_id);
        if($res==NULL){
            //
            return false;
        }
        if($res->pay_status!=0){
            //要么已支付成功，要么已支付失败
            return true;
        }
        
        $pay_status=1;//支付成功
        if($post_data["result_code"]!="SUCCESS"){
            $pay_status=-1;
        }
        //更新状态为已经支付成功
        paymentModel::setPayrecPaystatusByTradeId($trade_id, $pay_status);
        if($pay_status==-1){
            return;
        }
        
        //计算返现情况
        //这个需要放在支付回调通知函数中，只有支付成功了，才真正给以返现
        if ($couponId != -1) {
             $coupon = couponModel::getCouponById($couponId);
            if ($coupon == NULL) {
                $this->json(["code" => funCodeConst::INVALID_COUPON['code'], "msg" => __FUNCTION__ . "." . funCodeConst::INVALID_COUPON['msg']]);
                return;
            }
            
            $cashback = couponModel::calculateCashback($total_fee, $coupon);
            $create_t = date("Y-m-d H:i:s", time());
            $cashbackRec = [
                'customer_id' => $coupon->create_customer_id, //返现的钱，归分享的用户
                'use_coupon_id' => $couponId,
                'use_coupon_customer_id' => $payerId,
                'share_id' => $coupon->related_id,
                'cashback' => $cashback,
                'create_time' => $create_t];
            $res = shareModel::addCashbackRec($cashbackRec);
            if ($res == 0) {
                //rollback
                $this->json(["code" => funCodeConst::ERR_DB_OPER['code'], "msg" => __FUNCTION__ . "." . funCodeConst::ERR_DB_OPER['msg']]);
                return;
            }
            $conds = ['customer_id' => $coupon->create_customer_id, 'use_coupon_id' => $couponId, 'create_time' => $create_t];
            $cashbackrec = shareModel::getCashbackRecByCondition($conds);
            if ($cashbackrec == NULL) {
                $this->json(["code" => funCodeConst::ERR_DB_OPER['code'], "msg" => __FUNCTION__ . "." . funCodeConst::ERR_DB_OPER['msg']]);
                return;
            }

            //更新状态为使用并关联支付
            $res = shareModel::useCouponInPay($coupon->id, $user->id, $cashbackrec->id);
            if ($res == 0) {
                $this->json(["code" => funCodeConst::ERR_DB_OPER['code'], "msg" => __FUNCTION__ . "." . funCodeConst::ERR_DB_OPER['msg']]);
                return;
            }
        }

        
        
    }

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
        $openId = commonModel::get_obj_value($inputs, 'openId');


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
        else{
            $couponId=-1;
        }
        

//
        //
        //--------------------------------------开始操作数据------------------------------------//
        //1 支付记录
        //2 计算返现并保存
        //3 更新优惠券
        //保存支付记录
        $now = time();
        $paytime = date("Y-m-d H:i:s", $now);
        $tradeNumber = date("YmdHis-") . utils::createNoncestr(15);
        $data = ['total_price' => $totalAmount, 'discount_type' => 1, 'final_price' => $needPayAmount, 'payer' => $user->id, 'pay_time' => $paytime, 'pay_status' => 0,
            "trade_number" => $tradeNumber];
        $res = paymentModel::addPayRecord($data);
        $conds = ['payer' => $user->id, 'pay_time' => $paytime];
        $order = paymentModel::getOnePayrecByConditions($conds);
        if ($order == NULL) {
            $this->json(["code" => funCodeConst::ERR_DB_OPER['code'], "msg" => __FUNCTION__ . "." . funCodeConst::ERR_DB_OPER['msg']]);
            return;
        }

        

        //------------------------真正的支付调用-----------------//
        //TODO
        require_once 'WeixinPay.php';
        $appid = 'wx888888888'; //hardcode here
        $openid = $openId;
        $mch_id = '141388888';
        $key = '9A0A86888888888';
        $weixinpay = new QCloud_WeApp_SDK\Model\WeixinPay($appid, $openid, $mch_id, $key);

        $payParams = new QCloud_WeApp_SDK\Model\PayParams();
        $payParams->body = "海门鱼仔-餐费";
        $time_end=date("YmdHis",$now+60*90);//90分钟
        $payParams->attach = json_encode(["total_fee"=>$totalAmount,"couponId" => $couponId, "out_trade_no" => $tradeNumber, "trade_id" => $order->id,"time_end"=>$time_end
                ,"payer_id"=>$user->id]);
        $payParams->out_trade_no = $tradeNumber;
        $payParams->total_fee = $totalAmount * 100; //转换为分为单位
        $payParams->spbill_create_ip = $this->input->ip_address();
        $payParams->time_start = date("YmdHis", $now);
        $payParams->notify_url = "https://www.haimenyuzai.com/payment/pay_callback";
        $payParams->trade_type = "JSAPI";
        $payParams->openid = $openId;

        $paygetparams = $weixinpay->pay($payParams);

        $this->json([
            'code' => 0,
            'msg' => 'success',
            'data' => $paygetparams
        ]);
    }

    public function getUserPayStatistic() {
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

        $res = paymentModel::getUserPayStatistic($user->id);
        $this->json([
            'code' => 0,
            'msg' => '',
            'data' => $res
        ]);
    }

    /**
     * 查询买单记录
     */
    public function queryPayHistory() {
        $met = $this->input->method();
        if (strcasecmp($met, "get") != 0) {
            $this->json(["code" => funCodeConst::NEED_GET_METHOD['code'], "msg" => __FUNCTION__ . "." . funCodeConst::NEED_GET_METHOD['msg']]);
            return;
        }

        $unionId = $this->input->get("unionId");
        $offset = $this->input->get("offset");
        $cnt = $this->input->get("cnt");

        if ($offset == NULL) {
            $offset = 0;
        }
        if ($cnt == NULL) {
            $cnt = 5;
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

        $res = paymentModel::getPaymentRecsByPage($user->id, $offset, $cnt);
        $this->json([
            'code' => 0,
            'msg' => '',
            'data' => [
                'cnt' => count($res),
                'items' => $res
            ]
        ]);
    }

}
