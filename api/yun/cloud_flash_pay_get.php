<?php
if(!isset($_REQUEST["channel"]) || !isset($_REQUEST["token"])){
    echo '{"success":"false","msg":"没有数据展示！"}';
    exit();
}
require_once __DIR__.DIRECTORY_SEPARATOR."./config.php";

$arr = null;
$arr["channel"] = $_REQUEST["channel"];
$token = create_token($arr,$config["key"]);
if($_REQUEST["token"] != $token){
    echo '{"success":"false","msg":"非法操作！"}';
    exit();
}

//先取1条
$sql = "SELECT `trade_no`,`mobile_url`,`money` FROM `pay_yspay` WHERE `mobile_url`='{$arr["channel"]}' AND `admin_status`='0' order by addtime desc  LIMIT 1";

$row = $pdo->query($sql)->fetchAll();

echo json_encode($row,JSON_UNESCAPED_UNICODE);