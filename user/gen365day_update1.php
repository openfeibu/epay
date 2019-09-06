<?php
ini_set('max_execution_time', '0');
set_time_limit(0); 

ini_set('display_errors','On');

error_reporting(E_ALL);
include("../includes/common.php");

$todays="20180417";

$listall=$DB->query("SELECT * FROM bc_tradeday where ymd>".$todays." order by ymd")->fetchAll();
foreach($listall as $userrows){
	$ymd=strval($userrows['ymd']);
	$status=$userrows['status'];
	if ($status==0){
		$yesterday = strval(date("Ymd",strtotime($ymd." -1 day")));
		$sds=$DB->exec("update `bc_tradeday` set `prevday`={$yesterday} where ymd={$ymd}");
	}
	
}



?>