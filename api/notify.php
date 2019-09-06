<?php
header("Content-type: text/html; charset=utf-8");
date_default_timezone_set("Asia/Hong_Kong");
$log_file = __DIR__.DIRECTORY_SEPARATOR."../etc/log/notify.log.php";
$log2_file = __DIR__.DIRECTORY_SEPARATOR."../etc/log/notify.log2.php";
$log_error_file = __DIR__.DIRECTORY_SEPARATOR."../etc/log/notify.log_error.php";
//日志
require_once __DIR__."/../includes/function.php";
$str = json_encode($_REQUEST,JSON_UNESCAPED_UNICODE);
$str = date("Y-m-d H:i:s : ").real_ip().$str.PHP_EOL;
file_put_contents($log_file,$str,FILE_APPEND);

if(isset($_REQUEST['sign']) && isset($_REQUEST['userids'])){
    $sign = $_REQUEST['sign'];
    $userid = $_REQUEST['userids'];
    $data = $_REQUEST;
    $money = $_REQUEST['money'];
    unset($data['sign']);
}else{
    echo "success:600/参数不完整。";
    exit();
}
// require_once __DIR__.DIRECTORY_SEPARATOR."notify3_e87iff66SXjpF477Gt84.php";
// return;
//此接口关闭
echo "success:600/此版本软件已更新，请使用新版本。";
exit();

require_once __DIR__."/../includes/common.php";
require_once __DIR__."/../person_api/function.php";

//查询商户密钥
$user = \epay\find_user($userid);
$signkey = $user['key'];
$user_others = \epay\user::find_user_others($userid);
$signkey_others = $user_others['key'];

//获取签名
$sign2 = \epay\getSign_person($data,$signkey);
$sign2_others = \epay\getSign_person($data,$signkey_others);


//检查签名
if($sign2 != $sign && $sign2_others != $sign){
    //签名不正确
    $result = "success:601/签名不正确";
    $error = $_REQUEST;
    $error['error'] = $result;
    $str = json_encode($error,JSON_UNESCAPED_UNICODE);
    $str = date("Y-m-d H:i:s : ").real_ip().$str.PHP_EOL;
    file_put_contents($log_error_file,$str,FILE_APPEND);
    echo $result;
    exit();
}

if($sign2 == $sign){
    $result = "success:602/用的用户的KEY";
    $error = $_REQUEST;
    $error['msg'] = $result;
    $str = json_encode($error,JSON_UNESCAPED_UNICODE);
    $str = date("Y-m-d H:i:s : ").real_ip().$str.PHP_EOL;
    file_put_contents($log_error_file,$str,FILE_APPEND);
    echo $result;
}elseif ($sign2_others == $sign){
    $result = "success:602/用的新的KEY";
    $error = $_REQUEST;
    $error['msg'] = $result;
    $str = json_encode($error,JSON_UNESCAPED_UNICODE);
    $str = date("Y-m-d H:i:s : ").real_ip().$str.PHP_EOL;
    file_put_contents($log_error_file,$str,FILE_APPEND);

    if($signkey_others == '0'){
        $result = "success:602/不能用0作为密钥";
        $error = $_REQUEST;
        $error['error'] = $result;
        $str = json_encode($error,JSON_UNESCAPED_UNICODE);
        $str = date("Y-m-d H:i:s : ").real_ip().$str.PHP_EOL;
        file_put_contents($log_error_file,$str,FILE_APPEND);
        exit();
    }
}else{
    $result = "success:605/未知错误，退出。";
    $error = $_REQUEST;
    $error['error'] = $result;
    $str = json_encode($error,JSON_UNESCAPED_UNICODE);
    $str = date("Y-m-d H:i:s : ").real_ip().$str.PHP_EOL;
    file_put_contents($log_error_file,$str,FILE_APPEND);
    echo $result;
    exit();
}


$sql = "SELECT * FROM pay_order WHERE trade_no='{$data['mark']}' limit 1";
//var_dump($sql);
$srow=$DB->query($sql)->fetch();

if(!$srow){
    //找不到订单号
    $result = "success:603/找不到订单号";
    $error = $_REQUEST;
    $error['error'] = $result;
    $str = json_encode($error,JSON_UNESCAPED_UNICODE);
    $str = date("Y-m-d H:i:s : ").real_ip().$str.PHP_EOL;
    file_put_contents($log_error_file,$str,FILE_APPEND);
    echo $result;
    exit();
}

//验证付款金额
if($srow['money'] != $money){
    $result = "success:604/金额不对";
    $error = $_REQUEST;
    $error['error'] = $result;
    $str = json_encode($error,JSON_UNESCAPED_UNICODE);
    $str = date("Y-m-d H:i:s : ").real_ip().$str.PHP_EOL;
    file_put_contents($log_error_file,$str,FILE_APPEND);
    echo $result;
    exit();
}

// if($srow['money'] == "已收款"){
//     $result = "success:604/金额不对";
//     $error = $_REQUEST;
//     $error['error'] = $result;
//     $str = json_encode($error,JSON_UNESCAPED_UNICODE);
//     $str = date("Y-m-d H:i:s : ").real_ip().$str.PHP_EOL;
//     file_put_contents($log_error_file,$str,FILE_APPEND);
// }

//查看订单是否已完成，旧逻辑不对，目前已经加入状态9
//if($srow['status'] != 0){
if($srow['status'] == 1){
    $result = "success:606/该订单已完成。";
    $error = $_REQUEST;
    $error['error'] = $result;
    $str = json_encode($error,JSON_UNESCAPED_UNICODE);
    $str = date("Y-m-d H:i:s : ").real_ip().$str.PHP_EOL;
    file_put_contents($log_error_file,$str,FILE_APPEND);
    echo $result;
    exit();
}

//var_dump($srow);
//付款完成后，支付系统发送该交易状态通知
//同时验证商户ID和订单号
$sql = "update `pay_order` set `status` ='1',`endtime` ='$date',`buyer` ='$buyer_email', `buyer` = '{$data['no']}@{$data['account']}' where `pid` = '{$userid}' and `trade_no`='{$data['mark']}';";

//只验证订单号
$sql = "update `pay_order` set `status` ='1',`endtime` ='$date',`buyer` ='$buyer_email', `buyer` = '{$data['no']}@{$data['account']}' where `trade_no`='{$data['mark']}';";
$DB->query($sql);
//$addmoney=round($srow['money']*$conf['money_rate']/100,2);
//$DB->query("update pay_user set money=money+{$addmoney} where id='{$srow['pid']}'");

//$url=creat_callback($srow);
//if(!$DB->query("update `ims_ewei_shop_article` set  `resp_desc`='".$url['notify']."' where `id`=20"))exit('创建订单失败，请返回重试！');
//curl_get($url['notify']);
//proxy_get($url['notify']);
$result = "success";
EOF:
//$result = json_encode($result,JSON_UNESCAPED_UNICODE);
echo $result;
$url = $website_urls."api/return_url.php?trade_no={$data['mark']}&notify=yes";
$call_back = file_get_contents($url);
$log = array();
$log['url'] = $url;
$log['call_back'] = $call_back;
$str = json_encode($log,JSON_UNESCAPED_UNICODE);
$str = date("Y-m-d H:i:s : ").real_ip().$str.PHP_EOL;
file_put_contents($log2_file,$str,FILE_APPEND);
