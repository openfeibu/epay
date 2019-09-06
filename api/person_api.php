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

//判断支付方式是否为alipay
$type = $result['type'];
if(!in_array($type,$type2)){
    $error = "支付方式不正确。";
    echo $error;
    exit();
}

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


require_once __DIR__.DIRECTORY_SEPARATOR.'person_api/libs/function.php';
require_once __DIR__.DIRECTORY_SEPARATOR.'person_api/libs/person_api.php';

$i = 0;
$j = 2;//循环次数
while($i < $j){
    $mobile_url = \epay\person_api::get_mobile_url($trade_no);
    $data = \epay\person_api::get_qrcode($mobile_url,$money,$trade_no,$type_topay);
    //$data = \epay\person_api::get_qrcode2($money,$trade_no,$type_topay);
    if($data['code'] == 1){
        break;
        $i++;
    }
}
if($data['code'] == 0){
    echo $data['msg'];
    exit();
}

//开启支付
header("Location: {$website_urls}api/person_api/pay.php?trade_no={$trade_no}");
