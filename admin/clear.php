<?php
include("../includes/common.php");
if(!$_SESSION['is_admin']){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
if(!isset($_REQUEST["action"]) || $_REQUEST["action"]!="clear"){
    exit("<script language='javascript'>window.location.href='./index.php';</script>");
}
if($_REQUEST["action"]=="clear"){
		$userss=$_SESSION['admin_user'];
		$sql2r = "UPDATE `pay_admin` SET `epayid` = '' WHERE `admin_user` = '$userss'";
		$DB->query($sql2r);
}
?>