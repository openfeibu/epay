<?php
/**
 * 支付提交接口
 */
 require_once __DIR__.DIRECTORY_SEPARATOR.'../../includes/api/init.php';
  require_once __DIR__.DIRECTORY_SEPARATOR.'../../includes/function.php';
error_reporting(0);
header("Content-type: text/html; charset=utf-8");

$pid="99@".$_REQUEST["pid"];
$sql = "SELECT * FROM `pay_channel` WHERE `appid` = '{$pid}' and `status` ='1' and `type` ='kuaikuai' limit 1";
$usertype = $DB->query($sql)->fetch();
$pay_memberid = $usertype["public_key"];   //商户ID
$Md5key = $usertype["private_key"];

$pay_orderid = $_GET["tradeno"];    //订单号
$pay_amount =  $_GET["money"];    //交易金额

	$pay_bankcode = "901";


$pay_applydate = date("Y-m-d H:i:s");  //订单时间
$pay_notifyurl = "./notify.php";   //服务端返回地址
$pay_callbackurl = "./return.php";  //页面跳转返回地址
  //密钥

//扫码
$native = array(
    "pay_memberid" => $pay_memberid,
    "pay_orderid" => $pay_orderid,
    "pay_amount" => $pay_amount,
    "pay_applydate" => $pay_applydate,
    "pay_bankcode" => $pay_bankcode,
    "pay_notifyurl" => $pay_notifyurl,
    "pay_callbackurl" => $pay_callbackurl,
);
ksort($native);
reset($native);
$md5str = "";
foreach ($native as $key => $val) {
    $md5str = $md5str . $key . "=" . $val . "&";
}
//echo($md5str . "key=" . $Md5key);
$sign = strtoupper(md5($md5str . "key=" . $Md5key));
$native["pay_md5sign"] = $sign;
$native['pay_attach'] = $pid;
$native['pay_productname'] ='充值';
$native["return_type"] = $return_type;

//api接口提交
$url = "http://kkapi.kuai168.cc/Pay_Index.html";   //提交地址
$data = http_build_query($native);
list($returnCode, $returnContent) = curl($url, $data);
echo $returnContent;

function curl($url, $data){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:application/x-www-form-urlencoded; charset=utf-8"));
    ob_start();
    curl_exec($ch);
    $returnContent = ob_get_contents();
    ob_end_clean();
    $returnCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    return [$returnCode, $returnContent];
}
