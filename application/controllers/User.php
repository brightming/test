<?php

defined('BASEPATH') OR exit('No direct script access allowed');

use \QCloud_WeApp_SDK\Auth\LoginService as LoginService;
use QCloud_WeApp_SDK\Constants as Constants;
use \QCloud_WeApp_SDK\Model\Coupon as CouponModel;
use \QCloud_WeApp_SDK\Model\User as UserModel;
use \QCloud_WeApp_SDK\Model\Common as commonModel;

class User extends CI_Controller {

    public function index() {
        $result = LoginService::check();

        if ($result['loginState'] === Constants::S_AUTH) {
            $this->json([
                'code' => 0,
                'data' => $result['userinfo']
            ]);
        } else {
            $this->json([
                'code' => -1,
                'data' => []
            ]);
        }
    }

    public function getMyCoupon() {

        $openId = $_POST["openId"];
        $totalCouponCnt = $_POST["totalCouponCnt"]; //总数量，如果是第一次获取，此参数是负数，那么就需要先获取总数量
        $startIdx = $_POST["startIdx"]; //分页查询的起始编号
        $needCnt = $_POST["needCnt"]; //获取多少条

        if ($totalCouponCnt < 0) {
            
        }
    }

    public function getUserDrawMoneyRec() {
        $customer_id = 1;
        $store_id = 2;
        $res = CouponModel::getUseDrawCacheRecToday($customer_id, $store_id);
        if ($res == NULL) {
            echo 'no rec';
        } else {
            echo "cnt=" . count($res);
            foreach ($res as $row) {
                echo 'store_id=' . $row->store_id . ' create_time=' . $row->create_time . '<br/>';
            }
            //$res2=json_encode($res);
            //echo $res2;
        }
        //$res2=json_encode($res);
        //echo $res2;		
    }

    /**
     * 判断某用户此时是否能抽奖
     */
    public function canDrawMoney() {
        //token是判断的依
        //$this->input->get_post  对于post或test提交的都可以获取


        $token = $this->input->get_post('token');
        $storeId = $this->input->get_post('storeId');
        $openId = $this->input->get_post('openId');
        $this->json(['token' => $token, 'storeId' => $storeId, 'openId' => $openId]);
        //return;

        $userinfo = UserModel::findUserByOpenId($openId);

        //获取最新的token
        $setting = CouponModel::getDrawCashSetting($storeId);

        if ($setting == NULL || count($setting) == 0) {
            //此店未设置抽奖
            $this->json([
                'code' => -1,
                'desc' => '此店未设置抽奖'
            ]);
            return;
        }

        //对比token
        $oneset = $setting[0];
        //print_r("onset=".$oneset);
        if ($token != $oneset->token) {
            //用了过时的token
            $this->json([
                'code' => -2,
                'desc' => '请联系服务员扫描抽奖二维码'
            ]);
            return;
        }

        //记录允许的时间段

        $valid_time_rgs = $oneset->valid_time_ranges; //9:00-12:00,17:00-19:00
        $subrgs = explode(",", $valid_time_rgs);

        //判断当前属于哪个时间段
        $inwhich = -1;
        $idx = 0;
        $checkDayStr = date('Y-m-d ', time());
        foreach ($subrgs as $rg) {//每个允许时间段
            $rrgg = explode("-", $rg);
            if (count($rrgg) != 2) {
                //配置数据有误，全部禁止参与
                $this->json([
                    'code' => -3,
                    'desc' => '配置数据有误！'
                ]);
                return;
            }
            $timeBegin1 = strtotime($checkDayStr . "$rrgg[0]" . ":00");
            $timeEnd1 = strtotime($checkDayStr . "$rrgg[1]" . ":00");
            $curr_time = time();
            if ($curr_time >= $timeBegin1 && $curr_time <= $timeEnd1) {
                $inwhich = $idx;
                break;
            }
            $idx = $idx + 1;
        }

        //用户抽奖的记录
        if ($userinfo == NULL) {
            $this->json([
                'code' => -4,
                'desc' => '用户未登录！'
            ]);
            return;
        }
        $customer_id = $userinfo->id;
        $res = CouponModel::getUseDrawCacheRecToday($customer_id, $storeId);
        if (count($res) > 0) {
            //判断时间段
            foreach ($res as $row) {
                $idx = 0;
                $histtime = $row->create_time;
                //如果发现已有记录的时间段，有包含在inwhich指向的时间段，则说明当前时间段已经操作过一次了，这次不允许再抽奖了
                foreach ($subrgs as $rg) {//每个允许时间段
                    $rrgg = explode("-", $rg);
                    $timeBegin1 = strtotime($checkDayStr . "$rrgg[0]" . ":00");
                    $timeEnd1 = strtotime($checkDayStr . "$rrgg[1]" . ":00");
                    if ($histtime >= $timeBegin1 && $histtime <= $timeEnd1 && $idx == $inwhich) {
                        $this->json([
                            'code' => -5,
                            'desc' => '同一段时间只允许一次抽奖！'
                        ]);
                        return;
                    }
                    $idx = $idx + 1;
                }
            }
        }

        $this->json([
            'code' => 1,
            'desc' => '可以抽奖！'
        ]);
    }

    //分页获取指定用户的点评信息
    public function getMyRemark() {
        $unionId = $this->input->get_post('unionId');
        $offset = $this->input->get_post('offset');
        $cnt = $this->input->get_post('cnt');
        $needDetail = $this->input->get_post('needDetail');

        //get customer_id
        $userinfo = UserModel::findUserByUnionId($unionId);
        $customer_id = $userinfo->id;

        $sql = ' select t1.id ,t1.remark_time as createTime,t1.extra_remark_desc as extraDesc,Store.name as storeName from (select * from CustomerRemarkRecord where customer_id=' . $customer_id . ' limit ' . $offset . ',' . $cnt . ') t1 left join Store on t1.storeId=Store.id;';
        echo $sql;
        $recs = commonModel::raw_sql_select($sql);


        $this->json([
            'code' => 0,
            'desc' => '',
            'data' => $recs
        ]);
    }

    //分页获取指定用户的点评信息
    public function getRemarkDetail() {
        $unionId = $this->input->get_post('unionId');
        $remark_rec_id = $this->input->get_post('remark_rec_id');

        //get customer_id
        $userinfo = UserModel::findUserByUnionId($unionId);
        $customer_id = $userinfo->id;

        $sql = '  select t2.seq as seq,t2.content ,t1.remark_score as score from (select * from CustomerRemarkDetail where customer_remark_rec_id=' . $remark_rec_id . ') t1 left join RemarkTemplate t2 on t1.remark_template_id=t2.id;';
        $recs = commonModel::raw_sql_select($sql);

        $this->json([
            'code' => 0,
            'desc' => '',
            'data' => $recs
        ]);
    }

}
