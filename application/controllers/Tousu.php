<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use QCloud_WeApp_SDK\Constants as Constants;
use \QCloud_WeApp_SDK\Model\Tousu as TousuModel;
use \QCloud_WeApp_SDK\Model\User as User;
use \QCloud_WeApp_SDK\Model\FileRecord as FileRecordModel;

/**
投诉相关

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
   
   /**
   * 带图片投诉
   */
public function addTousu(){
		
		$uri = $_SERVER['REQUEST_URI']; 
		
		//$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
		
		$openId=$_POST["openId"];	
		$userinfo = User::findUserByOpenId($openId);
		
		//$this->json([
		//'openId'=>$openId,
		//'userinfo'=>$userinfo
		//]);
		//return;
		
		//-------------create files dir -----------------//
		$file = $_FILES['upict']; // 
		$tmpPath=$file['tmp_name'];
		$dir='./uploads/';
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
		
		
 
		//--save record---//	
	//public static function addUserTousu2($input_customer_id,$extra_comment,$table_id,$store_id,$picture_cnt,$picture_dir,$tousu){
		$res=TousuModel::addUserTousu2($userinfo->id,$_POST["extraDesc"],$_POST["tableId"],$_POST["storeId"],1,$file_path,$_POST['complain_ids']);
		
		if($res==NULL){
			$this->json(['code'=>-1,'desc'=>'fail']);
			return;
		}
		
		//----------save file----//
		$ok=false;
		
		$originalName = $file['name']; 
        $arr = explode(".", $originalName);
		$dest_name="tousu-".$res->id.'-1.'.$arr[count($arr)-1];
		$destination=$file_path.'/'.$dest_name;
		if(move_uploaded_file($tmpPath, $destination)){
			$ok=true;
		}
		
		//-----save to file record---//
		$res=FileRecordModel::storeFileRecord ($userinfo->id,1, $res->id, $destination,$file['size'] ) ;
		$filerec=false;
		if($res!=NULL){
			$filerec=true;
			
		}
		$this->json([
		'code'=>1,
		'type'=>$file['type'],
		'tmpname'=>$file['tmp_name'],
		'destination'=>$destination,
		'ok'=>$ok,
		'name'=>$file['name'] ,
		'suffix'=>$arr[1],
		'filerec'=>$filerec
		]);		
	}
}
