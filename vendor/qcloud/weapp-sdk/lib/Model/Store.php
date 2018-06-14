<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace QCloud_WeApp_SDK\Model;

use QCloud_WeApp_SDK\Mysql\Mysql as DB;

/**
 * Description of Store
 *
 * @author gumh
 */
class Store {

    public static function getStoresByPage($offset, $cnt) {
        if ($offset < 0 || $cnt <= 0) {
            return [];
        }
        $sql = "select * from Store limit $offset,$cnt";
        return DB::raw_select($sql);
    }

    public static function getStoreCnt() {
        $sql = "select count(id) as cnt from Store";
        $res = DB::raw_select($sql);
        return $res[0]->cnt;
    }

    //put your code here
    public static function getAllStores() {

        $sql = "select * from Store where status=1";
        return DB::raw_select($sql);
    }

    public static function addStore($store) {
        //TODO

        return 0;
    }

    public static function getStoreById($id) {
        return DB::row("Store", ['*'], " id=$id");
    }

    /**
     * 获取指定分店的员工信息
     * @param type $store_id
     */
    public static function getStoreStaff($store_id) {
        $sql = "select a.*,b.name as role_name from (select * from StaffRole where store_id=$store_id) as a left join Role as b on a.role_id=b.id";
        return DB::raw_select($sql);
    }

    /**
     * 获取指定分店的桌子信息
     * @param type $store_id
     */
    public static function getStoreTables($store_id) {
        $sql = "select * from DinnerTable where store_id=$store_id";
        return DB::raw_select($sql);
    }

    /**
     * 获取指定分店是否在房间内的桌子
     * @param type $store_id
     * @param type $is_inroom
     */
    public static function getStoreInRoomTables($store_id, $is_inroom = 1) {

        $sql = "select * from DinnerTable where store_id=$store_id and is_inroom=$is_inroom";
        return DB::raw_select($sql);
    }

    /**
     * 删除参数指定的桌子与员工的关系
     * @param type $tableids_str
     */
    public static function removeStaffTableRelation($tableids_str) {
        $conditions = "table_id in ($tableids_str)";
        return DB::delete("StaffAndTable", $conditions);
    }

    /**
     * 保存服务员与table的关系
     * @param type $staff_id
     * @param type $table_ids
     */
    public static function saveStaffTableRelation($staff_id, $table_ids) {
        $cnt = 0;
        if (count($table_ids) == 0) {
            return $cnt;
        }

        foreach ($table_ids as $id) {
            $data = ["staff_id" => $staff_id, "table_id" => $id];
            $cnt += DB::insert("StaffAndTable", $data);
        }
    }
    
    /**
     * 获取指定员工管理的桌子信息
     * @param type $staff_id
     */
    public static function getTableOfStaff($staff_id){
        $sql="select b.* from (select table_id from StaffAndTable where staff_id=$staff_id)as a left join DinnerTable as b on a.table_id=b.id";
        return DB::raw_select($sql);
    }

}
