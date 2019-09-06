<?php
date_default_timezone_set("Asia/Hong_Kong");

$url = "https://www.domain.com/"; //网关地址
$gateway_url = $url."api/submit.php"; //下单地址（正式环境）
$order_query_url = $url."api/api.php"; //查询地址

$config = array(
    "pid"     => "",//商户号
    "key"   => "",//商户密钥
    'gateway_url' => $gateway_url,//支付网关地址，如无变更，请不要修改。
    'order_query_url' => $order_query_url, //订单查询网关
);
