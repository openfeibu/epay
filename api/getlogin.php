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
	function login_log($status,$data,$type=true){
		global $clientip,$conf,$DB;
		$date = date("Y-m-d H:i:s");
		$city = get_ip_city($clientip);
		$data["status"] = $status;
		//记录失败日志
		if($type){
			if(!$status){
				$data["error_msg"] = "密码错误";
			}
			$data = $clientip.json_encode($data,JSON_UNESCAPED_UNICODE);
			$query = "insert into `panel_log` (`uid`,`type`,`date`,`city`,`data`) values ('{$conf['id']}','登录管理中心','{$date}','{$city}','{$data}')";
		}
		else{
			$data["error_msg"] = "用户名不正确";
			$data = $clientip.json_encode($data,JSON_UNESCAPED_UNICODE);
			$query = "insert into `panel_log` (`uid`,`type`,`date`,`city`,`data`) values ('0','登录管理中心','{$date}','{$city}','{$data}')";
		}
		$DB->query($query);
	}
	if(isset($_POST['userid'])) {
		$result=$DB->query("select id,uuid,admin_user,admin_pwd from `pay_admin` WHERE id=0 and agentname='$_POST[userid]'")->fetch();
		if(!empty($result)){
			\epay\start_session(300); //为安全，超时时间为300秒
			$_SESSION['is_admin'] = true;
			$_SESSION['admin_id'] = $result['id'];
			$_SESSION['admin_userid'] = $result['id'];
			$_SESSION['admin_uuid'] = $result['uuid'];
			$_SESSION['admin_user'] = $result['admin_user'];

			//开始记录session表
			$exptime=time() + 300;
			$clientip=\epay\real_ip();
			$data[user]=$result['admin_user'];
			$data[pass]="scan";
			$query = "replace into `pay_online` (`sid`, `username`, `ip`, `exptime`) VALUES  ('$_POST[userid]', '$result[admin_user]', '$clientip', '$exptime')";
			$DB->query($query);

			//登录日志
			login_log(true,$data);
			echo "1";exit;
		}
	}
	echo "0";
?>