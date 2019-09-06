<?php
/*
 * 添加订单
 */
require '../includes/common.php';
require '../includes/alipay/config.php';
$trade_no = date("YmdHis").rand(10,99);
if(!isset($_SESSION['userid'])){
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
$p_id = $_SESSION['userid'];
$date = date("Y-m-d H:i:s");
$no = $_POST['WIDout_trade_no'];
$money = $_POST['WIDtotal_amount'];

$sql = "insert into pay_order(`trade_no`,`out_trade_no`,`notify_url`,`return_url`,`type`,`pid`,`addtime`,`name`,`money`,`status`) values('{$trade_no}','{$no}','{$config['notify_url']}','{$config['return_url']}','alipay','{$p_id}','{$date}','支付宝扫码充值','{$money}','0')";
$DB->query($sql);
