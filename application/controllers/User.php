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
		
		/*$desc=$_REQUEST['extraDesc'];
		$scores=$_REQUEST['scores'];
		$storeId=$_REQUEST['storeId'];
		$tableId=$_REQUEST['tableId'];
		$openId=$_REQUEST['openId'];
		
		$this->json([
                'desc' => $desc,
                'scores' => $scores,
				'storeId'=>$storeId,
				'tableId'=>$tableId,
				'openId'=>$openId
            ]);
			*/
			$this->json([
			'uri'=>$uri
			]);
			
	}
}
