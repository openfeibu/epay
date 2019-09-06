<?php
include("../includes/common.php");
$title = $conf['web_name'];
//include './head.php';
//if($islogin==1){}else exit("<script language='javascript'>window.location.href='./login.php';</script>");
var_dump($islogin);
var_dump($isLogin2);
//if(isset($_SESSION['userid'])){
//    header("Location: index.php");
//}
if(isset($_REQUEST['id'])){
    $uid = $_REQUEST['id'];
}
$date = date("Y-m-d H:i:s");
$city = '';
$clientip = $_SERVER['REMOTE_ADDR'];
$DB->query("insert into `panel_log` (`uid`,`type`,`date`,`city`,`data`) values ('".$user."','登录用户中心','".$date."','".$city."','".$clientip."')");
$session = md5($user.$pass.$password_hash);
$expiretime = time() + 604800;
$token = authcode("{$user}\t{$session}\t{$expiretime}",'ENCODE',SYS_KEY);
setcookie("user_token",$token,time() + 604800);
$_SESSION['userid'] = $user;
setcookie("p_id",$user);
@header('Content-Type: text/html; charset=UTF-8');
//exit("<script language='javascript'>alert('登录用户中心成功！');window.location.href='./';</script>");
