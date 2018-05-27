<?php

defined('BASEPATH') OR exit('No direct script access allowed');

use QCloud_WeApp_SDK\Constants as Constants;
use \QCloud_WeApp_SDK\Model\Remark as remarkModel;
use \QCloud_WeApp_SDK\Model\User as User;

/**
  点评相关

 */
class Setting extends CI_Controller {

    /**
     * post的方式接收用户的lng,lat,unionId
     */
    public function check_remark_setting() {
        echo $this->input->method();
    }

}
