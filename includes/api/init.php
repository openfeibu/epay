<?php
date_default_timezone_set("Asia/Hong_Kong");
require_once __DIR__.DIRECTORY_SEPARATOR."dbconnect2.php";
//require_once __DIR__.'/balance.php';
require_once __DIR__.DIRECTORY_SEPARATOR."../alipay_core.function.php";
require_once __DIR__.DIRECTORY_SEPARATOR."../alipay_md5.function.php";
require_once __DIR__.DIRECTORY_SEPARATOR."function_from_includes.php";
require_once __DIR__.DIRECTORY_SEPARATOR."function.php";
require_once __DIR__.DIRECTORY_SEPARATOR."autoload.php";

//初始化文件缓存
if(file_exists(__DIR__.DIRECTORY_SEPARATOR."../cache.class.php")){
    require_once __DIR__.DIRECTORY_SEPARATOR."../cache.class.php";
    $cache = new Cache(999999,'../config/cache/');
}

//为了安全记录所有用户输入
$getinfo=isset($_GET)?JSON_ENCODE($_GET):"";
$postinfo=isset($_POST)?JSON_ENCODE($_POST):"";

//取当前访问地址
if(isset($_SERVER)){
    if(isset($_SERVER[HTTP_X_FORWARDED_FOR])){
        $realip = $_SERVER[HTTP_X_FORWARDED_FOR];
    }elseif(isset($_SERVER[HTTP_CLIENT_IP])) {
        $realip = $_SERVER[HTTP_CLIENT_IP];
    }else{
        $realip = $_SERVER[REMOTE_ADDR];
    }
}else{
    //不允许就使用getenv获取
    if(getenv("HTTP_X_FORWARDED_FOR")){
        $realip = getenv( "HTTP_X_FORWARDED_FOR");
    }elseif(getenv("HTTP_CLIENT_IP")) {
        $realip = getenv("HTTP_CLIENT_IP");
    }else{
        $realip = getenv("REMOTE_ADDR");
    }
}
//访问页面
$pageURL = 'http';
if ($_SERVER["HTTPS"] == "on"){
    $pageURL .= "s";
}
$pageURL .= "://";
if ($_SERVER["SERVER_PORT"] != "80"){
    $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
}else{
    $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
}

$DB->query("INSERT INTO pay_logs(`get`,`post`,`url`,`ip`) VALUES('".$getinfo."','".$postinfo."','".$pageURL."','".$realip."')");