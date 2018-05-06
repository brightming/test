<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use QCloud_WeApp_SDK\Constants as Constants;
use \QCloud_WeApp_SDK\Model\Tousu as TousuModel;
use \QCloud_WeApp_SDK\Model\User as User;

/**
点评相关

*/
class Tousu extends CI_Controller {

public function index(){
	//获取投诉模板
	$result=TousuModel::getTousuTemplateInfo();
	$this->json(['data'=>$result]);
}

public function addTousuNoPict(){
	
	$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
	$mypost = json_decode($rws_post);
	$openId=$mypost->openId;
	
	$userinfo = User::findUserByOpenId($openId);
	$res=TousuModel::addUserTousu($userinfo->id,$mypost);
	
	if($res==NULL){
		$this->json(['code'=>-1,'desc'=>'fail']);
	}else{
		$this->json(['code'=>1,'desc'=>'success','id'=>$res->id]);
	}
}
   
public function addTousu(){
		
		$uri = $_SERVER['REQUEST_URI']; 
		
		//$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
		
		$openId=$_POST["openId"];
	
		$datas=stdclass();
		$userinfo = User::findUserByOpenId($openId);
		
		
		
		//-------------create files dir -----------------//
		$file = $_FILES['upict']; // 
		$tmpPath=$file['tmp_name'];
		$dir='.uploads/'
		//按照年/月/日创建文件夹
		$file_path="$dir".'/'.date("Y").'/'.date("m").'/'.date("d"); 
        if(!is_dir($file_path)){  
             if (mkdir($file_path,755,true)) {  
                  echo "创建递归文件夹成功";  
             }else{  
                 echo "创建文件夹失败";  
             }  
		}else{  
				echo "该文件夹已经有了";  
		}  		
		
		$datas->extra_comment=$_POST["extraDesc"];
		$datas->table_id=$_POST["tableId"];
		$datas->store_id=$_POST["storeId"];
		$datas->picture_cnt=1;
		$datas->picture_dir=$file_path;
		$datas->tousu=$_POST["tousu"];
	    
		//--save record---//
		$res=TousuModel::addUserTousu($userinfo->id,$datas);
		
		if($res==NULL){
			$this->json(['code'=>-1,'desc'=>'fail']);
			return;
		}
		
		//----------save file----//
		$ok=false;
		
		$originalName = $file['name']; 
        $arr = explode(".", $originalName);
		$dest_name="tousu-".$res->id.'-1'.$arr[1];
		$destination=$file_path.'/'.$dest_name;
		if(move_uploaded_file($tmpPath, $destination)){
			$ok=true;
		}
		
		//-----save to file record---//
		
		
		$this->json([
		'type'=>$file['type'],
		'tmpname'=>$file['tmp_name'],
		'destination'=>$destination,
		'ok'=>$ok,
		'name'=>$file['name'] ,
		'suffix'=>arr[1]
		]);		
	}
}
