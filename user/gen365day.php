<?php
ini_set('display_errors','On');

error_reporting(E_ALL);
include("../includes/common.php");

$todays="20181229";


for ($x=1; $x<=3; $x++) {
	$todays = strval(date("Ymd",strtotime($todays." +1 day")));

  $sds=$DB->exec("INSERT INTO `bc_tradeday` (`ymd`, `prevday`, `status`) VALUES ({$todays}, {$todays}, 0)");
  echo "INSERT INTO `bc_tradeday` (`ymd`, `prevday`, `status`) VALUES ({$todays}, {$todays}, 0)<br/>";
}



?>