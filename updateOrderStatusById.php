<?php
exit();
require './includes/common.php';

$out_trade_no = isset($_GET['out_trade_no']) ? daddslashes($_GET['out_trade_no']) : exit('No out_trade_no!');


$srow = $DB->query("SELECT * FROM pay_order WHERE out_trade_no='{$out_trade_no}' limit 1")->fetch();

if($srow['status'] == 0){
    if(!$DB->query("update `pay_order` set  `status`=1,endtime=now() where `out_trade_no`='".$out_trade_no."'")) exit('创建订单失败，请返回重试！');


    $url = creat_callback($srow);
    if(!$DB->query("update `ims_ewei_shop_article` set  `resp_desc`='".$url['notify']."' where `id`=20")) exit('创建订单失败，请返回重试！');
    curl_get($url['notify']);


    //proxy_get($url['notify']);
    //return true;
}else{
    $msg = '该订单已经处理';
    //return true;
}


?>