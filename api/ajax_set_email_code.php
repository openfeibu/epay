<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . "../includes/api/init.php";
if (!session_id()) {
    session_start();
}
if(!isset($_SESSION["is_admin"])){
    exit();
}
$file = "../config/cache/".md5('QQ_Email_Code');
if(isset($_REQUEST["t"])&&$_REQUEST["t"]=="set"){
    if(!isset($_REQUEST["QQCode"]) || !isset($_REQUEST["QQEmail"]) || !isset($_REQUEST["QQName"]) || $_REQUEST["QQCode"] == "" || $_REQUEST["QQEmail"] == "" || $_REQUEST["QQName"] == ""){
        echo '{"success":"false","msg":"参数不完整或者为空！"}';
        exit();
    }
    $QQEmail = daddslashes($_REQUEST["QQEmail"]);
    $QQCode = daddslashes($_REQUEST["QQCode"]);
    $QQName = daddslashes($_REQUEST["QQName"]);
    $json["QQEmail"] = $QQEmail;
    $json["QQCode"] = $QQCode;
    $json["QQName"] = $QQName;
    $json["update_time"] = date("Y-m-d H:i:s");
    $data = serialize($json);
    if(file_put_contents($file,$data)){
        $return["success"] = 'true';
        $return["msg"] = '保存成功！';
        $return["QQEmail"] = $json["QQEmail"];
        $return["QQCode"] = hideStr($json["QQCode"],0,32);
        $return["QQName"] = $json["QQName"];
    } else {
        $return["success"] = 'false';
        $return["msg"] = '保存失败，请重试！';
    }
}
else if(isset($_REQUEST["t"])&&$_REQUEST["t"]=="get") {
    if(!file_exists($file)){
        $return["success"] = 'false';
        $return["msg"] = '未绑定邮箱';
    }
    else{
        $data = unserialize(file_get_contents($file));
        //其它情况直接读取缓存
        $return["success"] = 'true';
        $return["msg"] = '获取成功！';
        $return["QQEmail"] = $data["QQEmail"];
        $return["QQCode"] = hideStr($data["QQCode"], 0, 32);
        $return["QQName"] = $data["QQName"];
    }

}
echo json_encode($return, JSON_UNESCAPED_UNICODE);