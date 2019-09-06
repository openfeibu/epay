<?php
//header('Content-Type: text/html; charset=utf-8'); //不能改这个，否则会改变json为string.
//手机app映射的外网地址
//define('REQUEST_URL','http://weimifu.s1.natapp.cc/');
//define('SIGNKEY','123456');
//date_default_timezone_set("Asia/Hong_Kong");
//$debug = true;
if(!isset($trade_no) || $trade_no == '') exit();
require_once __DIR__.DIRECTORY_SEPARATOR."../../includes/api/init.php";
//require_once __DIR__.DIRECTORY_SEPARATOR."lunxun.php";
require_once __DIR__.DIRECTORY_SEPARATOR."libs/function.php";
require_once __DIR__.DIRECTORY_SEPARATOR."libs/person_api.php";

$mobile_url = \epay\person_api::get_mobile_url($trade_no);
define('REQUEST_URL',$mobile_url);
define('SIGNKEY',"");
date_default_timezone_set("Asia/Hong_Kong");
$debug = true;
if(!isset($_SESSION['mobile_url'])){
    $_SESSION['mobile_url'] = $mobile_url;
}
