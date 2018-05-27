<?php

namespace QCloud_WeApp_SDK\Model;

use QCloud_WeApp_SDK\Mysql\Mysql as DB;
use QCloud_WeApp_SDK\Constants;
use \Exception;

class Common {

    
    public static function get_post_value($data,$property){
        if(property_exists($data, $property)){
            return $data->{$property};
        }else{
            return NULL;
        }
    }
    
    /**
     * 数组 转 对象
     *
     * @param array $arr 数组
     * @return object
     */
    public static function array_to_object($arr) {
        if (gettype($arr) != 'array') {
            return;
        }
        foreach ($arr as $k => $v) {
            if (gettype($v) == 'array' || getType($v) == 'object') {
                $arr[$k] = (object) array_to_object($v);
            }
        }

        return (object) $arr;
    }

    /**
     * 对象 转 数组
     *
     * @param object $obj 对象
     * @return array
     */
    public static function object_to_array($obj) {
        $obj = (array) $obj;
        foreach ($obj as $k => $v) {
            if (gettype($v) == 'resource') {
                return;
            }
            if (gettype($v) == 'object' || gettype($v) == 'array') {
                $obj[$k] = (array) object_to_array($v);
            }
        }

        return $obj;
    }

    /**
     * if cmp_date is between beg_date and end_date
     * @param type $beg_date
     * string: yyyy/MM/dd or YYY-MM-dd
     * @param type $end_date
     * @param type $cmp_date
     */
    public static function between_date($beg_date, $end_date, $cmp_date) {
        $a=get_date_beg_time($beg_date);
        $b=get_date_beg_time($end_date);
        $c=get_date_beg_time($cmp_date);
        
        if($a==-1 || $b==-1 || $c==-1){
            return false;
        }
        if($c>=$a && $c<=$b){
            return true;
        }else{
            return false;
        }
    }

    /**
     * if time string is 2018-5-20 11:00:22 ,then will return 2018-5-20 00:00:00
     * @param type $date_str
     * YYYY-MM-dd or YYYY/MM/dd
     */
    public static function get_date_beg_time($date_str) {
        if (strstr($date_str, ":")) {
            $arr = explode(" ", $date_str);
            if(count($arr)==0){
                return -1;
            }
            $date_str2=$arr[0]." 00:00:00";
            return strtotime($date_str2);
        } else {
            $date_str2 = $date_str . " 00:00:00";
            return strtotime($date_str2);
        }
    }
    
    /**
     * 比较一天之内的时间段
     * @param type $rngs_str
     * 9:00-12:00 18:00-22:00
     * @param type $cmp_time
     */
    public static function between_time_ranges($rngs_str,$cmp_time){
        if($rngs_str==""){
            return false;
        }
        
        $ct=strtotime($cmp_time);
        $arr1= explode(" ", $rngs_str);
        if(count($arr1)==0){
            return false;
        }
        foreach($arr1 as $one){
            $arr2=explode("-",$one);
            if(count($arr2)!=2){
                continue;
            }
            $t1=strtotime($arr2[0]);
            $t2=strtotime($arr2[1]);
            if($t1<=$ct && $t2>=$ct){
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * first find out in which range now time reside in,
     * and check if the input cmp_time is within the same time range
     * @param type $rngs_str
     * @param type $cmp_time
     */
    public static function between_cur_time_range($rngs_str,$cmp_time){
        if($rngs_str==""){
            return false;
        }    
        $curtime= strtotime(date('H:i:s',time()));  
        $ct=strtotime($cmp_time);     
        $arr1= explode(" ", $rngs_str);
        if(count($arr1)==0){
            return false;
        }
        foreach($arr1 as $one){
            $arr2=explode("-",$one);
            if(count($arr2)!=2){
                continue;
            }
            $t1=strtotime($arr2[0]);
            $t2=strtotime($arr2[1]);
            if($t1<=$curtime && $t2>=$curtime){
               if($t1<=$ct && $t2>=$ct){
                   return true;
               }
            }
        }
        
        return false;
    }
    
    public static function is_today($time_str){
        $in_time= strtotime($time_str);
        $curdate = date("Y/m/d");
        
        $b1=$curdate." 00:00:00";
        $bt= strtotime($b1);
        
        $e1=$curdate." 23:59:59";
        $et= strtotime($e1);
        
        if($in_time>=$bt && $in_time<=$et){
            return true;
        }
        return false;
        
    }

    public static function raw_sql_select($sql) {
        return DB::raw_select($sql);
    }

    //判断两个经纬度位置是否靠近
    public static function is_nearby($lng1, $lat1, $lng2, $lat2, $allow_dist) {
        $dist = get_distance($lng1, $lat1, $lng2, $lat2);
        if ($dist <= $allow_dist) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 求两个已知经纬度之间的距离,单位为米
     * 
     * @param lng1 $ ,lng2 经度
     * @param lat1 $ ,lat2 纬度
     * @return float 距离，单位米
     * @author www.Alixixi.com 
     */
    public static function get_distance($lng1, $lat1, $lng2, $lat2) {
        // 将角度转为狐度
        $radLat1 = deg2rad($lat1); //deg2rad()函数将角度转换为弧度
        $radLat2 = deg2rad($lat2);
        $radLng1 = deg2rad($lng1);
        $radLng2 = deg2rad($lng2);
        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * 6378.137 * 1000;
        return $s;
    }

}
