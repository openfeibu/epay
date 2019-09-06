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

//查找订单信息
require_once __DIR__."/../includes/api/init.php";
$trade_no = daddslashes($_REQUEST['trade_no']);
$sql = "SELECT * FROM `pay_order` WHERE `trade_no` = '{$trade_no}' AND `status` = '0' LIMIT 1";
$result = $DB->query($sql)->fetch();
if(!$result){
    $error = "该订单不存在或者已支付完成。";
    echo $error;
    exit();
}

//判断支付方式是否为yimei
if($result['type'] != 'yimei' && $result['type'] != 'yimei_url'){
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
    'merCode' => $channel['appid'],

    //商户私钥，您的原始格式RSA私钥
    'merPubKey' => $channel['private_key'],

    //异步通知地址
    'notify_url' => $channel['notify_url'],

    //同步跳转
    'return_url' => $channel['return_url'],

    //编码格式
    'charset' => $channel['charset'],

    //签名方式
    'sign_type'=> $channel['sign_type'],

    //支付宝网关
    'gateway_url' => $channel['gatewayUrl'],

    //查询网关
    'order_query_url' => "http://testpapi.shenbianhui.cn/ QueryOrder", //订单查询网关

    //支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
    'alipay_public_key' => $channel['public_key'],


);

//日志
$log_file = __DIR__.DIRECTORY_SEPARATOR."../etc/log/yimei.log.php";
if(isset($_REQUEST)){
    $log = $_REQUEST;
}
$log['config'] = $config['merCode'];
$log['channel'] = $channel;
$log['file'] = __FILE__;
$str = json_encode($log,JSON_UNESCAPED_UNICODE);
$str = date("Y-m-d H:i:s : ").real_ip().$str.PHP_EOL;
file_put_contents($log_file,$str,FILE_APPEND);




//商户订单号，商户网站订单系统中唯一订单号，必填
global $out_trade_no;
$out_trade_no = $result['trade_no'];

//订单名称，必填
$subject = $channel['subject'];

//付款金额，必填
$total_amount = $result['money'];

//商品描述，可空
$body = $channel['body'];

//超时时间(分钟)
$timeout_express="120";

//订单创建时间
$addtime = $result['addtime'];
$addtime = strtotime($addtime);
$addtime = date("YmdHis",$addtime);

require_once __DIR__.DIRECTORY_SEPARATOR."yimei/libs/yimei.php";
require_once __DIR__.DIRECTORY_SEPARATOR."yimei/libs/function.php";
$aop = new \yimei\yimei($config);
$aop->out_trade_no = $out_trade_no;
$aop->amount = round($total_amount * 100,0);//金额以分为单位
$aop->callbackUrl = $config['notify_url'];
$aop->showUrl = $config['return_url'];
//$aop->subject = "在线充值";
$aop->productDesc = $subject;
//$aop->extra = '{"openId":"o2RvowBf7sOVJf8kJksUEMceaDqo"}';
$aop->payType = "47";
$aop->dateTime = $addtime;
$aop->validityNum = $timeout_express;
$response = $aop->submit();
//var_dump($response);
$url = $response['url'];
$post = $response['data'];
//var_dump($post);
//return;
$response = \yimei\curl_request($url,$post);
if($response){
    //var_dump($response);
    $response = json_decode($response,true);
    if($response['resultCode'] == '000000'){
        //验证签名
        $sign =strtolower(\yimei\getSign($response,$config['merPubKey']));
        if($sign != $response['sign']){
            echo "Sign Error";
            exit();
        }
        //处理订单信息
        $url = $response['qrCodeUrl'];
        //var_dump($url);
        header("Location: $url");
    }else{
        echo $response['resultCode'];
        var_dump($response);
    }
    var_dump($response);
}else{
    echo "系统错误。";
}
