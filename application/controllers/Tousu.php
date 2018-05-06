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
		
			
		$file = $_FILES['upict']; // 
		$tmpPath=$file['tmp_name'];
		
		
		//$uploaddir = $_SERVER['DOCUMENTROOT']."/uploads/";
		$ok=false;
		$destination='uploads/2.jpg';
		if(move_uploaded_file($tmpPath, $destination)){
			$ok=true;
		}
		
		$this->json([
		'type'=>$file['type'],
		'tmpname'=>$file['tmp_name'],
		'destination'=>$destination,
		'ok'=>$ok,
		'filetype'=>$file['type'] 
		]);
		
			
	}
}
