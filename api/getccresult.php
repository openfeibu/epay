<?php
	require_once __DIR__.DIRECTORY_SEPARATOR."../config/config.php";
	require_once __DIR__ . DIRECTORY_SEPARATOR . "../config_base.php";
	require_once __DIR__ . DIRECTORY_SEPARATOR . "../includes/api/init.php";
	function request_post($url = '', $post_data = array()) {
		if (empty($url) || empty($post_data)) {
			return false;
		}

		$o = "";
		foreach ( $post_data as $k => $v )
		{
			$o.= "$k=" . urlencode( $v ). "&" ;
		}
		$post_data = substr($o,0,-1);

		$postUrl = $url;
		$curlPost = $post_data;
		$ch = curl_init();//初始化curl
		curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
		curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
		curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
		curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
		$data = curl_exec($ch);//运行curl
		curl_close($ch);

		return $data;
	}
	if(isset($_GET['access_token']) && isset($_GET['clusterId']) && isset($_GET['sender']) && isset($_GET['trade_no'])) {
		$data1 = array (
			'clusterId' => $_GET['clusterId'],
			'appkey' => '21603258',
			'imei' => '111111111111111',
			'type' => '0',
			'imsi' => '111111111111111',
			'sender' => $_GET['sender'],
			't' => time(),
			'access_token' => $_GET['access_token']
		);
		$content=request_post("https://redenvelop.laiwang.com/v2/redenvelop/send/doSend", $data1);
		$data1 = array (
			't' => time(),
			'imei' => '111111111111111',
			'appkey' => '21603258',
			'clusterId' => $_GET['clusterId'],
			'_c_' => '21750770',
			'_s_' => '98298c4fe8e400c5293255fccbdb8ab1',
			'imsi' => '111111111111111',
			'sender' => $_GET['sender'],
			'access_token' => $_GET['access_token']
		);
		$content1=request_post("https://redenvelop.laiwang.com/v2/redenvelop/pick/doPick", $data1);
		//$cc=substr($content1,strpos($content1,'"amount":"')+10);
		//$cc=substr($cc,0,strpos($cc,'","count":1'));
		$oc=json_decode($content1,true);
		$cc=$oc[rEClusterWrapperVO][pickedREFlow][amount];
		//echo $cc."<br />";
		//echo $_GET['trade_no'];
		//拿到金额和订单后，就可以自动更新订单
		$order = \epay\order::find($_GET['trade_no']);
		if ($order && $order['status'] != 1) { //业务逻辑更改，要针对状态9
			//开始比较金额
			if(strval(($order['money'] -$order['money2'])*100)== strval($cc*100)){
				$now = date("Y-m-d H:i:s");
				$sql = "update `pay_order` set `status` ='1',`endtime` ='{$now}', `buyer` = 'auto@cc' where `trade_no` = '{$_GET['trade_no']}';";
				$DB->query($sql);
				echo "<script>alert('充值成功');</script>";
			}else{
				//不相等直接报错
				echo "<script>alert('金额不对，请不要乱提交参数！');</script>";
			}
		}
	}else{
		echo "服务器接客太过繁忙，请稍候再扫！";
	}
?>
