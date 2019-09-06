<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>零度即时到帐支付</title>
</head>
<?php
exit();
/* *
 * 功能：即时到账交易接口接入页
 * 
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
 */
require './includes/common.php';
require_once(SYSTEM_ROOT."zeropay/zero.config.php");
require_once(SYSTEM_ROOT."zeropay/Zero_submit.class.php");
$trade_no = daddslashes($_GET['trade_no']);
$sitename = daddslashes($_GET['sitename']);
$row = $DB->query("SELECT * FROM pay_order WHERE trade_no='{$trade_no}' limit 1")->fetch();
/**************************请求参数**************************/
$notify_url = "http://".$_SERVER['HTTP_HOST']."/notify_url.php";
//需http://格式的完整路径，不能加?id=123这类自定义参数

//页面跳转同步通知页面路径
$return_url = "http://".$_SERVER['HTTP_HOST']."/return_url.php";
//需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/

//二维码控制器地址
$qrcode_url = "http://".$_SERVER['HTTP_HOST']."/qrcode_url.php";
//需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/

//商户订单号
$out_trade_no = $_POST['WIDout_trade_no'];
//商户网站订单系统中唯一订单号，必填

//用户ID
$pay_id = $_POST['WIDout_pay_id'];
//金额是否变动的唯一识标，必填

//支付方式
$type = $_POST['type'];
//商品名称
$name = $_POST['WIDsubject'];
//付款金额
$money = $_POST['WIDtotal_fee'];
//站点名称
$sitename = '零度即时到帐支付测试站点';
//必填


/************************************************************/

//构造要请求的参数数组，无需改动
$parameter = array(
    "pid"          => $zeropay_config['partner'],
    "out_trade_no" => $row['trade_no'],
    "pay_id"       => $pay_id ? $pay_id : getpay_id(),//如果没有pay_id 默认使用ip
    "name"         => $row['name'],
    "money"        => number_format((float)$row['money'],2,'.',''),//金额保留两位小位数点加入签名，因为系统回调的时候都带小位数点回调的
    "type"         => $row['type'],
    "notify_url"   => $notify_url,
    "return_url"   => $return_url,
    "qrcode_url"   => $qrcode_url,
    "sitename"     => $sitename,
);

//建立请求
$ZeropaySubmit = new ZeropaySubmit($zeropay_config);
$html_text = $ZeropaySubmit->buildRequestForm($parameter,'GET');
echo $html_text;

?>
</body>
</html>
