<?php
if(!isset($_REQUEST["trade_no"]) || !isset($_REQUEST["channel"]) || !isset($_REQUEST["url_back"]) || !isset($_REQUEST["token"])){
    echo '{"success":"false","msg":"没有数据展示！"}';
    exit();
}
require_once __DIR__.DIRECTORY_SEPARATOR."./config.php";

$arr = null;
$arr["trade_no"] = $_REQUEST["trade_no"];
$arr["channel"] = $_REQUEST["channel"];
$arr["url_back"] = $_REQUEST["url_back"];
$token = create_token($arr,$config["key"]);
if($_REQUEST["token"] != $token){
    echo '{"success":"false","msg":"非法操作！"}';
    exit();
}
$sql_channel = "SELECT `type` FROM `pay_channel` WHERE `id` = '{$arr["channel"]}'";
$row = $pdo->query($sql_channel)->fetch();
//判断是不是json
if(is_json($arr["url_back"])){
    $orderStr = json_decode($arr["url_back"],true);
    $urlcode = urlencode($orderStr["orderStr"]);
    $urlcode1 = '{"ddpay":"'.$urlcode.'"}';
    $urlcode = ',"ddpay":"'.$urlcode.'"}';
}
else if($row["type"] == "yunshanpay"){
    $urlcode = urlencode($arr["url_back"]);
    $urlcode1 = '{"yunshanpay":"'.$urlcode.'"}';
    $urlcode = ',"yunshanpay":"'.$urlcode.'"}';
}
else if($row["type"] == "yinshengpay"){
    $urlcode = urlencode($arr["url_back"]);
    $urlcode1 = '{"yinshengpay":"'.$urlcode.'"}';
    $urlcode = ',"yinshengpay":"'.$urlcode.'"}';
}

$sql_ys = "UPDATE `pay_yspay` SET `note2`= '".$urlcode1."',`admin_status`='1' WHERE `trade_no`='{$arr["trade_no"]}' AND `mobile_url`='{$arr["channel"]}' AND `admin_status`='0'";
$sql = "UPDATE `pay_order` SET `note2`= REPLACE ( note2, '}', '".$urlcode."' ),`admin_status`='1' WHERE `trade_no`='{$arr["trade_no"]}' AND `mobile_url`='{$arr["channel"]}' AND `admin_status`='0'";
$pdo->query($sql_ys);
if($pdo->query($sql)){
    echo '{"success":"true"}';
}
else{
    echo '{"success":"false"}';
}


function is_json($str){
    $return = null;
    if(is_null(json_decode($str,true))){
        $return = false;
    }
    else{
        $return = true;
    }
    return $return;
}
