<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace QCloud_WeApp_SDK\Model;


class PayParams {

    public $appid; //小程序ID ,String(32)
    public $mch_id; //商户号 ,String(32)
    public $device_info; //设备号 ,String(32)
    public $nonce_str; //随机字符串 ,String(32)
    public $sign; //签名 ,String(32)
    public $sign_type; //签名类型 ,String(32)
    public $body; //商品描述 ,String(128)
    public $detail; //商品详情 ,String(6000)
    public $attach; //附加数据 ,String(127)
    public $out_trade_no; //商户订单号 ,String(32)
    public $fee_type; //标价币种 ,String(16)
    public $total_fee; //标价金额 ,Int
    public $spbill_create_ip; //终端IP ,String(16)
    public $time_start; //交易起始时间 ,String(14)
    public $time_expire; //交易结束时间 ,String(14)
    public $goods_tag; //订单优惠标记 ,String(32)
    public $notify_url; //通知地址 ,String(256)
    public $trade_type; //交易类型 ,String(16)
    public $product_id; //商品ID ,String(32)
    public $limit_pay; //指定支付方式 ,String(32)
    public $openid; //用户标识 ,String(128)

}

/**
 * Description of WeixinPay
 *
 * @author gumh
 */
class WeixinPay {

    //put your code here
    protected $appid;
    protected $mch_id;
    protected $key;
    protected $openid;

    function __construct($appid, $openid, $mch_id, $key) {
        $this->appid = $appid;
        $this->openid = $openid;
        $this->mch_id = $mch_id;
        $this->key = $key;
    }

    public function pay(PayParams $payParams) {
        //统一下单接口
        $return = $this->weixinapp($payParams);
        return $return;
    }

    //统一下单接口
    private function unifiedorder(PayParams $payParams) {
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $parameters = array(
            'appid' => $this->appid, //小程序ID
            'mch_id' => $this->mch_id, //商户号
            'nonce_str' => $this->createNoncestr(), //随机字符串
            'body' => $payParams->body, //商品描述
            'out_trade_no' => $payParams->out_trade_no, //商户订单号
            'total_fee' => $payParams->total_fee, //总金额 单位 分
            'spbill_create_ip' => $payParams->spbill_create_ip, //$_SERVER['REMOTE_ADDR'], //终端IP
            'notify_url' => $payParams->notify_url, //'https://www.haimenyuzai.com/payment/pay_callback', //通知地址
            'openid' => $this->openid, //用户id
            'trade_type' => 'JSAPI'//交易类型
        );
        //统一下单签名
        $parameters['sign'] = $this->getSign($parameters);
        $xmlData = $this->arrayToXml($parameters);

        $return = $this->xmlToArray($this->postXmlSSLCurl($xmlData, $url, 60));

        return $return;
    }
    
    private function postXmlSSLCurl($xml, $url, $second = 30)   
    {  
        $ch = curl_init();  
        //设置超时  
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);  
        curl_setopt($ch, CURLOPT_URL, $url);  
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);  
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); //严格校验  
        //设置header  
        curl_setopt($ch, CURLOPT_HEADER, FALSE);  
        //要求结果为字符串且输出到屏幕上  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);  
        //post提交方式  
        curl_setopt($ch, CURLOPT_POST, TRUE);  
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);  
  
  
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);  
        curl_setopt($ch, CURLOPT_TIMEOUT, 40);  
        set_time_limit(0);  
  
  
        //运行curl  
        $data = curl_exec($ch);  
        //返回结果  
        if ($data) {  
            curl_close($ch);  
            return $data;  
        } else {  
            $error = curl_errno($ch);  
            curl_close($ch);  
            throw new WxPayException("curl出错，错误码:$error");  
        }  
    }  

    //数组转换成xml  
    private function arrayToXml($arr) {
        $xml = "<root>";
        foreach ($arr as $key => $val) {
            if (is_array($val)) {
                $xml .= "<" . $key . ">" . arrayToXml($val) . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            }
        }
        $xml .= "</root>";
        return $xml;
    }

    //xml转换成数组  
    private function xmlToArray($xml) {

        //禁止引用外部xml实体   
        libxml_disable_entity_loader(true);

        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);

        $val = json_decode(json_encode($xmlstring), true);


        return $val;
    }

    //微信小程序接口
    private function weixinapp(PayParams $payParams) {
        //统一下单接口
        $unifiedorder = $this->unifiedorder($payParams);

        $parameters = array(
            'appId' => $this->appid, //小程序ID
            'timeStamp' => '' . time() . '', //时间戳
            'nonceStr' => $this->createNoncestr(), //随机串
            'package' => 'prepay_id=' . $unifiedorder['prepay_id'], //数据包
            'signType' => 'MD5'//签名方式
        );
        //签名
        $parameters['paySign'] = $this->getSign($parameters);

        return $parameters;
    }

    //作用：产生随机字符串，不长于32位
    private function createNoncestr($length = 32) {
        $chars = "abc01def23ghijklmnop789qrstuvwxyz456";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    //作用：生成签名
    private function getSign($Obj) {
        foreach ($Obj as $k => $v) {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = WeixinPay::formatBizQueryParaMap($Parameters, false);
        //签名步骤二：在string后加入KEY
        $String = $String . "&key=" . $this->key;
        //签名步骤三：MD5加密
        $String = md5($String);
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        return $result_;
    }

    

}
