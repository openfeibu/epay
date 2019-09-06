<?php
exit();
require './includes/common.php';

//$out_trade_no=isset($_GET['out_trade_no'])?daddslashes($_GET['out_trade_no']):exit('No out_trade_no!');

if(!$DB->query("update `ims_ewei_shop_article` set  `resp_img`='out_trade_no is ".$_GET['out_trade_no']."; sign is ".$_GET['sign']."' where `id`=20")) exit('创建订单失败，请返回重试！');

echo $_GET['name'];


$queryArr = $_GET;

$prestr = createLinkstring(argSort(paraFilter($queryArr)));
$pid = intval($queryArr['pid']);
if(empty($pid)) sysmsg('PID不存在');
$userrow = $DB->query("SELECT * FROM pay_user WHERE id={$pid} limit 1")->fetch();
echo "SELECT * FROM pay_user WHERE id={$pid} limit 1";
if(!md5Verify($prestr,$queryArr['sign'],$userrow['key'])){
    echo '<br/>签名MD5校验失败，请返回重试！prestr:'.$prestr.' sign:'.$queryArr['sign'].'key:'.$userrow['key'];
}else{
    echo '<br/>签名成功';
}


?>