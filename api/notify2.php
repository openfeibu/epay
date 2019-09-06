<?php
exit();
header("Content-type: text/html; charset=utf-8");
date_default_timezone_set("Asia/Hong_Kong");
//日志
require_once __DIR__."/../includes/api/autoload.phpp";
$log_file = __DIR__.DIRECTORY_SEPARATOR."../etc/log/notify2.log.php";
\epay\log::writeLog($log_file,$_REQUEST);


$result['result_original'] = $_REQUEST['username'];
$result['result'] = json_decode($_REQUEST['username'],true);
$result['password'] = $_REQUEST['password'];
ob_start();
var_dump($result);
$str = ob_get_clean();
\epay\log::writeLog($log_file,$str);

//验证密码是否正确
if($result['password'] != 'password'){
    exit();
}

$result = $result['result'];

$summary = $result['result']['summary'];
$detail = $result['result']['detail'];
$paging = $result['result']['paging'];

$status = $result['status'];
$stat = $result['stat'];

if($status == 'deny' || $stat == 'deny'){
    //未登录
    echo "No Login";
    exit();
}elseif ($status != 'succeed'){
    echo "Unknown Error";
    exit();
}


$count = count($detail);
if($count == 0){
    echo 'success';
    exit();
}

require_once __DIR__."/../includes/common.php";
require_once __DIR__."/../person_api/function.php";
foreach ($detail as $value){
    //判断个人或企业支付宝
    if(isset($value['consumerLoginId'])){
        //个人支付宝
        $trade_no = $value['goodsTitle'];//支付系统订单号
        $tradeTime = $value['gmtCreate'];//交易时间
        $tradeNo_alipay = $value['tradeNo'];//支付宝订单号
        $otherAccountEmail = $value['consumerLoginId'];//对方账户
        $tradeAmount = $value['totalAmount'];//付款金额

        $buyer = $tradeNo_alipay."@".$otherAccountEmail;
    }else{
        $transMemo = $value['transMemo'];//支付系统订单号
        $tradeTime = $value['tradeTime'];//交易时间
        $tradeNo_alipay = $value['tradeNo'];//支付宝订单号
        $otherAccountEmail = $value['otherAccountEmail'];//对方账户
        $tradeAmount = $value['tradeAmount'];//付款金额

        $trade_no = $transMemo;
        $buyer = $tradeNo_alipay."@".$otherAccountEmail;
    }

    updateOrder(1,$tradeTime,$buyer,$trade_no);

}

function updateOrder($status,$endtime,$buyer,$trade_no){
    global $DB;
    global $log_file;
    global $website_urls;
    $sql = "SELECT * FROM `pay_order` WHERE `trade_no` = '{$trade_no}' limit 1";
    $srow=$DB->query($sql)->fetch();
    if($srow){
        $sql = "update `pay_order` set `status` = '{$status}',`endtime` ='{$endtime}', `buyer` = '{$buyer}' where `trade_no`='{$trade_no}';";
        if($DB->query($sql)){
            echo 'success';
            $url = $website_urls."api/return_url.php?trade_no={$trade_no}&notify=yes";
            $call_back = file_get_contents($url);
            \epay\log::writeLog($log_file,$call_back);
        }else{
            echo 'error';
            $call_back = $sql;
            \epay\log::writeLog($log_file,$call_back);
        }
    }else{
        echo "success";
        ob_start();
        var_dump($sql);
        $str = ob_get_clean();
        \epay\log::writeLog($log_file,$str);
    }
}
exit();

