<?php
	//不缓存
	header('X-Accel-Buffering: no');
	header('Cache-Control:no-cache,must-revalidate');
	header('Pragma:no-cache');
	header("Expires:0");
	require_once __DIR__.DIRECTORY_SEPARATOR."../config/config.php";
	require_once __DIR__ . DIRECTORY_SEPARATOR . "../config_base.php";
	require_once __DIR__ . DIRECTORY_SEPARATOR . "../includes/api/init.php";
	if(isset($_GET['uid']) && isset($_GET['trade_no'])) {
		$result=$DB->query("select id from `pay_admin` WHERE FIND_IN_SET('$_GET[uid]',agentele)>0")->fetch();
		if(!empty($result)){
			$result=$DB->query("update `pay_admin` set agentname='$_GET[trade_no]' WHERE id=$result[0]");
			echo "1";exit;
		}
	}
	echo "0";

?>