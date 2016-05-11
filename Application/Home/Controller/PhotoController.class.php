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
    /*
    public function _initialize(){

        if (is_login()) {
            echo "Hello " . session('user')['username'] . ".";
        } else {
            $this->redirect('User/index');
        }
    }
    */

    public function upload() {
        $this->display();
    }

    public function oss() {

        function gmt_iso8601($time) {
            $dtStr = date("c", $time);
            $mydatetime = new \DateTime($dtStr);
            $expiration = $mydatetime->format(\DateTime::ISO8601);
            $pos = strpos($expiration, '+');
            $expiration = substr($expiration, 0, $pos);
            return $expiration."Z";
        }
        if (!is_login()) exit('unauthorized!');
        $id = 'ulbXmLMev4vDnlFt';
        $key = 'W0MEvHv6GCMhElfrxGRxKr3c1Npvrm';
        $host = 'http://alialbum.oss-cn-qingdao.aliyuncs.com';
        $callbackUrl = U('callback','','',true);
        $callback_param = array('callbackUrl'=>$callbackUrl,
            'callbackBody'=>'filename=${object}&size=${size}&mimeType=${mimeType}&height=${imageInfo.height}&width=${imageInfo.width}',
            'callbackBodyType'=>"application/x-www-form-urlencoded",
            'uploadUser'=>session('user')['uid']);
        $callback_string = json_encode($callback_param);

        $base64_callback_body = base64_encode($callback_string);
        $now = time();
        $expire = 30;
        $end = $now + $expire;
        $expiration = gmt_iso8601($end);

        $dir = '/Public/img';

        //最大文件大小.用户可以自己设置
        $condition = array(0=>'content-length-range', 1=>0, 2=>1048576000);
        $conditions[] = $condition;

        //表示用户上传的数据,必须是以$dir开始, 不然上传会失败,这一步不是必须项,只是为了安全起见,防止用户通过policy上传到别人的目录
        $start = array(0=>'starts-with', 1=>'$key', 2=>$dir);
        $conditions[] = $start;


        $arr = array('expiration'=>$expiration,'conditions'=>$conditions);
        //echo json_encode($arr);
        //return;
        $policy = json_encode($arr);
        $base64_policy = base64_encode($policy);
        $string_to_sign = $base64_policy;
        $signature = base64_encode(hash_hmac('sha1', $string_to_sign, $key, true));

        $response = array();
        $response['accessid'] = $id;
        $response['host'] = $host;
        $response['policy'] = $base64_policy;
        $response['signature'] = $signature;
        $response['expire'] = $end;
        $response['callback'] = $base64_callback_body;
        //这个参数是设置用户上传指定的前缀
        $response['dir'] = $dir;
        echo json_encode($response);

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
