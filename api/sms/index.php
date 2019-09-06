<?php
/*
    ***聚合数据（JUHE.CN）短信API服务接口PHP请求示例源码
    ***DATE:2015-05-25
*/
//header('content-type:text/html;charset=utf-8');
require_once __DIR__."/../../includes/common.php";
//require_once __DIR__."/../../includes/api/debug.php";
//if($islogin2 != 1){
//    exit("<script language='javascript'>window.location.href='./../../user/login.php';</script>");
//}
$sendUrl = 'http://v.juhe.cn/sms/send'; //短信接口的URL


if(isset($_REQUEST['pid']) && isset($_REQUEST['act']) && $_REQUEST['act'] == 'sendsms'){
    $pid = daddslashes($_REQUEST['pid']);
//    if($_SESSION['p_id'] != $pid){
////        echo "商户ID不对";
//        echo 2;
//        exit();
//    }
}else{
//    echo "参数不对。";
    echo 2;
    exit();
}

//查找用户手机号
$sql = "SELECT * FROM `pay_user` WHERE `id` = '{$pid}'; ";
$result = $DB->query($sql)->fetch();
if(!$result){
    exit(2);
    //查找不到商户ID
}
$phone = trim($result['com_phone']);//去掉手机号的空格

//验证手机号是否正确
if(!is_mobile($phone)){
    echo "2";
    exit();
}

//检查余额

//录入数据库
$time = time();
$now = date("Y-m-d H:i:s",$time);
$expiredtime = date("Y-m-d H:i:s",$time + 300);
$code = rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9);
$sql = "INSERT INTO `pay_smslog` (`id`, `userid`, `type`, `createtime`, `expiredtime`, `updatetime`, `ip`, `city`, `code`, `data`) VALUES (NULL, '{$pid}', 'juhe', '{$now}', '{$expiredtime}', CURRENT_TIMESTAMP, NULL, NULL, '{$code}', '');";
$result = $DB->query($sql);
if(!$result){
    //抛出异常
    echo '1';
    exit();
}
$id = $DB->lastInsertId();
$tpl_value = "#code#={$code}&#m#=5";

function is_mobile( $text ) {
    $search = '/^0?1[3|4|5|6|7|8][0-9]\d{8}$/';
    if ( preg_match( $search, $text ) ) {
        return ( true );
    } else {
        return ( false );
    }
}

$smsConf = array(
    'key'   => '701a609584d10f22b7e9458a3324c26d', //您申请的APPKEY
    'mobile'    => $phone, //接受短信的用户手机号码
    'tpl_id'    => '92433', //您申请的短信模板ID，根据实际情况修改
    'tpl_value' => $tpl_value //您设置的模板变量，根据实际情况修改
);

$content = juhecurl($sendUrl,$smsConf,1); //请求发送短信

if($content){
    $result = json_decode($content,true);
    $error_code = $result['error_code'];
    if($error_code == 0){
        //状态为0，说明短信发送成功
//        echo "短信发送成功,短信ID：".$result['result']['sid'];
        echo "0";
        $note1 = "发送成功";
    }else{
        //状态非0，说明失败
//        $msg = $result['reason'];
//        echo "短信发送失败(".$error_code.")：".$msg;
        echo "1";
        $note1 = "发送失败";
    }
}else{
    //返回内容异常，以下可根据业务逻辑自行修改
//    echo "请求发送短信失败";
    $result = "请求发送短信失败";
    $note1 = "请求失败";
}

$data = json_encode($result,JSON_UNESCAPED_UNICODE);
$sql = "UPDATE `pay_smslog` SET `data` = '{$data}', `note1` = '{$phone}' WHERE `id` = '{$id}' ";
$DB->query($sql);
//if($content){
//    $result = json_decode($content,true);
//    $error_code = $result['error_code'];
//    if($error_code == 0){
//        //状态为0，说明短信发送成功
//        echo "短信发送成功,短信ID：".$result['result']['sid'];
//    }else{
//        //状态非0，说明失败
//        $msg = $result['reason'];
//        echo "短信发送失败(".$error_code.")：".$msg;
//    }
//}else{
//    //返回内容异常，以下可根据业务逻辑自行修改
//    echo "请求发送短信失败";
//}

/**
 * 请求接口返回内容
 * @param  string $url [请求的URL地址]
 * @param  string $params [请求的参数]
 * @param  int $ipost [是否采用POST形式]
 * @return  string
 */
function juhecurl($url,$params=false,$ispost=0)
{
    $httpInfo = array();
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.172 Safari/537.22');
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($ispost) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_URL, $url);
    } else {
        if ($params) {
            curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
        } else {
            curl_setopt($ch, CURLOPT_URL, $url);
        }
    }
    $response = curl_exec($ch);
    if ($response === FALSE) {
        //echo "cURL Error: " . curl_error($ch);
        return false;
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
    curl_close($ch);
    return $response;
}
