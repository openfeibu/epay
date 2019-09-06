<?php
	if(isset($_GET['userid']) && isset($_GET['mark']) && isset($_GET['money']) && isset($_GET['url'])) {
		//取相关账单
		$opts = array(
			'http'=>array(
				'method'=>"GET",
				'timeout'=>10,//单位秒
			)
		);
		//要存在三个参数，才进行处理
		$msg=file_get_contents($_GET['url']."/gen?$_GET[userid],$_GET[mark],$_GET[money]", false, stream_context_create($opts));
		$msg=empty($msg)?"":$msg;
		echo $msg;
	}else{
		echo "服务器接客太过繁忙，请稍候再扫！";
	}
?>
