<?php
ini_set('max_execution_time', '0');
set_time_limit(0); 

ini_set('display_errors','On');

error_reporting(E_ALL);
include("../includes/common.php");

$todays="20180417";


for ($x=1; $x<=259; $x++) {
	$todays = strval(date("Ymd",strtotime($todays." +1 day")));
	$todayisholiday = curl_get("http://tool.bitefu.net/jiari/?d=".$todays);
	$sds=$DB->exec("update `bc_tradeday` set `status`={$todayisholiday} where ymd={$todays}");
	echo "update `bc_tradeday` set `status`={$todayisholiday} where ymd={$todays}"."<br/>";
	usleep(100000); 
	//echo curl_get("http://tool.bitefu.net/jiari/?d=20180414")."<br/>";
  //$sds=$DB->exec("INSERT INTO `bc_tradeday` (`ymd`, `prevday`, `status`) VALUES ({$todays}, {$todays}, 0)");
  //echo "INSERT INTO `bc_tradeday` (`ymd`, `prevday`, `status`) VALUES ({$todays}, {$todays}, 0)<br/>";
}



?>