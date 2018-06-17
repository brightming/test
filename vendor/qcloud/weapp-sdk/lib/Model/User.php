<?php
namespace QCloud_WeApp_SDK\Model;

use QCloud_WeApp_SDK\Mysql\Mysql as DB;
use QCloud_WeApp_SDK\Constants;
use \Exception;

class User
{
    public static function storeUserInfo ($userinfo, $skey, $session_key) {
        $uuid = bin2hex(openssl_random_pseudo_bytes(16));
        $create_time = date('Y-m-d H:i:s');
        $last_visit_time = $create_time;
        $open_id = $userinfo->openId;
        $user_info = json_encode($userinfo);

        $res = DB::row('cSessionInfo', ['*'], compact('open_id'));
        if ($res === NULL) {
            DB::insert('cSessionInfo', compact('uuid', 'skey', 'create_time', 'last_visit_time', 'open_id', 'session_key', 'user_info'));
        } else {
            DB::update(
                'cSessionInfo',
                compact('uuid', 'skey', 'last_visit_time', 'session_key', 'user_info'),
                compact('open_id')
            );
        }
    }

    public static function findUserBySKey ($skey) {
        return DB::row('cSessionInfo', ['*'], compact('skey'));
    }
	
	public static function findUserByOpenId ($open_id) {
        return DB::row('cSessionInfo', ['*'], compact('open_id'));
    }
	public static function findUserByUnionId ($unionId) {
        return DB::row('cSessionInfo', ['*'], compact('union_id'));
    }
    
    /**
     * 获取指定id的员工的角色详情
     */
    public static function getStaffRoleDetail($staff_id){
        $sql="select a.name,b.name as role_name from (select * from StaffRole where staff_id=$staff_id) as a left join Role as b on a.role_id=b.id";
        $res=DB::raw_select($sql);
        if($res==NULL || count($res)==0){
            return NULL;
        }else{
            return $res[0];
        }
    }
}
