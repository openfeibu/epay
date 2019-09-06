<?php
header("Content-type:text/html; charset=UTF-8");
require_once __DIR__.DIRECTORY_SEPARATOR."../../includes/api/init.php";
require_once __DIR__.DIRECTORY_SEPARATOR."../../config/config_base.php";
require_once __DIR__.DIRECTORY_SEPARATOR."../../includes/SendSms.php";
if($sms_on_off == false){
    echo '{"success":"false","msg":"非法操作！"}';
    exit();
}
\epay\start_session(300);
//$file_name = "../../config/cache/".md5("sms_code").".php";
/*$ser = unserialize(str_replace("<?php exit(); ?>","",file_get_contents($file_name)) );*/
//date_default_timezone_set("PRC");
//$datetime = date("Y-m-d H:i:s",strtotime("-5 Minute",strtotime($ser["time"])));
//if(date("Y-m-d H:i:s")>=$datetime){
//    echo '{"success":"false","msg":"短信已发送请查看！"}';
//    exit();
//}
if(isset($_COOKIE[md5("sms_code")])){
    date_default_timezone_set("PRC");
    $ser = unserialize($_COOKIE[md5("sms_code")]);
    $num = $ser["num"];
    $datetime = null;
    $time = null;
//    var_dump($ser);
    switch($num){
        case 1:$datetime = date("Y-m-d H:i:s",strtotime("+1 Minute",strtotime($ser["time"])));$time = "1分钟后重试";break;
        case 2:$datetime = date("Y-m-d H:i:s",strtotime("+2 Minute",strtotime($ser["time"])));$time = "2分钟后重试";break;
        case 3:$datetime = date("Y-m-d H:i:s",strtotime("+3 Minute",strtotime($ser["time"])));$time = "3分钟后重试";break;
        case 4:$datetime = date("Y-m-d H:i:s",strtotime("+4 Minute",strtotime($ser["time"])));$time = "4分钟后重试";break;
        case 5:$datetime = date("Y-m-d H:i:s",strtotime("+5 Minute",strtotime($ser["time"])));$time = "5分钟后重试";break;
        case 6:$datetime = date("Y-m-d H:i:s",strtotime("+10 Minute",strtotime($ser["time"])));$time = "10分钟后重试";break;
        case 7:$datetime = date("Y-m-d H:i:s",strtotime("+30 Minute",strtotime($ser["time"])));$time = "30分钟后重试";break;
        case 8:$datetime = date("Y-m-d H:i:s",strtotime("+60 Minute",strtotime($ser["time"])));$time = "60分钟后重试";break;
        case 9:$datetime = date("Y-m-d H:i:s",strtotime("+90 Minute",strtotime($ser["time"])));$time = "90分钟后重试";break;
        case 10:$datetime = date("Y-m-d H:i:s",strtotime("+12 hours",strtotime($ser["time"])));$time = "12小时后重试";break;
        default:$time = "已被封禁！";break;
    }
    $new_time = date("Y-m-d H:i:s");
//    echo date("Y-m-d H:i:s")."&&&&".$datetime;
    if($new_time<=$datetime && $new_time>=$ser["time"]){
        echo '{"success":"false","msg":"由于您发送过于频繁,'.$time.'！"}';
        exit();
    }
}
//发送短信

$token = mt_rand(100000,999999);
$SignName = $sign_name;
$TemplateCode = "SMS_160536008";
$PhoneNumbers = $send_sms_munber;
$ParamString="{\"code\":\"{$token}\"}";
$sessionid = session_id();//sessionid唯一

$sms = new SendSms();
$ali_back = $sms->send_sms($PhoneNumbers,$SignName,$TemplateCode,$ParamString);
//var_dump($ali_back);

//业务逻辑开始
if($ali_back["Code"] == "OK"){
    $sms_code["token"] = $token;
    $sms_code["sessionid"] = $sessionid;
    date_default_timezone_set("PRC");
    $sms_code["time"] = date("Y-m-d H:i:s");
    $sms_code1 = "<?php exit(); ?>".serialize($sms_code);
    $file_name = "../../config/cache/".md5("sms_code").".php";
    file_put_contents($file_name,$sms_code1);
    $ali_back["ParamString"] = $ParamString;
    $ali_back["$SignName"] = $SignName;
    $ali_back["PhoneNumbers"] = $PhoneNumbers;
    $time_s = time();
    $expire = $time_s + (strtotime(date("Y-m-d 23:59:59"))-$time_s);
    $num = 1;
    if(isset($_COOKIE[md5("sms_code")])){
        $ser = unserialize($_COOKIE[md5("sms_code")]);
        $num = $ser["num"]+1;
        setcookie(md5("sms_code"),serialize(["num"=>$num,"time"=>$sms_code["time"],"ssk"=>$sms_code["token"],"PhoneNumbers"=>$PhoneNumbers]),$expire);
    }
    else{
        setcookie(md5("sms_code"),serialize(["num"=>"$num","time"=>$sms_code["time"],"ssk"=>$sms_code["token"],"PhoneNumbers"=>$PhoneNumbers]),$expire);
    }
    ll($send_sms_munber);
    echo '{"success":"true","msg":"短信发送成功！","num":"'.$num.'"}';
    exit();
}
else{
    echo '{"success":"false","msg":"短信发送过于频繁，请稍后重试！"}';
    exit();
}

//公用
function ll($send_sms_munber){
    global $sms_code;
    global $ali_back;
    global $DB;
    $ip = get_ip();
    $city = get_ip_city1($ip);
    $ct = json_decode($city,true);
    if($ct["code"]==0){
        $city1 = $ct["data"]["country"]."->".$ct["data"]["region"]."->".$ct["data"]["city"]."->".$ct["data"]["county"]."->".$ct["data"]["isp"];
    }else{
        $city1 = "未查询出城市";
    }
    $ali_back = json_encode($ali_back,JSON_UNESCAPED_UNICODE);
    $sql = "INSERT INTO `pay_smslog`(`userid`, `type`, `createtime`, `endtime`, `expiredtime`, `ip`, `city`, `code`, `data`) VALUES 
('0','管理云登录@{$send_sms_munber}','{$sms_code["time"]}','{$sms_code["time"]}','{$sms_code["time"]}','{$ip}','{$city1}','{$sms_code["token"]}','{$ali_back}')";
    $DB->query($sql);
    return $DB->lastInsertId();
}
//获取ip
function get_ip(){
    //判断服务器是否允许$_SERVER
    $realip = $_SERVER["REMOTE_ADDR"];

    return $realip;
}
//获取城市
function get_ip_city1($ip){
    $url = 'http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip='; //这个地址不能用了
    $url = "http://ip.taobao.com/service/getIpInfo.php?ip={$ip}";
    // $city = curl_get($url);//此函数有问题
    if($ip == "::1"){
        $city = json_encode(["code"=>"99"]);
    }else{
        $city = file_get_contents($url);
    }

    return $city;
}




function https_request($url)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($curl);
    if (curl_errno($curl)) {return 'ERROR '.curl_error($curl);}
    curl_close($curl);
    return $data;
}
function xml_to_array($xml){
    $reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
    if(preg_match_all($reg, $xml, $matches)){
        $count = count($matches[0]);
        for($i = 0; $i < $count; $i++){
            $subxml= $matches[2][$i];
            $key = $matches[1][$i];
            if(preg_match( $reg, $subxml )){
                $arr[$key] = xml_to_array( $subxml );
            }else{
                $arr[$key] = $subxml;
            }
        }
    }
    return @$arr;
}

//生成uuid
function guid(){
    if (function_exists('com_create_guid')){
        return com_create_guid();
    }else{
        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = //chr(123)// "{"
            substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12);
        //.chr(125);// "}"
        return $uuid;
    }
}

function percentEncode($str)
{
    // 使用urlencode编码后，将"+","*","%7E"做替换即满足ECS API规定的编码规范
    $res = urlencode($str);
    $res = preg_replace('/\+/', '%20', $res);
    $res = preg_replace('/\*/', '%2A', $res);
    $res = preg_replace('/%7E/', '~', $res);
    return $res;
}

function computeSignature($parameters, $accessKeySecret)
{
    // 将参数Key按字典顺序排序
    ksort($parameters);
    // 生成规范化请求字符串
    $canonicalizedQueryString = '';
    foreach($parameters as $key => $value)
    {
        $canonicalizedQueryString .= '&' . percentEncode($key)
            . '=' . percentEncode($value);
    }
    // 生成用于计算签名的字符串 stringToSign
    $stringToSign = 'GET&%2F&' . percentencode(substr($canonicalizedQueryString, 1));
    //echo "<br>".$stringToSign."<br>";
    // 计算签名，注意accessKeySecret后面要加上字符'&'
    $signature = base64_encode(hash_hmac('sha1', $stringToSign, $accessKeySecret . '&', true));
    return $signature;
}

?>