<?php
if(!isset($_REQUEST["trade_no"]) || !isset($_REQUEST["channel"]) || !isset($_REQUEST["money"]) || !isset($_REQUEST["token"])){
    echo '{"success":"false","msg":"没有数据展示！"}';
    exit();
}
require_once __DIR__.DIRECTORY_SEPARATOR."./config.php";

$arr = null;
$arr["trade_no"] = $_REQUEST["trade_no"];
$arr["channel"] = $_REQUEST["channel"];
$arr["money"] = $_REQUEST["money"];
$token = create_token($arr,$config["key"]);
if($_REQUEST["token"] != $token){
    echo '{"success":"false","msg":"非法操作！"}';
    exit();
}
$sql = "SELECT `note2` FROM `pay_yspay` WHERE `trade_no`='{$arr["trade_no"]}' AND `mobile_url`='{$arr["channel"]}' AND `money`='{$arr["money"]}' AND `admin_status`='1'";
$row = $pdo->query($sql)->fetch();
$row1 = json_decode($row["note2"],true);

if(isset($row1["ddpay"])){
    $row["success"] = "true";
    $row["msg"] = "ddpay";
}
else if(isset($row1["yunshanpay"])){
    $row["success"] = "true";
    $row["msg"] = "yunshanpay";
    $row["remarks"] = urldecode($row1["yunshanpay"]);
}
else if(isset($row1["yinshengpay"])){
    $row["success"] = "true";
    $row["msg"] = "yinshengpay";
    $row["remarks"] = urldecode($row1["yinshengpay"]);
}
else{
    $row["success"] = "false";
}
echo json_encode($row,JSON_UNESCAPED_UNICODE);
//$sql = "DELETE FROM `pay_yspay` WHERE `trade_no`='{$arr["trade_no"]}' AND `mobile_url`='{$arr["channel"]}' AND `money`='{$arr["money"]}' AND `admin_status`='1'";
//$row = $pdo->query($sql);