<?php
/**
 * Created by PhpStorm.
 * User: Yinger650
 */

namespace Home\Controller;
use Think\Controller;

class UserController extends Controller {
    public function index(){
        if (is_login()) {
            echo "Hello " . session('user')['username'] . ".";
            //$this->success('You have login before', U('Photo/album'));
            echo "<a href='" . U('Photo/album') . "''>click</a>";
        } else {


            $this->display();
        }
    }

    public function register() {
        if (IS_POST) {
            // instantiate User
            $regUser = M('User');
            $rules = array(
                array('username','','exist username!',0,'unique',1),
                array('email','','exist email!',0,'unique',1),
                array('repassword','password','password and verify password are not same!',0,'confirm'),
                array('password','checkPwd','password contain illegal characters!',0,'function'),
                array('email','email','wrong email.'),
            );

            if (!$regUser->validate($rules)->create()){
                // 如果创建失败 表示验证没有通过 输出错误提示信息
                $this->error($regUser->getError());
            }else{
                // 验证通过 可以进行其他数据操作
                $regUser->password = md5($regUser->password);
                $regUser->add();
                $this->success('Register successfully.','index');
            }

        } else {
            $this->display();
        }
    }

    public function login() {
        $user = I('post.username');
        $pass = md5(I('post.password'));
        //echo $pass;
        $userTable = M('user');
        $userItem = $userTable->where('username = "%s"',$user)->find();
        if ($userItem) {
            if ($userItem == NULL) $this->error('No specific username');
            else {
                if ($userItem['password'] == $pass) {
                    /* record SESSION and COOKIES */
                    $auth = array(
                        'uid'             => $userItem['uid'],
                        'username'        => $userItem['username'],
                        'last_login_time' => NOW_TIME,
                    );
                    session('user', $auth);
                    $this->success('Login sucessfully.');
                } else {
                    //echo $userItem['password'];
                    $this->error('Wrong password');
                }
            }
        } else {
            $this->error('No specific username');
        }

    }


    public function logout() {
        if(is_login()){
            session('user', null);
            session('[destroy]');
            $this->success('退出成功！', U('index'));
        } else {
            $this->redirect('index');
        }
    }


}