<?php
header("Content-type: text/html; charset=utf-8");
require_once __DIR__.DIRECTORY_SEPARATOR.'../config_base.php';
//require_once __DIR__.DIRECTORY_SEPARATOR.'../includes/api/debug.php';

$type2 = array("alipay2","wechat2","qqpay2","alipay2_url","wechat2_url","qqpay2_url","alipay2qr","wechat2qr","qqpay2qr");
//查找订单信息
require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/init.php";
$DB->exec("SET SESSION wait_timeout=300");
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
$channel = $DB->query($sql)->fetch(PDO::FETCH_ASSOC);
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
$config = $channel;
//var_dump($config);
//return;
switch($type){
    case 'alipay2':
    case 'alipay2_url':
    case 'alipay2qr':
        $type_topay = 'alipay';
        break;
    case 'wechat2':
    case 'wechat2_url':
    case 'wechat2qr':
        $type_topay = 'wechat';
        break;
    case 'qqpay2':
    case 'qqpay2_url':
    case 'qqpay2qr':
        $type_topay = 'qq';
        break;
    default:
        echo "Error：601/支付类型错误，请联系管理员。";
        exit();
        break;
}
$userid = $result['pid'];
$money = $result['money'];
$return_url = $result['return_url'];
$notify_url = $result['notify_url'];
$appid = $channel['appid'];


if(true){
    //确定最终付款金额
    //初始化增加金额
    $i = 0;
    $money2 = $DB2->fetchRowMany("SELECT `money2` FROM `pay_order` WHERE `status` = 0 AND `appid` = :appid ;",["appid" => $appid]);
    if($money2){
        $money2_array = [];
        foreach($money2 as $value){
            $money2_array[] = $value['money2'];
        }

        $money2 = $money;
        while(true){
            if(in_array($money2,$money2_array)){
                $i = $i + 0.01;
                $money2 = $money + $i;
                if($i == 1){
                    echo "收款码已用完，请联系客服处理，谢谢。";
                    exit();
                }
            }else{
                break;
            }
        }
    }else{
        $money2 = $money;
    }
}



require_once __DIR__.DIRECTORY_SEPARATOR.'person_api2/libs/function.php';
require_once __DIR__.DIRECTORY_SEPARATOR.'person_api2/libs/person_api.php';

$i = 0;
$j = 2;//循环次数

while($i < $j){
    //$mobile_url = \epay\person_api::get_mobile_url($trade_no);
    $mobile_url = $channel['id'];
    //$data = \epay\person_api::get_qrcode($mobile_url,$money,$trade_no,$type_topay);
    //$data = \epay\person_api::get_qrcode2($money,$trade_no,$type_topay);

    //使用金额验证
    //$data = \epay\person_api::get_qrcode3($appid,$mobile_url,$config['public_key'],$money2,$trade_no,$type_topay);
    //使用备注验证
    $data = \epay\person_api::get_qrcode4($appid,$mobile_url,$config['public_key'],$money,$money2,$trade_no,$type_topay,"code");
    if($data['code'] == 1){
        break;
        $i++;
    }
}
if($data['code'] == 0){
    echo $data['msg'];
    exit();
}
var_dump($data);
return;
//开启支付
header("Location: {$website_urls}api/person_api2/pay.php?trade_no={$trade_no}");
