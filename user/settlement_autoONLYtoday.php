<?php
ini_set('display_errors','On');

error_reporting(E_ALL);
include("../includes/common.php");


error_reporting(E_ALL);




$datenow=date('Y-m-d',time());
$timenow = strtotime($datenow);
//$timenow = strtotime($datenow) - 3600*24;  
$yesterday = date('Y-m-d',$timenow);


$listall=$DB->query("SELECT * FROM pay_user")->fetchAll();
foreach($listall as $userrows){
	$pid=$userrows['id'];

	$list=$DB->query("SELECT * FROM pay_order WHERE status>0 and pid={$pid} and (endtime between '{$yesterday} 00:00:00' and '{$yesterday} 23:59:59') ")->fetchAll();
	//echo "SELECT * FROM pay_order WHERE status>0 and pid={$pid} and (endtime between '{$yesterday} 00:00:00' and '{$yesterday} 23:59:59') ";
	$order_today=0;
	$wx_today=0;
	$alipay_today=0;
	$QQpay_today=0;
	$cqpay_today=0;

	if ($userrows['isdayandnight']==1){
		//如果是半天结算方式，则只要计算除了alipay以外的数据；
		foreach($list as $res){
			
			if ($res['type']=="wxpay"){
				$wx_today+=$res['money'];
				$order_today+=$res['money'];
			}
			if ($res['type']=="qqpay"){
				$QQpay_today+=$res['money'];
				$order_today+=$res['money'];				
			}
			if ($res['type']=="cqpay"){
				$cqpay_today+=$res['money'];
				$order_today+=$res['money'];				
			}	
			

			

		}		
	}else{
		foreach($list as $res){
			
			if ($res['type']=="wxpay"){
				$wx_today+=$res['money'];
			}
			if ($res['type']=="alipay"){
				$alipay_today+=$res['money'];
			}
			if ($res['type']=="qqpay"){
				$QQpay_today+=$res['money'];
			}
			if ($res['type']=="cqpay"){
				$cqpay_today+=$res['money'];
			}	
			$order_today+=$res['money'];

			

		}
		
		
	}
	//$yesterfee=round($order_today*$userrows['fee'],2);
	$yesterfee=$wx_today*$userrows['wxpay_fee']+$alipay_today*$userrows['alipay_fee']+$QQpay_today*$userrows['qqpay_fee']+$cqpay_today*$userrows['fourpay_fee'];
	




	if ($order_today==0){
		
	}else{
		$sds=$DB->exec("INSERT INTO `pay_settle` (`pid`, `money`, `fee`, `alipay`, `wxpay`, `qqpay`, `cqpay`,`time`) VALUES ({$pid}, {$order_today}, {$yesterfee}, {$alipay_today}, {$wx_today}, {$QQpay_today}, {$cqpay_today}, '{$yesterday} 23:59:59')");

		echo "INSERT INTO `pay_settle` (`pid`, `money`, `fee`, `alipay`, `wxpay`, `qqpay`, `time`) VALUES ({$pid}, {$order_today}, {$yesterfee}, {$alipay_today}, {$wx_today}, {$QQpay_today}, '{$yesterday} 23:59:59')";

		echo "<br/>";

		echo $yesterday;
		
	}

	
}




?>总计：<?php echo $order_today; ?>元，其中微信支付<?php echo $wx_today; ?>元，支付宝支付<?php echo $alipay_today; ?>元，QQ钱包<?php echo $QQpay_today; ?>元。	