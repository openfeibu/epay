<!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>支付宝电脑网站支付return_url</title>
</head>
<body>
<?php
/* *
 * 功能：支付宝页面跳转同步通知页面
 * 版本：2.0
 * 修改日期：2017-05-01
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。

 *************************页面功能说明*************************
 * 该页面可在本机电脑测试
 * 可放入HTML等美化页面的代码、商户业务逻辑程序代码
 */

require_once("config.php");
require_once 'pagepay/service/AlipayTradeService.php';

require_once __DIR__.DIRECTORY_SEPARATOR."../../includes/api/init.php";
//require_once __DIR__.DIRECTORY_SEPARATOR."../../includes/api/debug.php";

$arr = $_REQUEST;
$alipaySevice = new AlipayTradeService($config);
//$alipaySevice->writeLog(var_export($_REQUEST,true));
$log = $arr;
$log['file'] = __FILE__;
\epay\notify::log($log);
if(!isset($_REQUEST['sign'])){
    echo "fail:100";
    return;
}

$result = $alipaySevice->check($arr);

/* 实际验证过程建议商户添加以下校验。
1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号，
2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额），
3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）
4、验证app_id是否为该商户本身。
*/
if($result){//验证成功
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //请在这里加上商户的业务逻辑程序代码


    //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——

    //获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表

    //商户订单号
    $out_trade_no = \epay\daddslashes($_REQUEST['out_trade_no']);

    //支付宝交易号
    $trade_no = \epay\daddslashes($_REQUEST['trade_no']);

    //交易状态
    $trade_status = $_REQUEST['trade_status'];

    //买家ID
    $buyer_id = \epay\daddslashes($_REQUEST['buyer_id']);

    //交易金额(单位为元，无需转换)
    $money = \epay\daddslashes($_REQUEST['total_amount']);


    if($_REQUEST['trade_status'] == 'TRADE_FINISHED'){

        //判断该笔订单是否在商户网站中已经做过处理
        //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
        //请务必判断请求时的total_amount与通知时获取的total_fee为一致的
        //如果有做过处理，不执行商户的业务程序

        //注意：
        //退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知
    }elseif($_REQUEST['trade_status'] == 'TRADE_SUCCESS'){
        //判断该笔订单是否在商户网站中已经做过处理
        //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
        //请务必判断请求时的total_amount与通知时获取的total_fee为一致的
        //如果有做过处理，不执行商户的业务程序
        //注意：
        //付款完成后，支付宝系统发送该交易状态通知
        //业务处理代码
        $now = date("Y-m-d H:i:s");
        $sql = "SELECT * FROM `pay_order` WHERE `trade_no` = '{$out_trade_no}' LIMIT 1";
        $srow = $DB->query($sql)->fetch();

        if(!$srow){
            //找不到订单号
            $result_error = $_REQUEST;
            $result_error['error'] = "找不到订单号";
            $str = json_encode($result_error,JSON_UNESCAPED_UNICODE);
            file_put_contents(__DIR__.DIRECTORY_SEPARATOR."../../etc/log/notify3.log_error.php",date("Y-m-d H:i:s : ").real_ip().$str.PHP_EOL,FILE_APPEND);
            echo "fail";
            exit();
        }

        //验证付款金额
        $money = $_REQUEST['total_amount'];
        if($srow['money'] != $money){
            $result_error = $_REQUEST;
            $result_error['error'] = "金额不对";
            $str = json_encode($result_error,JSON_UNESCAPED_UNICODE);
            file_put_contents(__DIR__."/../../etc/log/notify3.log_error.php",date("Y-m-d H:i:s : ").real_ip().$str.PHP_EOL,FILE_APPEND);
            echo "fail";
            exit();
        }

        //付款完成后，支付系统发送该交易状态通知
        $endtime = $_REQUEST['gmt_payment'];
        $buyer_email = $_REQUEST['buyer_logon_id'];
        $buyer = $_REQUEST['trade_no']."@".$_REQUEST['seller_email'];
        // $sql = "update `pay_order` set `status` ='1',`endtime` ='{$endtime}',`buyer` ='{$buyer_email}', `buyer` = '{$buyer}' where `pid` = '{$userid}' and `trade_no`='{$out_trade_no}';";
        $sql = "UPDATE `pay_order` SET `status` = '1',`endtime` ='{$endtime}',`buyer` ='{$buyer_email}', `buyer` = '{$buyer}' where `trade_no`='{$out_trade_no}';";
        $DB->query($sql);
        $result = "success";
        $url = $website_urls."api/return_url.php?trade_no={$out_trade_no}&notify=yes";
        $call_back = file_get_contents($url);
        file_put_contents(__DIR__."/../../etc/log/notify3.log2.php",$call_back,FILE_APPEND);
    }

    $url = $website_urls."api/return_url.php?trade_no={$out_trade_no}";
    header("Location: $url");
    echo "验证成功<br />支付宝交易号：".$trade_no;

    //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}else{
    //验证失败
    echo "验证失败";
}
?>
</body>
</html>