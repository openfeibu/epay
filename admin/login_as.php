<?php
/**
 * 作为用户身份登录
**/
include("../includes/common.php");
require_once __DIR__."/../includes/api/init.php";
//require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/debug.php";
if(!$_SESSION['is_admin']){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
if(isset($_REQUEST['type']) && $_REQUEST['type'] == 'staff'){

}
$id = \epay\daddslashes($_REQUEST['id']);
$user = \epay\user::find_user($id);
\epay\start_session(300); //为安全，超时时间为300秒
$_SESSION['is_user'] = true;
$_SESSION['userid'] = $user['id'];
$_SESSION['uuid'] = $user['uuid'];
$_SESSION['user'] = $userrow['id'];                         //用户的id
$_SESSION['uid'] = $userrow['uid'];                         //代理商的id
$_SESSION['agentuuid'] = $userrow['agentuuid'];
header("Location: ../user/index.php");
?>
