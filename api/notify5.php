
<?php
require_once __DIR__.DIRECTORY_SEPARATOR.'../includes/api/init.php';
require_once __DIR__.DIRECTORY_SEPARATOR.'../includes/api/debug.php';

	
	require_once './tonglian/AppUtil.php';
	
	$params = array();
	foreach($_REQUEST as $key=>$val) {//动态遍历获取所有收到的参数,此步非常关键,因为收银宝以后可能会加字段,动态获取可以兼容由于收银宝加字段而引起的签名异常
		$params[$key] = $val;
	}

	//file_put_contents("../config/cache/jieguo.txt",json_encode( $_REQUEST, JSON_UNESCAPED_UNICODE ));
	$remark=$_REQUEST['trxreserved'];
	$pid="99@".$remark;
	$outtrxid=$_REQUEST['outtrxid'];
	$trxstatus=$_REQUEST['trxstatus'];
	$acct=$_REQUEST['acct'];
	$sql = "SELECT * FROM `pay_channel` WHERE `appid` = '{$pid}' and `type` = 'tonglian'";
$userrow = $DB->query($sql)->fetch();
$shhao=$userrow["id"];
$now=date("Y-m-d H:i:s");
	$sign = $_REQUEST['sign'];
	unset($params['sign']);
		$params['key'] = $userrow["private_key"];
		$zhanghao = $userrow["public_key"];
		ksort($params);
		$buff = "";
		foreach ($params as $k => $v)
		{
			if($v != "" && !is_array($v)){
				$buff .= $k . "=" . $v . "&";
			}
		}
		
		$buff = trim($buff, "&");
		$jiami=strtoupper(md5($buff));
		
if($sign==$jiami)
	{
	if($trxstatus=="0000")
	    {
		
			$sql = "update `pay_order` set `status` ='1',`endtime` ='{$now}', `mobile_url` ='{$shhao}',`buyer` = '{$zhanghao}' where `trade_no`='{$outtrxid}'";

$DB->query($sql);
		echo "success";
	    }
	}
	else{
		
		echo "erro";
	}
	

	

		
?>

	
