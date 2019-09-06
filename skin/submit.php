<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>正在为您跳转到支付页面，请稍候...</title>
    <style type="text/css">
        body {
            margin: 0;
            padding: 0;
        }

        p {
            position: absolute;
            left: 50%;
            top: 50%;
            width: 330px;
            height: 30px;
            margin: -35px 0 0 -160px;
            padding: 20px;
            font: bold 14px/30px "宋体", Arial;
            background: #f9fafc url(../images/loading.gif) no-repeat 20px 26px;
            text-indent: 22px;
            border: 1px solid #c5d0dc;
        }

        #waiting {
            font-family: Arial;
        }
    </style>
    <script>
        function open_without_referrer(link) {
            document.body.appendChild(document.createElement('iframe')).src = 'javascript:"<script>top.location.replace(\'' + link + '\')<\/script>"';
        }
    </script>
</head>
<body>
<?php
exit();
require './includes/common.php';

@header('Content-Type: text/html; charset=UTF-8');

if(isset($_GET['pid'])){
    $queryArr = $_GET;
}else{
    $queryArr = $_POST;
}

$prestr = createLinkstring(argSort(paraFilter($queryArr)));
$pid = intval($queryArr['pid']);
if(empty($pid)) sysmsg('PID不存在');
$userrow = $DB->query("SELECT * FROM pay_user WHERE id='{$pid}' limit 1")->fetch();
if(!md5Verify($prestr,$queryArr['sign'],$userrow['key'])) sysmsg('签名校验失败，请返回重试！');

if($userrow['active'] == 0) sysmsg('商户已封禁，无法支付！');

$type = daddslashes($queryArr['type']);
$out_trade_no = daddslashes($queryArr['out_trade_no']);
$notify_url = daddslashes($queryArr['notify_url']);
$return_url = daddslashes($queryArr['return_url']);
$name = daddslashes($queryArr['name']);
$money = daddslashes($queryArr['money']);
$sitename = urlencode(base64_encode(daddslashes($queryArr['sitename'])));


if(empty($out_trade_no)) sysmsg('订单号(out_trade_no)不能为空');
if(empty($notify_url)) sysmsg('通知地址(notify_url)不能为空');
if(empty($return_url)) sysmsg('回调地址(return_url)不能为空');
if(empty($name)) sysmsg('商品名称(name)不能为空');
if(empty($money)) sysmsg('金额(money)不能为空');
if($money <= 0) sysmsg('金额不合法');

$row = $DB->query("SELECT * FROM pay_order WHERE pid='$pid' and out_trade_no='{$out_trade_no}' limit 1")->fetch();
$trade_no = date("YmdHis").rand(10000,99999);
if(!$DB->query("insert into `pay_order` (`trade_no`,`out_trade_no`,`notify_url`,`return_url`,`type`,`pid`,`addtime`,`name`,`money`,`status`) values ('".$trade_no."','".$out_trade_no."','".$notify_url."','".$return_url."','".$type."','".$pid."','".$date."','".$name."','".$money."','0')")) exit('创建订单失败，请返回重试！');

switch($type){
    case 'alipay'://支付宝
        //sysmsg('此支付方式暂时维护，请选择其他方式进行支付！');
        //echo "<script>window.location.href='alipay.php?out_trade_no={$out_trade_no}&sitename={$sitename}';</script>";//第三方
        echo "<script>window.location.href='epayapi.php?out_trade_no={$out_trade_no}&type={$type}';</script>";//对接其他支付
        break;
    case 'cspay'://第4方支付宝
        echo "<script>window.location.href='cspayapi.php?out_trade_no={$out_trade_no}&type=qqpay';</script>";//对接其他支付
        break;
    case 'personpay'://个人支付宝
        echo "<script>window.location.href='alipay_person_api.php?out_trade_no={$out_trade_no}&type=qqpay';</script>";//对接其他支付
        break;
    case 'personpay_wx'://个人微信PC免签版
        echo "<script>window.location.href='weixin_person_api.php?out_trade_no={$out_trade_no}&type=qqpay';</script>";//对接其他支付
        break;
    case 'personpayH5'://个人支付宝
        echo "<script>window.location.href='alipay_person_apiH5.php?out_trade_no={$out_trade_no}&type=qqpay';</script>";//对接其他支付
        break;
    case 'cqpay'://第4方微信支付
        echo "<script>window.location.href='cqpayapi.php?out_trade_no={$out_trade_no}&type=qqpay';</script>";//对接其他支付
        break;
    case 'tenpay':
        //sysmsg('此支付方式暂时维护，请选择其他方式进行支付！');
        //echo "<script>window.location.href='tenpay.php?out_trade_no={$out_trade_no}&sitename={$sitename}';</script>";//第三方
        echo "<script>window.location.href='epayapi.php?out_trade_no={$out_trade_no}&type=qqpay';</script>";//对接其他支付
        break;
    case 'wxpay'://微信
        //sysmsg('此支付方式暂时维护，请选择其他方式进行支付！');
        echo "<script>window.location.href='wxpay.php?out_trade_no={$out_trade_no}&sitename={$sitename}';</script>";//第三方
        //echo "<script>window.location.href='epayapi.php?out_trade_no={$out_trade_no}&type={$type}';</script>";//对接其他支付
        break;
    case 'qqpay'://QQ
        //sysmsg('此支付方式暂时维护，请选择其他方式进行支付！');
        echo "<script>window.location.href='qqpay.php?out_trade_no={$out_trade_no}&sitename={$sitename}';</script>";//第三方
        //echo "<script>window.location.href='epayapi.php?out_trade_no={$out_trade_no}&type={$type}';</script>";//对接其他支付
        break;
    case 'alipay2'://个人版支付宝
        echo "<script>window.location.href='person_api/topay.php?trade_no={$trade_no}'</script>";
        break;
    case 'wechat2'://个人版微信
        echo "<script>window.location.href='person_api/topay.php?trade_no={$trade_no}'</script>";
        break;
    default:
        echo "<script>window.location.href='default.php?out_trade_no={$out_trade_no}&sitename={$sitename}';</script>";
        break;
}

?>
<p>正在为您跳转到支付页面，请稍候...</p>
</body>
</html>