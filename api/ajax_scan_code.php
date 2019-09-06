<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . "../includes/api/init.php";
if(!isset($_REQUEST["token"])){
    exit();
}
$token = $_REQUEST["token"];

//设置是否为扫码模式
if($token == "set_scan_code"){
    $scan_code = $_REQUEST["scan_code"];
    $json["scan_code"] = $scan_code;
    $json["update_time"] = date("Y-m-d H:i:s");
    $cache->put('scan_code', $json);
    echo '{"error":"1"}';
}
else if($token == "get_scan_code"){
    //设置是否为扫码模式

    $data=$cache->get('scan_code');
    $scan_code = false;
    if (empty($cache) || !isset($data["scan_code"])) {
        //如果无缓存就
        $json["scan_code"] = $scan_code;
        $json["update_time"] = date("Y-m-d H:i:s");
        $cache->put('scan_code', $json);
    } else {
        //其它情况直接读取缓存
        $scan_code     = $data['scan_code'];
    }
    echo $scan_code;
}