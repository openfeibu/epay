<?php
exit();
header("Content-type: text/html; charset=utf-8");
require_once 'config.php';


// foreach   ($_REQUEST as $key=>$value)
// {
 // echo   "Key: $key; Value: $value \n ";
// }

//签名sign验证，防止恶意提交
$sign=$_REQUEST['sign'];
$dt=$_REQUEST['dt'];
$mark=$_REQUEST['mark'];
$money=$_REQUEST['money'];
$no=$_REQUEST['no'];
$type=$_REQUEST['type'];

$newsign=md5($dt.$mark.$money.$no.$type.SIGNKEY);

if($newsign == $sign){
	//验证成功
	//写充值成功的操作，比如更改更新数据库给用户充值
	echo "success";
}else{
	echo "sign签名错误";
}
?>