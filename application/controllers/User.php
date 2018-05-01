<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use \QCloud_WeApp_SDK\Auth\LoginService as LoginService;
use QCloud_WeApp_SDK\Constants as Constants;

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
	
	public function addRemark(){
		
		$uri = $_SERVER['REQUEST_URI']; 
		
		$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
		$mypost = json_decode($rws_post);
		
		$desc=$mypost['extraDesc'];
		$scores=$mypost['scores'];
		$storeId=$mypost['storeId'];
		$tableId=$mypost['tableId'];
		$openId=$mypost['openId'];
		
		
		/*
		$this->json([
                'desc' => $desc,
                'scores' => $scores,
				'storeId'=>$storeId,
				'tableId'=>$tableId,
				'openId'=>$openId
            ]);
			*/
			$this->json([
			'uri'=>$uri,
			'openId'=>$this->input->post('openId'),
			'rws_post'=>$rws_post
			]);
			
			
	}
}
