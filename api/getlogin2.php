<?php
	//不缓存
	header('X-Accel-Buffering: no');
	header('Cache-Control:no-cache,must-revalidate');
	header('Pragma:no-cache');
	header("Expires:0");
	require_once __DIR__.DIRECTORY_SEPARATOR."../config/config.php";
	require_once __DIR__ . DIRECTORY_SEPARATOR . "../config_base.php";
	require_once __DIR__ . DIRECTORY_SEPARATOR . "../includes/api/init.php";
	require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/function.php";
	if(isset($_POST['userid'])) {
		$result=$DB->query("select id,uuid,agentuuid,uid,note2 from `pay_user` WHERE note2='$_POST[userid]'")->fetch();
		if(!empty($result)){
			\epay\start_session(300); //为安全，超时时间为300秒
		   $_SESSION['is_user'] = true;
            $_SESSION['userid'] = $result['id'];
            $_SESSION['uuid'] = $result['uuid'];
            $_SESSION['user'] = $result['id'];                         //用户的id
            $_SESSION['uid'] = $result['uid'];                         //代理商的id
            $_SESSION['agentuuid'] = $result['agentuuid'];

			//开始记录session表
			$exptime=time() + 300;
			$clientip=\epay\real_ip();
			$query = "replace into `pay_online` (`sid`, `username`, `ip`, `exptime`) VALUES  ('$_POST[userid]', '$result[id]', '$clientip', '$exptime')";
			$DB->query($query);
			echo "1";exit;
		}
	}
	echo "0";

?>