<?php

namespace QCloud_WeApp_SDK\Helper;

class Util {

    private static $postPayload = NULL;

    public static function getHttpHeader($headerKey) {
        $headerKey = strtoupper($headerKey);
        $headerKey = str_replace('-', '_', $headerKey);
        $headerKey = 'HTTP_' . $headerKey;
        return isset($_SERVER[$headerKey]) ? $_SERVER[$headerKey] : '';
    }

    public static function writeJsonResult($obj, $statusCode = 200) {
        header('Content-type: application/json; charset=utf-8');

        http_response_code($statusCode);
        echo json_encode($obj, JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE);

        Logger::debug("Util::writeJsonResult => [{$statusCode}]", $obj);
    }

    public static function getPostPayload() {
        if (is_string(self::$postPayload)) {
            return self::$postPayload;
        }

        return file_get_contents('php://input');
    }

    public static function setPostPayload($payload) {
        self::$postPayload = $payload;
    }

    public static function createNoncestr($length = 32) {
        $chars = "abc01def23ghijklmnop789qrstuvwxyz456";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    //将xml格式转换成数组  
    public static function xmlToArray($xml) {

        //禁止引用外部xml实体   
        libxml_disable_entity_loader(true);

        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);

        $val = json_decode(json_encode($xmlstring), true);

        return $val;
    }
    
    ///作用：格式化参数，签名过程需要使用
    public static function formatBizQueryParaMap($paraMap, $urlencode) {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if ($urlencode) {
                $v = urlencode($v);
            }
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar;
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }

}
