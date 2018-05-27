<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use \QCloud_WeApp_SDK\Auth\LoginService as LoginService;
use QCloud_WeApp_SDK\Constants as Constants;

class TestFile extends CI_Controller {
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
    
    public function test_accept_params(){
        $met = $this->input->method();
        if (strcasecmp($met, "post") != 0) {
            $this->json(["code" => 600, "msg" => "check_remark_setting.expected post method"]);
           // return;
        }
        
        $name = $this->input->post('name');
        //$this->json(["name"=>$name]);
        
        $rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
        $mypost = json_decode($rws_post);
        $name2=$mypost->name;
        $this->json(["name"=>$name2]);
    }

}
