<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use \QCloud_WeApp_SDK\Model\Remark as Remark;
use QCloud_WeApp_SDK\Constants as Constants;

class RemarkTemplate extends CI_Controller {
    public function index() {
        $result = Remark::getRemarkTemplateInfo();
		$this->json(['data'=>$result]);
    }
}
