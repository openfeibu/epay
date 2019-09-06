<?php

//ini_set('display_errors','On');
//error_reporting(E_ALL);



require '../includes/common.php';


		
		
		
require_once(SYSTEM_ROOT."cspay/epay.config.php");
require_once(SYSTEM_ROOT."cspay/epay_submit.class.php");

//echo $_POST['bankxinming'];
//echo $alipay_config['partner'];


//$datenow=date('Y-m-d',time());
//$timenow = strtotime($datenow);
//$yesterday = date('Y-m-d',$timenow);
$today = date("Y-m-d");

// 今日cqpay订单总额
$rs=$DB->query("SELECT * from pay_order where pid={$pid} and status=1 and type='cqpay' and (endtime between '{$today} 00:00:00' and '{$today} 23:59:59')");
$todaycqpay_money=0;
while($row = $rs->fetch())
{
	$todaycqpay_money+=$row['money'];
}

$today0timestamp = strtotime(date("Y-m-d"),time());
$rs=$DB->query("SELECT * from bc_outmoney where User_id={$pid} and   paytype='XFZF_DF_DZ'  and out_status<2  and out_id>{$today0timestamp}");
$todayD0cqpay_money=0;
$todayD0count=0;
//计算出今天直接代付的总额
while($row = $rs->fetch())
{
	$todayD0cqpay_money+=$row['out_money'];
	$todayD0count++;
}

$todayAllowDZOut=floor(((($todaycqpay_money-(($todayD0cqpay_money+$todayD0count)/0.8)/(1-$userrow['fee'])))*0.8-1)*(1-$userrow['fee'])*100)/100;

if ($todayAllowDZOut<0) {
	$todayAllowDZOut=0;
}
//echo "<br/>今天还可取现的总额是：".$todayAllowDZOut;

$todaymdstr=date("Ymd",time());
$todaymdstrymdhms0 = strtotime($$todaymdstr." 00:00:00");
$rs=$DB->query("SELECT * from bc_tradeday where ymd={$todaymdstr}");
$prevdaystrtmp = $todaymdstr;
while($row = $rs->fetch()){
	$prevdaystrtmp=strval($row['prevday']);
}
date_default_timezone_set("PRC");
if ($prevdaystrtmp == $todaymdstr){
	$prevdaystrtmp3 = strtotime($prevdaystrtmp." 00:00:00");
	$prevdaystrtmp2 = substr($prevdaystrtmp,0,4)."-".substr($prevdaystrtmp,4,2)."-".substr($prevdaystrtmp,6,2)." 00:00:00";
}else{
	$prevdaystrtmp3 = strtotime($prevdaystrtmp." 23:59:59");	
	$prevdaystrtmp2 = substr($prevdaystrtmp,0,4)."-".substr($prevdaystrtmp,4,2)."-".substr($prevdaystrtmp,6,2)." 23:59:59";	
}

$rs=$DB->query("SELECT * from pay_order where pid={$pid} and status=1 and type='cqpay' and (endtime between '2018-01-01 00:00:00' and '{$prevdaystrtmp2}')");
$fdsafl = "SELECT * from pay_order where pid={$pid} and status=1 and type='cqpay' and (endtime between '2018-01-01 00:00:00' and '{$prevdaystrtmp2}')";
//计算出前一个交易日及其之前的所有充值总额
$prevcqpay_money=0;
while($row = $rs->fetch())
{
	$prevcqpay_money+=$row['money'];

}


$rs=$DB->query("SELECT * from bc_outmoney where (User_id={$pid} and    out_status<2  and out_id<={$prevdaystrtmp3})  or (User_id={$pid} and    out_status<2 and paytype='XFZF_DF_NO' and out_id>{$todaymdstrymdhms0})");
//$rs=$DB->query("SELECT * from bc_outmoney where  User_id={$pid} and    out_status<2  and out_id<={$prevdaystrtmp3}");

$prevandbeforecqpay_money=0;
//计算出前一个交易日及其之前的提现总额
$todayD0count=1;
while($row = $rs->fetch())
{
	$prevandbeforecqpay_money+=$row['out_money'];
	$todayD0count++;
}


//计算可提现余额有多少
$couldoutmoney = floor((($prevcqpay_money*(1-$userrow['fee'])-$todayD0count-$prevandbeforecqpay_money))*100)/100;

if ($couldoutmoney<0) {
	$couldoutmoney=0;
}
//echo "<br/>今天还可取现的余额是：".$couldoutmoney;

if (floatval($_POST['fourpaymoney'])<=0){
	//如果今天需要代付的钱超过了允许范围【有人想hack】
	exit("<script language='javascript'>alert('转账失败,失败原因是：取现金额为0或负数，这是非法的。');history.go(-1);</script>");		
}
	
	
if ($_POST['paytype']=="XFZF_DF_DZ") {
	if (floatval($_POST['fourpaymoney'])>$todayAllowDZOut){
		//如果今天需要代付的钱超过了允许范围【有人想hack】
		exit("<script language='javascript'>alert('转账失败,失败原因是：取现金额超过了今天代付的剩余额度".print_r($html_text)."');history.go(-1);</script>");		
	}
}else{
	if (floatval($_POST['fourpaymoney'])>$couldoutmoney){
		//如果今天需要代付的钱超过了允许范围【有人想hack】
		exit("<script language='javascript'>alert('转账失败,失败原因是：取现金额超过了剩余额度');history.go(-1);</script>");		
	}
	
}

/**************************请求参数**************************/

	
$t=time();

//构造要请求的参数数组，无需改动
$parameter = array(
		"account_name" => $userrow['bankxinming'],
		"bank_card" => $userrow['bankcardid'],
		"bank_linked" => $userrow['bankopenid'],
		"bank_name"	=> $userrow['bankname'],
		"mch_id" => trim($alipay_config['partner']),
		"out_trade_no"	=> $t,		
		"service"	=> $_POST['paytype'],
		"trans_money"	=> $_POST['fourpaymoney']*100
		);
		
		
//建立请求
$alipaySubmit = new AlipaySubmit($alipay_config);
$html_text = $alipaySubmit->buildRequestOutMoneyCurl($parameter);
//echo $html_text;
//print_r($html_text);
//exit;

if ($html_text["ret_code"]=="SUCCESS"){
	if ($html_text["tradeStatus"]==2){
		//echo "转账失败,失败原因是：".$html_text["tradeMessage"];
		exit("<script language='javascript'>alert('转账失败,失败原因是：".print_r($html_text)."');history.go(-1);</script>");		
		//print_r($html_text);
		//exit;
	}

	if ($html_text["tradeStatus"]==1){
		//echo "转账处理中";

		//减少金额并插入bc_outmoney表轮寻处理结果
		$DB->query("update pay_user set already4paymoney=already4paymoney+{$_POST['fourpaymoney']} where id={$pid}");		
		$DB->query("insert into  `bc_outmoney` (`out_id`,`User_id`,`paytype`,`out_money`,`out_status`) values (".$t.",".$pid.",'".$_POST['paytype']."',".$_POST['fourpaymoney'].",0) ");				
		exit("<script language='javascript'>alert('已向结算中心发出转账申请，转账处理中，请耐心等候。当前结算中心返回：".$html_text["tradeMessage"]."');window.location.assign('/easypay/user/apply.php');</script>");				
		
	}

	if ($html_text["tradeStatus"]==3){
		//echo "转账成功";
		$DB->query("update pay_user set already4paymoney=already4paymoney+{$_POST['fourpaymoney']} where id={$pid}");		
		$DB->query("insert into  `bc_outmoney` (`out_id`,`User_id`,`paytype`,`out_money`,`out_status`) values (".$t.",".$pid.",'".$_POST['paytype']."',".$_POST['fourpaymoney'].",1) ");				
		exit("<script language='javascript'>alert('转账成功".$html_text["tradeMessage"]."');window.location.assign('/easypay/user/apply.php');</script>");				
		//减少金额
	}
}else{
		//print_r($html_text);
		//print_r($parameter);
		//exit;	
		exit("<script language='javascript'>alert('转账失败,失败原因是：".$html_text["ret_message"]."');history.go(-1);</script>");		

	
}

//echo "QR is ".$html_text["payinfo"];	
?>
