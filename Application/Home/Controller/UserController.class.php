<?php
/**
 * Created by PhpStorm.
 * User: Yinger650
 */

namespace Home\Controller;
use Think\Controller;

class UserController extends Controller {
    public function login(){
        $this->display();
    }

    public function register() {
        $this->display();
    }

    public function logout() {
        
    }


}