<?php
	//不缓存
	header('X-Accel-Buffering: no');
	header('Cache-Control:no-cache,must-revalidate');
	header('Pragma:no-cache');
	header("Expires:0");
	require_once __DIR__.DIRECTORY_SEPARATOR."../config/config.php";
	require_once __DIR__ . DIRECTORY_SEPARATOR . "../config_base.php";
	require_once __DIR__ . DIRECTORY_SEPARATOR . "../includes/api/init.php";
		$suiji=rand(10001,99999);
	if(isset($_GET['uid'])) {
		$user=$_GET['user'];
		$result=$DB->query("select id from `pay_user` WHERE id='$user' AND FIND_IN_SET('$_GET[uid]',googleauth)>0")->fetch();
		if(!empty($result)){
		
		$result=$DB->query("update `pay_user` set note1='$suiji' WHERE id=$result[0]");	
		echo "1";exit;
		}
	}
	echo "0";

?>