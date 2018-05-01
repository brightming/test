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
			'uri'=>$uri,
			'openId'=>$this->input->post('openId')
			]);
			
			JSONObject jsonObject = JSONObject.fromObject(getRequestPayload(request));
            String openId=jsonObject.get("openId").toString();
            String tableId=jsonObject.get("tableId").toString();
            String s = "";
            response.setContentType("application/json;charset=utf-8");
            response.setHeader("Access-Control-Allow-Origin", "*");
            System.out.println(openId + " " + tableId);
            s = "{\"employees\": [{ \"firstName\":\"John\" , \"lastName\":\"Doe\" },{ \"firstName\":\"Anna\" , \"lastName\":\"Smith\" },{ \"firstName\":\"Peter\" , \"lastName\":\"Jones\" }]}";
            response.getWriter().write(s);
			
	}
}
