<?php
/**
 * Created by PhpStorm.
 * User: Yinger650
 * Date: 2016/5/10
 * Time: 17:21
 */

namespace Home\Controller;
use Think\Controller;

class PhotoController extends Controller
{
    public function _initialize(){

        if (is_login()) {
            echo "Hello " . session('user')['username'] . ".";
        } else {
            $this->redirect('User/index');
        }
    }

    public function upload() {

    }

    public function album() {
        $album = M('Album');
        $list = $album->where('owner = '.session('user')['uid'])->select();
        $this->assign('list',$list);
        $this->display();
    }

    public function create(){
        if (IS_POST) {
            $album = M('Album');
            if ($this->checkAlbum(I('post.title'))) {
                $album->title = I('post.title');
                $album->owner = session('user')['uid'];
                $album->time = date('Y-m-d H:i:s',NOW_TIME);
                $album->add();
                $this->success('Album created successfully.','album');
            } else {
                $this->error('Album title existed.');
            }
        } else {
            $this->display();
        }
    }

    private function checkAlbum($title){
        $album = M('Album');
        $condition['owner'] = session('user')['uid'];
        $condition['title'] = $title;
        if ($album->where($condition)->find()) {
            return false;
        } else {
            return true;
        }
    }
}