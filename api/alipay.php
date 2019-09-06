<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>支付</title>
</head>
<?php
/* *
 * 功能：支付宝手机网站支付接口(alipay.trade.wap.pay)接口调试入口页面
 * 版本：2.0
 * 修改日期：2016-11-01
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 请确保项目文件有可写权限，不然打印不了日志。
 */

header("Content-type: text/html; charset=utf-8");
require_once __DIR__.DIRECTORY_SEPARATOR.'../config_base.php';
//require_once __DIR__.DIRECTORY_SEPARATOR.'../includes/api/debug.php';

$type2 = "alipay";
//查找订单信息
require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/init.php";
$trade_no = daddslashes($_REQUEST['trade_no']);
$sql = "SELECT * FROM `pay_order` WHERE `trade_no` = '{$trade_no}' AND `status` = '0' LIMIT 1";
$result = $DB->query($sql)->fetch();
if(!$result){
    $error = "该订单不存在或者已支付完成。";
    echo $error;
    exit();
}

//判断支付方式是否为alipay
if($result['type'] != 'alipay' && $result['type'] != 'alipay_url'){
    $error = "支付方式不正确。";
    echo $error;
    exit();
}

//查找通道信息
$channel = $result['mobile_url'];
$sql = "SELECT * FROM `pay_channel` WHERE `id` = '{$channel}' ";
$channel = $DB->query($sql)->fetch();
global $config;
$config = array (
    //应用ID,您的APPID。
    'app_id' => $channel['appid'],

    //商户私钥，您的原始格式RSA私钥
    'merchant_private_key' => $channel['private_key'],

    //异步通知地址
    'notify_url' => $channel['notify_url'],

    //同步跳转
    'return_url' => $channel['return_url'],

    //编码格式
    'charset' => $channel['charset'],

    //签名方式
    'sign_type'=> $channel['sign_type'],

    //支付宝网关
    'gatewayUrl' => $channel['gatewayUrl'],

    //支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
    'alipay_public_key' => $channel['public_key'],


);

//日志
$log_file = __DIR__.DIRECTORY_SEPARATOR."../etc/log/alipay.log.php";
if(isset($_REQUEST)){
    $log = $_REQUEST;
}
$log['config'] = $config;
$log['channel'] = $channel;
$log['file'] = __FILE__;
$str = json_encode($log,JSON_UNESCAPED_UNICODE);
$str = date("Y-m-d H:i:s : ").real_ip().$str.PHP_EOL;
file_put_contents($log_file,$str,FILE_APPEND);

// require_once __DIR__.DIRECTORY_SEPARATOR.'alipaywap/config.php';
require_once __DIR__.DIRECTORY_SEPARATOR.'alipay/pagepay/service/AlipayTradeService.php';
require_once __DIR__.DIRECTORY_SEPARATOR.'alipay/pagepay/buildermodel/AlipayTradePagePayContentBuilder.php';

//商户订单号，商户网站订单系统中唯一订单号，必填
global $out_trade_no;
$out_trade_no = $result['trade_no'];

//订单名称，必填
$subject = $channel['subject'];

//付款金额，必填
$total_amount = $result['money'];

//商品描述，可空
$body = $channel['body'];

//超时时间
$timeout_express="1m";

$payRequestBuilder = new AlipayTradePagePayContentBuilder();
$payRequestBuilder->setBody($body);
$payRequestBuilder->setSubject($subject);
$payRequestBuilder->setTotalAmount($total_amount);
$payRequestBuilder->setOutTradeNo($out_trade_no);

$aop = new AlipayTradeService($config);
$response = $aop->pagePay($payRequestBuilder,$config['return_url'],$config['notify_url']);
var_dump($response);
?>
</body>
</html>