<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use QCloud_WeApp_SDK\Auth\LoginService as LoginService;
use QCloud_WeApp_SDK\Constants as Constants;

include_once APPPATH . 'third_party/wxBizDataCrypt.php';

class Login extends CI_Controller {
    public function index() {
        $result = LoginService::login();
        
        if ($result['loginState'] === Constants::S_AUTH) {
            $this->json([
                'code' => 0,
                'data' => $result['userinfo']
            ]);
        } else {
            $this->json([
                'code' => -1,
                'error' => $result['error']
            ]);
        }
    }

    /**
     * [decode userinfo的加密字段解密接口]
     * @return [type] [description]
     */
    public function decode() {

        $appid = $this->input->get_post("appid");
        $sessionKey = $this->input->get_post("sessionKey");
        $encryptedData = $this->input->get_post("encryptedData");
        $iv = $this->input->get_post("iv");


        $pc = new WXBizDataCrypt($appid, $sessionKey);
        $errCode = $pc->decryptData($encryptedData, $iv, $data );

        if ($errCode == 0) {
            print($data . "\n");
        } else {
            print($errCode . "\n");
        }
    }
}
