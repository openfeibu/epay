<?php
/**
 * 充值结果
 **/
include("../includes/common.php");
$total_amount = $_POST['total_amount'];
$out_trade_no = $_POST['out_trade_no'];

$r1 = $DB->query("select * from pay_order where out_trade_no = '{$out_trade_no}'")->fetch();
if($r1['out_trade_no'] != '' || $r1['out_trade_no'] != null){
    $pid = $r1['pid'];
    $date = date("Y-m-d H:i:s");
    $r = $DB->query("select p.money from pay_user p where id = '{$pid}'")->fetch();
    $s_money = $r['money'];
    
    $money = $total_amount + $s_money;
    $DB->query("update pay_user set money = '{$money}' where id = '{$pid}'");
    
    $DB->query("update pay_order set status = 1,endtime = '{$date}' where out_trade_no = '{$out_trade_no}' ")->fetch();
}