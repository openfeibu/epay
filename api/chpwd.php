<?php
	function create_password($pw_length = 16){
		$randpwd = '';
		for ($i = 0; $i < $pw_length; $i++)	{
			$randpwd .= chr(mt_rand(33, 126));
		}
		return $randpwd;
	}

	function get_password( $length = 16 )	{
		$str = substr(md5(time().create_password()), 0, $length);
		return $str;
	}

	//生成随机密码
	$p1=get_password();
	$p2=get_password(6);
	$p3=get_password(6);

	require_once __DIR__ . DIRECTORY_SEPARATOR . "../includes/api/init.php";

	//先取旧记录
	$rs = $DB->query("select `admin_user` from `pay_admin` WHERE id=0;")->fetch();


	//更新随机密码
	$sql = "UPDATE `pay_admin` set`admin_pwd`='$p1',`twoauth`='$p2',`admin_user`='$p3' WHERE id=0;";
	$re  = $DB->query($sql);
	$website_urls=""; //请自行配置网站域名

	//开始换目录
	shell_exec("mv ../".$rs[admin_user]." ../".$p3);

	//发送邮件
	$subject = "重要通知：".$website_urls.date("Y-m-d")." 网站有变化!"; //主题
	$message = "url:".$website_urls.$p3."\r\nu:".$p3."\r\npwd:".$p1."\r\nauth:".$p2; //正文
	$to[1]=""; //收件人1，如需加入多个，请加入此数组即可。推荐使用163邮箱。
	foreach($to as $v){
		@mail($v,$subject,$message);
	}
?>