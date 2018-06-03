<?php

namespace QCloud_WeApp_SDK\Model;

use QCloud_WeApp_SDK\Mysql\Mysql as DB;
use QCloud_WeApp_SDK\Model\Common as commonModel;
use QCloud_WeApp_SDK\Constants;
use \Exception;

class Remark {

    public static function addRemarkTemplate($remarkTemp) {
        $tableName = "RemarkTemplate";
        if (gettype($remarkTemp) == "object") {
            $data = commonModel::object_to_array($remarkTemp);
            return DB::insert($tableName, $data);
        } else if (gettype($remarkTemp) == "array") {
            return DB::insert($tableName, $remarkTemp);
        } else {
            return -1;
        }
    }

    /**
     * 获取全部的点评模板信息，以seq升序来排
     *
     */
    public static function getRemarkTemplateInfo() {
        return DB::select('RemarkTemplate', ['*'], 'status=1', '', 'order by seq asc');
    }

    /* 获取用户最新的点评 */

    public static function getUserLatestRemark($customerId, $storeId = '', $tableId = '') {
        if ($storeId != "" && $tableId != "") {
            return DB::select('CustomerRemarkRecord', ['*'], ['customer_id' => $customerId, 'tableId' => $tableId, 'storeId' => $storeId], 'and', 'order by remark_time desc limit 1');
        } else if ($storeId != "" && $tableId == "") {
            return DB::select('CustomerRemarkRecord', ['*'], ['customer_id' => $customerId, 'storeId' => $storeId], 'and', 'order by remark_time desc limit 1');
        } else if ($storeId == "" && $tableId != "") {
            return DB::select('CustomerRemarkRecord', ['*'], ['customer_id' => $customerId, 'tableId' => $tableId], 'and', 'order by remark_time desc limit 1');
        } else {
            return DB::select('CustomerRemarkRecord', ['*'], ['customer_id' => $customerId], 'and', 'order by remark_time desc limit 1');
        }
    }

    /**
     * customer_id是用户的数据库id
     * remarkData是stdclass,包含有：desc：字符串，scores：数组，storeId：数字，tableId：数字
     *
     */
    public static function addUserRemark($input_customer_id, $remarkData) {
        //插入CustomerRemarkRecord
        $customer_id = $input_customer_id;
        $remark_time = date('Y-m-d H:i:s');
        $order_id = strtotime($remark_time);
        $extra_remark_desc = $remarkData->extraDesc;
        $tableId = $remarkData->tableId;
        $storeId = $remarkData->storeId;
        $remarks=json_encode($remarkData->scores);

        DB::insert('CustomerRemarkRecord', compact('customer_id', 'remark_time', 'order_id', 'extra_remark_desc', 'tableId', 'storeId','remarks'));
        $res = DB::row('CustomerRemarkRecord', ['*'], compact('order_id', 'tableId'));

//        $customer_remark_rec_id = $res->id;

//        //插入CustomerRemarkDetail
//        foreach ($remarkData->scores as $scoreItem) {
//            $remark_template_id = $scoreItem->remarkTempId;
//            $remark_score = $scoreItem->score;
//            DB::insert('CustomerRemarkDetail', compact('customer_remark_rec_id', 'remark_template_id', 'remark_score'));
//        }

        return $res;
    }

    /**
     * 获取同桌用户的点评领券情况
     */
    public static function getAllVoucherOneTable() {
        $met = $this->input->method();
        if (strcasecmp($met, "post") != 0) {
            $this->json(["code" => 600, "msg" => "getAllVoucherOneTable.expected post method"]);
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
        $token = commonModel::get_obj_value($inputs, 'token');
        $storeId = commonModel::get_obj_value($inputs, 'storeId');
        $tableId = commonModel::get_obj_value($inputs, 'tableId');
        $token = commonModel::get_obj_value($inputs, 'token');


        if ($storeId == NULL || $tableId == NULL || $token == NULL) {
            $this->json(["code" => -1, "msg" => "getAllVoucherOneTable.not enough params"]);
            return;
        }

        //unionId to customer_id
        //获取同个分店，同张桌，对应id前后时间的记录
        $sql = "select * from CustomerLuckyRecord where id=$token";
        $one = DB::raw($sql);
        if ($one == NULL) {
            $this->json(["code" => -2, "msg" => "getAllVoucherOneTable.token is invalid"]);
            return;
        }
        if ($one->{"customer_id"} != $customer_id || $one->{"table_id"} != $tableId || $one->{"store_id"} != $storeId) {
            $this->json(["code" => -3, "msg" => "getAllVoucherOneTable.token is invalid"]);
            return;
        }

        $remark_time = strtotime($one->{"lucky_time"});
        $beg = lucky_time - 10 * 60;
        $end = lucky_time + 10 * 60;

        $beg_str = date('YYYY-m-d H:i:s', $beg);
        $end_str = date("YYYY-m-d H:i:s", $end);

        $sql = "select b.* from (select id ,lucky_time from CustomerLuckyRecord where table_id=$tableId and store_id=$storeId and lucky_time>='$beg_str' and lucky_time<=$end_str) as a
            inner join (select * from Coupon where create_time>='$beg_str' and create_time<='$end_str') as b on a.create_id=b.id";
        $res = DB::raw_select($sql);
    }

    /**
     * 计算用户点评的总数
     * @param type $customer_id
     */
    public static function getUerRemarkCnt($customer_id) {
        $sql = "select count(id) as cnt from CustomerRemarkRecord where customer_id=$customer_id";
        $res = DB::raw_select($sql);
        if ($res == NULL || count($res) == 0) {
            return 0;
        } else {
            return $res[0]->cnt;
        }
    }

    /**
     * 分页获取用户的点评信息
     * @param type $customer_id
     * @param type $offset
     * @param type $cnt
     */
    public static function getUserRemarkByPages($customer_id, $offset, $cnt, $conditions = "") {
        if ($offset < 0 || $cnt <= 0) {
            return [];
        }
        $sql = "select * from CustomerRemarkRecord where customer_id=$customer_id  ";
        if ($conditions != "") {
            $sql = $sql . " and " . $conditions;
        }
        $sql = $sql . " order by remark_time desc limit $offset,$cnt";
        $res = DB::raw_select($sql);
        return $res;
    }

}
