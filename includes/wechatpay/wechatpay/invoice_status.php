<?php
require_once __DIR__."/../../../includes/common.php";


$invoiceid = daddslashes($_REQUEST['invoiceid']);
$sql = "SELECT * FROM `pay_recharge_record` WHERE `out_trade_no` = '{$invoiceid}' ";
$result = $DB->query($sql)->fetch();
if($result){
    $status = $result['status'];
    $paymentmethod = $result['type'];
    if($status == 1){
        if($paymentmethod == "wechat"){
            echo "SUCCESS";
        }else{
            echo "FAIL01";
        }
    }else{
        echo "FAIL02";
    }
}else{
    echo "";
}








