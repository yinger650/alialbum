<?php
/*****************************
*���ݿ�����
*****************************/
$conn = @mysql_connect("localhost","root","twinpack");
if (!$conn){
	die("Connection failure:" . mysql_error());
}

$userid = 'liyujun';
$picurl = 'hereisuser_reg';
mysql_select_db('FAMILYMART');
$sql = 'insert into pic_tbl'.
        '(userid, picurl)'.
        'values'.
        '("$userid", "$picurl")';
$retval = mysql_query($sql, $conn);
if(!$retval)
{
    die('could not enter data:' . mysql_error());
}
echo 'test finish\n';

//mysql_select_db("test", $conn);
//�ַ�ת��������
//mysql_query("set character set 'gbk'");
//д��
//mysql_query("set names 'gbk'");
?>
