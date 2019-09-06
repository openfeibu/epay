<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . "../../includes/api/init.php";
$money=addslashes($_GET["money"]);
$orderno=addslashes($_GET["mark"]);
$type=addslashes($_GET["bankCode"]);
$pid="99@".addslashes($_GET["pid"]);
$sql = "SELECT * FROM `pay_channel` WHERE `appid` = '{$pid}' and `type` = 'bank' and `status` = '1' order by rand() limit 1";
$userrow = $DB->query($sql)->fetch();
$subject = $userrow["id"];
$suidao=$userrow["subject"];
$url=$suidao."/alibank?money=".$money."&mark=".$orderno."&bankCode=".$type;
//$headerArray =array("Content-type:application/json;","Accept:application/json");
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//curl_setopt($url,CURLOPT_HTTPHEADER,$headerArray);
$output = curl_exec($ch);
curl_close($ch);
//echo $output;
$output = json_decode($output,true);
 $remarks=$output["batchNo"];
$sql = "UPDATE `pay_order` SET `remarks`='{$remarks}', `mobile_url`='{$subject}' WHERE `trade_no` = :trade_no";
$re  = $DB->prepare($sql);
$re->execute(array(":trade_no" => $orderno));
$result = $re->fetch(PDO::FETCH_ASSOC);
//if($result){
echo  $output["payurl"];
//}
?>