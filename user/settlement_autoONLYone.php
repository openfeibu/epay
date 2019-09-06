<?php
ini_set('display_errors','On');

error_reporting(E_ALL);
include("../includes/common.php");













$datenow=date('Y-m-d',time());
$timenow = strtotime($datenow) - 3600*24;  
$yesterday = date('Y-m-d',$timenow);

$list=$DB->query("SELECT * FROM pay_order WHERE status>0 and pid={$pid} and (endtime between '{$yesterday} 00:00:00' and '{$yesterday} 23:59:59') ")->fetchAll();
//echo "SELECT * FROM pay_order WHERE status>0 and pid={$pid} and (endtime between '{$yesterday} 00:00:00' and '{$yesterday} 23:59:59') ";
$order_today=0;
$wx_today=0;
$alipay_today=0;
$QQpay_today=0;

foreach($list as $res){
	
	if ($res['type']=="微信支付"){
		$wx_today+=$res['money'];
	}
	if ($res['type']=="支付宝支付"){
		$alipay_today+=$res['money'];
	}
	if ($res['type']=="QQ钱包"){
		$QQpay_today+=$res['money'];
	}	
	$order_today+=$res['money'];

	

}
$yesterfee=round($order_today*$conf['settle_fee']/100,2);



$sqs=$DB->exec("delete from  `pay_settle` where  time between '{$yesterday} 00:00:00' and '{$yesterday} 23:59:59'");

echo "delete from  `pay_settle` where  time between '{$yesterday} 00:00:00' and '{$yesterday} 23:59:59'";

echo "<br/>";

$sds=$DB->exec("INSERT INTO `pay_settle` (`pid`, `money`, `fee`, `alipay`, `wxpay`, `qqpay`, `time`) VALUES ({$pid}, {$order_today}, {$yesterfee}, {$alipay_today}, {$wx_today}, {$QQpay_today}, '{$yesterday}')");

echo "INSERT INTO `pay_settle` (`pid`, `money`, `fee`, `alipay`, `wxpay`, `qqpay`, `time`) VALUES ({$pid}, {$order_today}, {$yesterfee}, {$alipay_today}, {$wx_today}, {$QQpay_today}, '{$yesterday}')";

echo "<br/>";

echo $yesterday;

?>总计：<?php echo $order_today; ?>元，其中微信支付<?php echo $wx_today; ?>元，支付宝支付<?php echo $alipay_today; ?>元，QQ钱包<?php echo $QQpay_today; ?>元。	