<?php
header("Content-Type: application/json; Charset=utf-8");
$log_file = __DIR__.DIRECTORY_SEPARATOR."update.log.php";

//日志
require_once __DIR__.DIRECTORY_SEPARATOR."../../includes/api/function.php";
$log = $_REQUEST;
$str = json_encode($log,JSON_UNESCAPED_UNICODE);
$str = date("Y-m-d H:i:s : ").\epay\real_ip().$str.PHP_EOL;
file_put_contents($log_file,$str,FILE_APPEND);

if(isset($_REQUEST['version']) && isset($_REQUEST['version2'])){
    $version = $_REQUEST['version'];
    $version2 = $_REQUEST['version2'];
}else{
    exit();
}

$version_code = 102;//版本号，最新版的版本号
$version_name = "1.0.2";//APP显示给客户的版本号

$result = array();
if($version == $version_name){
    $result['code'] = 0;
    $result['msg'] = "此版本为最新版本";
}else{
    $result['code'] = 1;
    $result['msg'] = "有新版本，最新版本为：".$version_name;
    $result['version_code'] = $version_code;
    $result['version_name'] = $version_name;
    switch($version2){
        case 'test':
            $result['url'] = "https://www.domain.com/app/update/PayHelper1.0.2.apk";
            break;
        case 'domain':
            $result['url'] = "https://www.domain.com/app/update/PayHelper1.0.2.apk";
            break;
        default:
            $result['url'] = "https://www.domain.com/app/update/PayHelper1.0.2.apk";
            break;
    }
}

echo json_encode($result,JSON_UNESCAPED_UNICODE);
