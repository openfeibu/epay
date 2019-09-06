<?php
require_once __DIR__.DIRECTORY_SEPARATOR."../api/init.php";
//file_put_contents('../../config/cache/notify.txt',serialize($_REQUEST));
$userId = $_REQUEST["userId"];
$orderId = $_REQUEST["orderId"];
$userOrder = $_REQUEST["userOrder"];
$number = $_REQUEST["number"];
$merPriv = $_REQUEST["merPriv"];
$remark = $_REQUEST["remark"];
$date = $_REQUEST["date"];
$resultCode = $_REQUEST["resultCode"];
$resultMsg = $_REQUEST["resultMsg"];
$sn = "z1KQ5k";
$appID = $_REQUEST["appID"];
$chkValue = $_REQUEST["chkValue"];
$md55 = md5($userId."|".$orderId."|".$userOrder."|".$number."|".$merPriv."|".$remark."|".$date."|".$resultCode."|".$resultMsg."|".$appID."|".$chkValue."|".$sn);

if($resultCode == "0000" && $md55 == $chkValue){
    //写入库，订单完成
    $now = date("Y-m-d H:i;s");
    $sql = "update `pay_order` set `status` ='1',`endtime` ='{$now}', `buyer` = '{$orderId}@{$userId}@{$number}' where `trade_no`='{$userOrder}';";
    if($DB->query($sql)){
        exit("success");
    };
}