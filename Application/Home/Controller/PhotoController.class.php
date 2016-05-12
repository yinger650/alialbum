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
        $album = M('Album');
        $list = $album->where('owner = '.session('user')['uid'])->select();
        $this->assign('album',$list);
        $this->display();
    }

    public function callback(){

        // 1.获取OSS的签名header和公钥url header
        $authorizationBase64 = "";
        $pubKeyUrlBase64 = "";
        /*
         * 注意：如果要使用HTTP_AUTHORIZATION头，你需要先在apache或者nginx中设置rewrite，以apache为例，修改
         * 配置文件/etc/httpd/conf/httpd.conf(以你的apache安装路径为准)，在DirectoryIndex index.php这行下面增加以下两行
            RewriteEngine On
            RewriteRule .* - [env=HTTP_AUTHORIZATION:%{HTTP:Authorization},last]
         * */
        if (isset($_SERVER['HTTP_AUTHORIZATION']))
        {
            $authorizationBase64 = $_SERVER['HTTP_AUTHORIZATION'];
        }
        if (isset($_SERVER['HTTP_X_OSS_PUB_KEY_URL']))
        {
            $pubKeyUrlBase64 = $_SERVER['HTTP_X_OSS_PUB_KEY_URL'];
        }

        if ($authorizationBase64 == '' || $pubKeyUrlBase64 == '')
        {
            header("http/1.1 403 Forbidden");
            exit();
        }

    // 2.获取OSS的签名
        $authorization = base64_decode($authorizationBase64);

    // 3.获取公钥
        $pubKeyUrl = base64_decode($pubKeyUrlBase64);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $pubKeyUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        $pubKey = curl_exec($ch);
        if ($pubKey == "")
        {
            //header("http/1.1 403 Forbidden");
            exit();
        }

    // 4.获取回调body
        $body = file_get_contents('php://input');
        $bodyArray = explode("&", $body);
        $callbackData = array();
        foreach ($bodyArray as $item) {
            $itemData = explode("=",$item);
            $callbackData[$itemData[0]] = $itemData[1];
        }
        $photo = M('Photo');
        $album = M('Album');
        $condition['owner'] = $callbackData['uploader'];
        $condition['title'] = $callbackData['album'];
        $aid = $album->where($condition)->find();

        $data['oss_url'] = $callbackData['filename'];
        $data['uploader'] = $callbackData['uploader'];
        $data['time'] = date('Y-m-d H:i:s',NOW_TIME);
        $data['album'] = $aid['aid'];
        $data['description'] = $callbackData['description'];
        $data['public'] = ($callbackData['public']  == "true");
        $status = $photo->data($data)->add();


            // 5.拼接待签名字符串
        $authStr = '';
        $path = $_SERVER['REQUEST_URI'];
        $pos = strpos($path, '?');
        if ($pos === false)
        {
            $authStr = urldecode($path)."\n".$body;
        }
        else
        {
            $authStr = urldecode(substr($path, 0, $pos)).substr($path, $pos, strlen($path) - $pos)."\n".$body;
        }

    // 6.验证签名
        $ok = openssl_verify($authStr, $authorization, $pubKey, OPENSSL_ALGO_MD5);
        if ($ok == 1)
        {
            header("Content-Type: application/json");
            $data = array("Status"=>$status,
                          "albumID"=>$aid['aid'],
                          "file" => $callbackData['filename'],
                          "public" => $callbackData['public'],
                );
            echo json_encode($data);
        }
        else
        {
            //header("http/1.1 403 Forbidden");
            exit();
        }
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
        $host = 'https://alialbum.oss-cn-qingdao.aliyuncs.com';
        $callbackUrl = U('callback','','',true);
        $callback_param = array('callbackUrl'=>$callbackUrl,
            'callbackBody'=>'filename=${object}&size=${size}&mimeType=${mimeType}&height=${imageInfo.height}&width=${imageInfo.width}&uploader='
                            .session('user')['uid'].'&album=${album}&description=${description}&public=${public}',
            'callbackBodyType'=>"application/x-www-form-urlencoded");
        $callback_string = json_encode($callback_param);

        $base64_callback_body = base64_encode($callback_string);
        $now = time();
        $expire = 30;
        $end = $now + $expire;
        $expiration = gmt_iso8601($end);

        $dir = '';

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
