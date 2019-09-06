<?php
/**
 * 商户列表
 **/
exit();
include("../includes/common.php");


$memos = $_POST['memos'];
$status = $_POST['status'];
$pid = $_REQUEST['pid'];
$yesterday = $_REQUEST['time'];
$sqs = $DB->exec("update `pay_settle` set `memos` ='".$memos."',`status` =".$status." where `pid`=".$pid." and (time between '".$yesterday." 00:00:00' and '".$yesterday." 23:59:59')");
header('Location:slist.php');

//echo "update `pay_settle` set `memos` ='".$memos."',`status` =".$status." where `pid`=".$pid." and (time between '".$yesterday." 00:00:00' and '".$yesterday." 23:59:59')";
?>
      