
<?php
require_once __DIR__.DIRECTORY_SEPARATOR.'../includes/api/init.php';
require_once __DIR__.DIRECTORY_SEPARATOR.'../includes/api/debug.php';

	
	require_once './tonglian/AppUtil.php';
	//file_put_contents("../config/cache/jieguo.txt",json_encode( $_REQUEST, JSON_UNESCAPED_UNICODE ));

      //$shujur=json_encode($_REQUEST);
	 // $shujus=substr($shujur,92,28);
	//file_put_contents("../config/cache/jieguo2.txt",$shujur);
	
	
	//$tonglianhao=$params["chnltrxid"];
	$rema=$_REQUEST['trxreserve'];
	$remark=substr($rema,0,-3);
	
	$outtrxid=$_REQUEST['oid'];
	//$tonglianhao=$_GET['trxid'];
	$shanghu=$_REQUEST['appid'];
	$sql = "SELECT * FROM `pay_channel` WHERE `id` = '{$remark}' and `type` = 'tonglian' and `status` = '1' ";
$userrow = $DB->query($sql)->fetch();
$shhao=$userrow["id"];
$now=date("Y-m-d H:i:s");
	$sign = $_REQUEST['sign'];
		//$params['key'] = $userrow["private_key"];
		$zhanghao = $userrow["public_key"];

		$jiami=$userrow["body"];
		
if($shanghu==$jiami)
	{
	
	 
		
			$sql = "update `pay_order` set  `status` ='1',`endtime` ='{$now}', `mobile_url` ='{$shhao}',`buyer` = '{$zhanghao}' where `trade_no`='{$outtrxid}'";

$DB->query($sql);
		echo "success";
	    }
	
	else{
		
		echo "erro";
	}
	

	

		
?>

	
