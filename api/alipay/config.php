<?php
require_once __DIR__."/../../config_base.php";
require_once __DIR__.DIRECTORY_SEPARATOR."../../includes/api/init.php";

//定义支付类型
$type = "alipay";
//获取回调out_trade_no
if(isset($_REQUEST['out_trade_no']) && $_REQUEST['out_trade_no'] != ''){
    $out_trade_no = daddslashes($_REQUEST['out_trade_no']);
}

if(isset($out_trade_no) && $out_trade_no != ''){
    //查找订单信息
    $trade_no = $out_trade_no;
    $sql = "SELECT * FROM `pay_order` WHERE `trade_no` = '{$trade_no}' LIMIT 1";
    $result = $DB->query($sql)->fetch();
    if(!$result){
        //订单不存在
        $error = "fail";
        echo $error;
        exit();
    }

    //判断支付方式是否为alipay
    if($result['type'] != $type && $result['type'] != "{$type}_url"){
        $error = "支付方式不正确";
        echo $error;
        exit();
    }

    //查找通道信息
    $channel = $result['mobile_url'];
    $sql = "SELECT * FROM `pay_channel` WHERE `id` = '{$channel}' ";
    $channel = $DB->query($sql)->fetch();
    $config = array(
        //应用ID,您的APPID。
        'app_id' => $channel['appid'],

        //商户私钥
        'merchant_private_key' => $channel['private_key'],

        //异步通知地址
        'notify_url' => $channel['notify_url'],

        //同步跳转
        'return_url' => $channel['return_url'],

        //编码格式
        'charset' => $channel['charset'],

        //签名方式
        'sign_type' => $channel['sign_type'],

        //支付宝网关
        'gatewayUrl' => $channel['gatewayUrl'],

        //支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
        'alipay_public_key' => $channel['public_key'],
    );
}else{
    // exit();
}

//日志
$log_file = __DIR__.DIRECTORY_SEPARATOR."../../etc/log/{$type}.log.php";
if(isset($_REQUEST)){
    $log = $_REQUEST;
}
$log['config'] = $config;
$log['channel'] = $channel;
$log['file'] = __FILE__;
$str = json_encode($log,JSON_UNESCAPED_UNICODE);
$str = date("Y-m-d H:i:s : ").real_ip().$str.PHP_EOL;
file_put_contents($log_file,$str,FILE_APPEND);
