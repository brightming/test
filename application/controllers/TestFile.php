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

       // $rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
 
        $cont=$this->input->get_request_header('Content-Type', TRUE);
        $inputs=$this->input;
        if(strcasecmp($cont, "application/json")==0){
            $raw=$GLOBALS['HTTP_RAW_POST_DATA'];
            $inputs= json_decode($raw);
        }

        $name2 =commonModel::get_obj_value($inputs, "name");
        $age =commonModel::get_obj_value($inputs, "age");
        $a=commonModel::get_obj_value($inputs, "ok");
        
        $this->json(["name" => $name2,'age'=>$age,'a'=>$a,'conttype'=>$cont]);
    }

}
