<?php
require_once __DIR__.DIRECTORY_SEPARATOR."../../includes/config.php";
date_default_timezone_set("Asia/Hong_Kong");

$url = $website_urls; //网关地址
$gateway_url = $url."api/submit.php"; //下单地址（正式环境）
$order_query_url = $url."api/api.php"; //查询地址

$config = array(
    "pid"     => "10000",//商户号
    "key"   => "6hLABo7pq2JChK38kgC7FYpM86U6ye3",//商户密钥
    'gateway_url' => $gateway_url,//支付网关地址，如无变更，请不要修改。
    'order_query_url' => $order_query_url, //订单查询网关
);
