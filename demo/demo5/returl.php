	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<html>
	<head>

		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<meta http-equiv="Content-Language" content="zh-CN"/>
		<meta http-equiv="Expires" content="0" />        
		<meta http-equiv="Cache-Control" content="no-cache" />        
		<meta http-equiv="Pragma" content="no-cache" />
		<title>通联收银宝网关支付-商户接口范例-支付结果</title>
		<link href="css.css" rel="stylesheet" type="text/css" />
	</head>
	<body>
	<center> <font size=16><strong>支付结果</strong></font></center>

<?php
	require_once 'AppConfig.php';
	require_once 'AppUtil.php';
	
	$params = array();
	foreach($_POST as $key=>$val) {//动态遍历获取所有收到的参数,此步非常关键,因为收银宝以后可能会加字段,动态获取可以兼容由于收银宝加字段而引起的签名异常
		$params[$key] = $val;
	}
	if(count($params)<1){//如果参数为空,则不进行处理
		echo "error";
		exit();
	}
	$verifyvalue = "";
	if(AppUtil::ValidSign($params, AppConfig::APPKEY)){//验签成功
		//此处进行业务逻辑处理
		$verifyvalue ="报文验签成功";
		//echo "success";
	}
	else{
		$verifyvalue ="报文验签失败";
		//echo "erro";
	}
	
	$appid=$_POST["appid"];
	$cusid=$_POST['cusid'];
	$cusorderid=$_POST['cusorderid'];
	$trxcode=$_POST['trxcode'];
	$trxid=$_POST['trxid'];
	$trxamt=$_POST['trxamt'];
	$trxdate=$_POST['trxdate'];
	$paytime=$_POST['paytime'];
	$trxstatus=$_POST['trxstatus'];
	$trxreserved=$_POST['trxreserve'];	
	$sign=$_POST["sign"];
	
	$payvalue = "";
	if($payvalue=="0000")
		$payvalue = "支付成功";
	else
		$payvalue = "支付失败";
		
?>
	<div style="padding-left:40px;">			
			<div>验证结果：<?=$verifyvalue?></div>
			<div>支付结果：<?=$payvalue?></div>
			<hr/>
			<div>商户号：<?=$cusid ?> </div>
			<div>商户订单号：<?=$cusorderid ?> </div>
			<div>商户订单金额：<?=$trxamt ?></div>
			<div>网关支日期：<?=$trxdate ?> </div>
			<div>网关支时间：<?=$paytime ?> </div>

	</div>	
 </body>
</html>
	
	
