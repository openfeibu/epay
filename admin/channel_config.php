<?php
//$config = array();
//$config[] = array(
//    ['name'] => "alipay",
//    ['gatewayUrl'] => "https://openapi.alipay.com/gateway.do",
//    ['return_url'] => "{$website_urls}api/{$type}/return_url.php",
//    ['notify_url'] => "{$website_urls}api/{$type}/notify_url.php",
//);
//

$type_name = array(
    "alipay"   => "000_支付宝网站支付",
    "alipayh5" => "001_支付宝H5支付",
    "wechat"   => "010_微信扫码支付",
    "wechath5" => "011_微信H5支付",
    "alipay2"  => "200_支付宝扫码（个人）",
    "wechat2"  => "210_微信扫码（个人）",
    "qqpay2"   => "220_QQ钱包扫码（个人）",
    "yunshanpay" => "230_云闪付扫码（个人）",
    'ddpay'    => "240_钉钉扫码（个人）",
    "yinshengpay" => "250_银盛通扫码（个人）",
    "yimei"    => "300_溢美网银在线",
    "paiyi"    => "301_派易（支付宝，支付宝H5，微信）",
    "ousmd"    => "302_忆网（支付宝H5）",
    "maicheng"    => "303_麦橙（支付宝H5）",
    "kuaikuai"    => "304_快快（微信H5）",
    "tonglian"    => "305_通联（微信、支付宝）",
    "wuyoupay"    => "306_无忧（微信扫码）",
    "cntpay"    => "307_cnt（微信扫码）",
    "ddqr"    => "309_钉钉（支付宝扫码）",
    "bank"    => "310_网银（pc网银快捷）",
);
$type_selected = array(
    "alipay"   => "",
    "alipayh5" => "",
    "wechat"   => "",
    "wechath5" => "",
    "alipay2"  => "",
    "wechat2"  => "",
    "qqpay2"   => "",
    "yunshanpay"=>"",
    "ddpay"    => "",
    "yinshengpay"=>"",
    "yimei"    => "",
    "paiyi"    => "",
    "ousmd"    => "",
    "maicheng"    => "",
    "wuyoupay"    => "",
    "cntpay"    => ""
);
$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : "";
switch($type){
    case 'alipay':
        $gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $return_url = "{$website_urls}api/{$type}/return_url.php";
        $notify_url = "{$website_urls}api/{$type}/notify_url.php";
        break;
    case 'alipayh5':
        $gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $return_url = "{$website_urls}api/alipaywap/return_url.php";
        $notify_url = "{$website_urls}api/alipaywap/notify_url.php";
        break;
    case 'yimei':
        $gatewayUrl = "http://testpapi.shenbianhui.cn/QRCode/CreateOrder";
        $return_url = "{$website_urls}api/{$type}/return_url.php";
        $notify_url = "{$website_urls}api/{$type}/notify_url.php";
        break;
    case 'paiyi':
        $gatewayUrl = "http://open.oilvk.com/appmerchantproxy";//旧
        //$gatewayUrl = "http://open.pyicloud.com/appmerchantproxy";//新
        $return_url = "{$website_urls}api/{$type}/return_url.php";
        $notify_url = "{$website_urls}api/{$type}/notify_url.php";
        break;
    case 'ousmd':
        $gatewayUrl = "https://apicd.ousmd.cn/openpay/xbdo";
        $return_url = "{$website_urls}api/{$type}/return_url.php";
        $notify_url = "{$website_urls}api/{$type}/notify_url.php";
        break;
    case 'maicheng':
        $gatewayUrl = "http://139.159.133.182:8080/pay/codePayment.do";
        $return_url = "{$website_urls}api/{$type}/return_url.php";
        $notify_url = "{$website_urls}api/{$type}/notify_url.php";
        break;
    default:
        break;
}
require_once __DIR__.DIRECTORY_SEPARATOR."../includes/api/init.php";
$admin_users = $DB2->fetchRowMany("SELECT `id`,`uuid` FROM `pay_admin`");
$users = $DB2->fetchRowMany("SELECT `id`,`uuid` FROM `pay_user`");
$all_uuid = [];
foreach($admin_users as $value){
    $value['type'] = "admin";
    $all_uuid[] = $value;
}
foreach($users as $value){
    $value['type'] = "user";
    $all_uuid[] = $value;
}
