<?php
    $txt = $_GET['variableName'];
    echo $_GET['variableName'];
    $pic = mysql_real_escape_string('http://'.'ship01.oss-cn-shanghai.aliyuncs.com/'.$txt);
//    $pic = mysql_real_escape_string('http://'.urlencode('ship01.oss-cn-shanghai.aliyuncs.com/').$txt);
    echo $pic;

 //   $myfile = fopen("newfile.txt", "a") or die("Unable to open file!");
 //   fwrite($myfile, 'stevenLIYUJUN\n');
 //   fwrite($myfile, $txt);
 //   fclose($myfile);

     
    
//    $content = file_get_contents('http://ship01.oss-cn-shanghai.aliyuncs.com/'.rawurlencode($txt));
//    echo 'http://ship01.oss-cn-shanghai.aliyuncs.com/';
 //   echo rawurlencode($txt);
 //   echo $content;


    $conn = mysql_connect("localhost","root","twinpack");
    if (!$conn)
    {
        echo 'kkkkkkkkk';
        die('Could not connect haha: ' . mysql_error());
    }

    echo '%%%here';
    // echo $pic;
    // $pic = urlencode('warehouse/[PT][体育][136030].库里15-16赛季402三分原声集锦.mp4.torrent');
    // $pic = 'warehouse%2F%5BPT%5D%5B%E4%BD%93%E8%82%B2%5D%5B136030%5D.%E5%BA%93%E9%87%8C15-16%E8%B5%9B%E5%AD%A3402%E4%B8%89%E5%88%86%E5%8E%9F%E5%A3%B0%E9%9B%86%E9%94%A6.mp4.torrent';
    mysql_select_db("FAMILYMART", $conn);
    mysql_query("INSERT INTO pic_tbl (userid, picurl) VALUES ('Pic', '$pic')");
    mysql_close($conn);


    
?>
