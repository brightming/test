<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace QCloud_WeApp_SDK;

/**
 * Description of FunctionCodeConstants
 * 函数交互的code定义
 *
 * @author gumh
 */
class FunctionCodeConstants {
    //put your code here
    
    const ERR_DB_OPER=['code'=>-100,'msg'=>'db error'];
    
    //无效用户
    const INVALID_USER=['code'=>'-1','msg'=>'invalid user'];
    const INVALID_STORE=['code'=>-101,'msg'=>'invalid store'];
    const INVALID_PLACE=['code'=>-102,'msg'=>'not in any store'];
   
    //参数不足
    const NOT_ENOUGH_PARAM=['code'=>'-2','msg'=>'not enough params'];
    const INVALID_PARAM=['code'=>'-3','msg'=>'wrong params'];
    
    
    
    //获取方式不对
    const NEED_POST_METHOD=['code'=>'600','msg'=>'need post method'];
    const NEED_GET_METHOD=['code'=>'601','msg'=>'need get method'];
    
    //数据不存在
    const INVALID_COUPON=['code'=>-300,'msg'=>'invalid coupon'];
    const INVALID_COUPON_STATE=['code'=>-301,'msg'=>'invalid coupon status'];
    const INVALID_TOKEN=['code'=>-302,'msg'=>'invalid token'];
    
    //超时
    const TIME_EXPIRED=['code'=>-400,'msg'=>"time expired"];
    const DUPLICATED_OPER=['code'=>-401,'msg'=>'duplicated operation!'];
    
    //优惠券
    const COUPON_NOT_MEET_CONDITION=['code'=>-401,'msg'=>'not allow to use this coupon'];
    
}
