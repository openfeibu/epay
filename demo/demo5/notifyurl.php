
<?php
	require_once 'AppConfig.php';
	require_once 'AppUtil.php';
	
	$params = array();
	foreach($_REQUEST as $key=>$val) {//动态遍历获取所有收到的参数,此步非常关键,因为收银宝以后可能会加字段,动态获取可以兼容由于收银宝加字段而引起的签名异常
		$params[$key] = $val;
	}

	
	$appid=$_REQUEST["appid"];
	$cusid=$_REQUEST['cusid'];
	$outtrxid=$_REQUEST['outtrxid'];
	$trxcode=$_REQUEST['trxcode'];
	$trxid=$_REQUEST['trxid'];
	$trxamt=$_REQUEST['trxamt'];
	$trxdate=$_REQUEST['trxdate'];
	$paytime=$_REQUEST['paytime'];
	$chnltrxid=$_REQUEST['chnltrxid'];
	$trxstatus=$_REQUEST['trxstatus'];
	$termno=$_REQUEST['termno'];	
	$termbatchid=$_REQUEST['termbatchid'];
	$termtraceno=$_REQUEST['termtraceno'];
	$termauthno=$_REQUEST['termauthno'];
	$termrefnum=$_REQUEST['termrefnum'];
	$trxreserved=$_REQUEST['trxreserved'];
	$srctrxid=$_REQUEST['srctrxid'];
	$cusorderid=$_REQUEST['cusorderid'];
	$acct=$_REQUEST['acct'];
	$fee=$_REQUEST['fee'];
	$signtype=$_REQUEST['signtype'];
	$sign=$_REQUEST["sign"];
	$chuanc=$appid."/".$cusid."/".$outtrxid."/".$trxcode."/".$trxid."/".$trxamt."/".$trxdate."/".$paytime."/".$chnltrxid."/".$trxstatus."/".$termno."/".$termbatchid."/".$termtraceno."/".$termauthno."/".$termrefnum."/".$trxreserved."/".$srctrxid."/".$cusorderid."/".$acct."/".$fee."/".$signtype."/".$sign."/";
	file_put_contents("jieguo.txt", $chuanc , FILE_APPEND);
	if(AppUtil::ValidSign($params, AppConfig::APPKEY))
	{
	if($trxstatus=="0000")
	    {
		//此处进行业务逻辑处理
		
		echo "success";
	    }
	}
	else{
		
		echo "erro";
	}
	

		
?>

	
