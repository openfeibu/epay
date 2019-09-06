<?php
header("Content-type: text/html; charset=utf-8");
require_once __DIR__.DIRECTORY_SEPARATOR.'../config_base.php';
//require_once __DIR__.DIRECTORY_SEPARATOR.'../includes/api/debug.php';

$type2 = array("alipayh5","alipayh5_url");
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

//判断支付方式是否正确
$type = $result['type'];
if(!in_array($type,$type2)){
    $error = "支付方式不正确。";
    echo $error;
    exit();
}

//查找通道信息
$channel = $result['mobile_url'];
$sql = "SELECT * FROM `pay_channel` WHERE `id` = '{$channel}' ";
$channel = $DB->query($sql)->fetch();
global $config;
$config = array(
    //应用ID,您的APPID。
    'app_id'               => $channel['appid'],

    //商户私钥，您的原始格式RSA私钥
    'merchant_private_key' => $channel['private_key'],

    //异步通知地址
    'notify_url'           => $channel['notify_url'],

    //同步跳转
    'return_url'           => $channel['return_url'],

    //编码格式
    'charset'              => $channel['charset'],

    //签名方式
    'sign_type'            => $channel['sign_type'],

    //支付宝网关
    'gatewayUrl'           => $channel['gatewayUrl'],

    //支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
    'alipay_public_key'    => $channel['public_key'],

);

//日志
$log_file = __DIR__.DIRECTORY_SEPARATOR."../etc/log/alipaywap.log.php";
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
require_once __DIR__.DIRECTORY_SEPARATOR.'alipaywap/wappay/service/AlipayTradeService.php';
require_once __DIR__.DIRECTORY_SEPARATOR.'alipaywap/wappay/buildermodel/AlipayTradeWapPayContentBuilder.php';

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
$timeout_express = "1m";

$payRequestBuilder = new AlipayTradeWapPayContentBuilder();
$payRequestBuilder->setBody($body);
$payRequestBuilder->setSubject($subject);
$payRequestBuilder->setOutTradeNo($out_trade_no);
$payRequestBuilder->setTotalAmount($total_amount);
$payRequestBuilder->setTimeExpress($timeout_express);

$payResponse = new AlipayTradeService($config);
$result = $payResponse->wapPay($payRequestBuilder,$config['return_url'],$config['notify_url']);
return;
