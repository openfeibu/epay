<?php
/**
 * 手工修正系统
 **/
include("../includes/common.php");
if(!$_SESSION['userid']){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
if($_SESSION['agentuuid'] != '1'){
    exit("<script language='javascript'>window.history.go(-1) ;</script>");
}
$userid = $_SESSION['userid'];
//非结算算用，退出。
if($userrow['type'] != '2'){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}

//加入对手工订单的监控
if($_REQUEST["action"]=="clear"){
    $sql2r = "UPDATE `pay_user` SET `note1` = '' WHERE `id` = '$userid'";
    $DB->query($sql2r);
    exit();
}
include("../includes/log.php");
$status = daddslashes($_REQUEST['status']);
$trade_no = daddslashes($_REQUEST['trade_no']);
//$endtime = daddslashes($_REQUEST['endtime']);
$endtime = date("Y-m-d H:i:s"); //完成时间取当前时间，旧方法由用户自定不接受
$buyer = date("Y-m-d H:i:s")."@".real_ip()."@agent@manual";
$twoauth = daddslashes($_REQUEST['twoauth']);
$sms_key = daddslashes($_REQUEST['sms_key']);
$conf = $DB->query("SELECT `cash_pwd` FROM `pay_user` WHERE `id`='{$_SESSION["user"]}'")->fetch();

//判断是不是开启短信验证
if($agent_sms_switch == true){
    $sql_phone = "SELECT `com_phone` FROM `pay_user` WHERE `id`='{$_SESSION["user"]}'";
    $row = $DB->query($sql_phone)->fetch();
    $phone_json = unserialize($row["com_phone"]);
    if($trade_no != $phone_json["trade_no"]){
        echo '{"status":"false","msg":"该订单短信验证码未发送！"}';
        exit();
    }
    if($twoauth != $conf['cash_pwd'] || $sms_key != $phone_json["pass"]){
        echo '{"status":"false","msg":"二次验证密码或者短信验证码错误，修改订单状态失败。"}';
        exit();
    }
} elseif($scan_code_login = true) {
    if($twoauth != $conf['note1']){
        echo '{"status":"false","msg":"支付宝身份验证错误，修改订单状态失败。"}';
        exit();
    }
}
else{
    if($twoauth != $conf['cash_pwd']){
        echo '{"status":"false","msg":"二次验证密码错误，修改订单状态失败。"}';
        exit();
    }
}
$sql = "UPDATE `pay_order` SET `status` = '{$status}', `endtime` = '{$endtime}', `buyer` = '{$buyer}' WHERE `trade_no` = '{$trade_no}'; ";
if($DB->query($sql)){
    echo '{"status":"true","msg":"保存成功。"}';
    exit();
}else{
    echo '{"status":"false","msg":"保存失败。"}';
    exit();
}
