<?php
namespace Home\Controller;

use Think\Controller;

class IndexController extends Controller
{
    public function index()
    {
        echo "aliphoto";
    }

    public function album() {
        echo "进入相册";
    }
}