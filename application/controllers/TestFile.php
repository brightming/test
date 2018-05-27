<?php

defined('BASEPATH') OR exit('No direct script access allowed');

use \QCloud_WeApp_SDK\Auth\LoginService as LoginService;
use QCloud_WeApp_SDK\Constants as Constants;
use QCloud_WeApp_SDK\Model\Common as commonModel;

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

    public function test_accept_params() {
        $met = $this->input->method();
        if (strcasecmp($met, "post") != 0) {
            $this->json(["code" => 600, "msg" => "check_remark_setting.expected post method"]);
           // return;
        }

       // $name = $this->input->post('name');
        //$this->json(["name"=>$name]);

        $rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
        
        $rws_post=$this->input->raw_input_stream;
        $mypost = json_decode($rws_post);
        $name2 =commonModel::get_post_value($mypost, "name");
        $age =commonModel::get_post_value($mypost, "age");
        $a=commonModel::get_post_value($mypost, "ok");
        
        $this->json(["name" => $name2,'age'=>$age,'a'=>$a]);
    }

}
