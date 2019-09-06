<?php
require_once __DIR__.DIRECTORY_SEPARATOR."../config_base.php";
if(!session_id()){
    session_start();
}

error_reporting(0);
define('SYSTEM_ROOT',dirname(__FILE__).'/');
define('ROOT',dirname(SYSTEM_ROOT).'/');
date_default_timezone_set('Asia/Shanghai');
$date = date("Y-m-d H:i:s");

require_once SYSTEM_ROOT.'config.php';

//检测数据库安装
if(!defined('SQLITE') && (!$dbconfig['user'] || !$dbconfig['pwd'] || !$dbconfig['dbname'])){
    header('Content-type:text/html;charset=utf-8');
	echo '数据库未配置正确！';
    exit();
}

require_once __DIR__.DIRECTORY_SEPARATOR."api/init.php";
$DB->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$DB->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

//检测数据库中表
if($DB->query("select * from pay_admin where 1") == FALSE){
    header('Content-type:text/html;charset=utf-8');
    echo("<script language='javascript'>alert('检查到数据库未正确安装！请联系网站管理员。');</script>");
    exit();
}

$DB->query("set names utf8");

//查找网站配置信息
if(isset($_SESSION['admin_uuid']) && strlen($_SESSION['admin_uuid']) == 36){
    $sql = "SELECT * FROM `pay_admin` WHERE id = :id ;";
    $re = $DB->prepare($sql);
    $re->execute(["id" => $_SESSION['admin_id']]);
}elseif(isset($_SESSION['uuid'])){
    $sql = "SELECT * FROM `pay_admin` WHERE `uuid` = (SELECT `adminuuid` FROM `pay_user` WHERE `uuid` = :uuid );";
    $re = $DB->prepare($sql);
    $re->execute(["uuid" => $_SESSION['uuid']]);
}else{
    $sql = "SELECT * FROM `pay_admin` WHERE `admin_user` = 'root'";
    $re = $DB->prepare($sql);
    $re->execute();
}
//$sql = "SELECT * FROM `pay_admin` WHERE `id` = 0";
//$user_info = $DB->query($sql)->fetch();
$user_info = $re->fetch();
$conf = array();
foreach($user_info as $field => $value){
    if(!is_numeric($field)){
        $conf[$field] = $value;
    }
}
//unset($conf['admin_pwd']);

if(strpos($_SERVER['HTTP_USER_AGENT'],'QQ/') !== false && $conf['qqjump'] == 1){
    echo '<!DOCTYPE html>
<html>
 <head>
  <title>请使用浏览器打开</title>
  <script src="https://open.mobile.qq.com/sdk/qqapi.js?_bid=152"></script>
  <script type="text/javascript"> mqq.ui.openUrl({ target: 2,url: "'.$siteurl.'"}); </script>
 </head>
 <body>
 <h4>如果域名不跳转请手动将域名复制到浏览器打开</h4>
 </body>
</html>';
    exit;
}
if(!isset($conf['local_domain'])){
    $conf['local_domain'] = $_SERVER['HTTP_HOST'];
}
$password_hash = '!@#%!s!0';
require_once(SYSTEM_ROOT."alipay_core.function.php");
require_once(SYSTEM_ROOT."alipay_md5.function.php");
include_once(SYSTEM_ROOT."function.php");
include_once(SYSTEM_ROOT."member.php");

if($_SERVER['HTTP_HOST'] != $conf['local_domain']){
    include_once(SYSTEM_ROOT."txprotect.php");
}
