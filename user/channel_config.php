<?php
$config = array();
$config[] = array(
    ['name'] => "alipay",
    ['gatewayUrl'] => "https://openapi.alipay.com/gateway.do",
    ['return_url'] => "{$website_urls}api/{$type}/return_url.php",
    ['notify_url'] => "{$website_urls}api/{$type}/notify_url.php",
);


$type_name = array(
    "alipay"   => "000_支付宝网站支付",
    "alipayh5" => "001_支付宝H5支付",
    "wechat"   => "010_微信扫码支付",
    "wechath5" => "011_微信H5支付",
    "alipay2"  => "200_支付宝扫码（个人）",
    "wechat2"  => "210_微信扫码（个人）",
    "qqpay2"   => "220_QQ钱包扫码（个人）",
    "yimei"    => "300_溢美网银在线",
    "paiyi"    => "301_派易（支付宝，支付宝H5，微信）",
    "ousmd"    => "302_忆网（支付宝H5）",
    "maicheng"    => "303_麦橙（支付宝H5）",
);
$type_selected = array(
    "alipay"   => "",
    "alipayh5" => "",
    "wechat"   => "",
    "wechath5" => "",
    "alipay2"  => "",
    "wechat2"  => "",
    "qqpay2"   => "",
    "yimei"    => "",
    "paiyi"    => "",
    "ousmd"    => "",
    "maicheng"    => "",
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
$users = $DB2->fetchRowMany("SELECT `id`,`uuid` FROM `pay_user` WHERE `id` = :id OR `uid` = :uid",["id" => $_SESSION['userid'],"uid" => $_SESSION['userid']]);
$all_uuid = [];
foreach($users as $value){
    $value['type'] = "user";
    $all_uuid[] = $value;
}
