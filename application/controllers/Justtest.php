<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use \QCloud_WeApp_SDK\Auth\LoginService as LoginService;
use QCloud_WeApp_SDK\Constants as Constants;

class Justtest extends CI_Controller {
    public function index() {
            $this->json([
                'code' => -1,
                'data' => 'just test '
            ]);

    }
}
