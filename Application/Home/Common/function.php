<?php
/**
 * Created by PhpStorm.
 * User: Yinger650
 * Date: 2016/5/11
 * Time: 0:54
 */


/**
 * 检测用户是否登录
 * @return integer 0-未登录，大于0-当前登录用户ID
 */
function is_login(){
    $user = session('user');
    if (empty($user)) {
        return 0;
    } else {
        return $user['uid'];
    }
}