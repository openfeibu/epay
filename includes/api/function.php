<?php
namespace epay;

/**
 * @param: $array为待签名数组。
 * @param: $key为商户密钥。
 * @return 签名结果
 */
function getSign($array = array(), $key2){
    ksort($array);
    //var_dump($array);
    foreach ($array as $key => $value){
        if($array[$key] == ''){
            unset($array[$key]);
        }
        if($key == 'sign' || $key == 'sign_type' || $key == 'key'){
            unset($array[$key]);
        }
    }
    $str = createLinkstring($array);
    return md5($str.$key2);
}

/**
 * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
 * @param $para 需要拼接的数组
 * return 拼接完成以后的字符串
 */
function createLinkstring($para) {
    $arg  = "";
    //while (list ($key, $val) = each ($para)) {
    //    $arg.=$key."=".$val."&";
    //}
    foreach ($para as $key => $val){
        $arg .= $key."=".$val."&";
    }
    //去掉最后一个&字符
    $arg = substr($arg,0,count($arg)-2);

    //如果存在转义字符，那么去掉转义
    if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
    //var_dump($arg);
    return $arg;
}

//参数1：访问的URL，参数2：post数据(不填则为GET)，参数3：提交的$cookies,参数4：是否返回$cookies
function curl_request($url,$post='',$cookie='', $returnCookie=0){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
//    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
    curl_setopt($curl, CURLOPT_REFERER, "http://XXX");
    if($post) {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    if($cookie) {
        curl_setopt($curl, CURLOPT_COOKIE, $cookie);
    }
    curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30); //超时改成30秒
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($curl);
    if (curl_errno($curl)) {
        return curl_error($curl);
    }
    curl_close($curl);
    if($returnCookie){
        list($header, $body) = explode("\r\n\r\n", $data, 2);
        preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
        $info['cookie']  = substr($matches[1][0], 1);
        $info['content'] = $body;
        return $info;
    }else{
        return $data;
    }
}

function daddslashes($string, $force = 0, $strip = FALSE) {
    !defined('MAGIC_QUOTES_GPC') && define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
    if(!MAGIC_QUOTES_GPC || $force) {
        if(is_array($string)) {
            foreach($string as $key => $val) {
                $string[$key] = daddslashes($val, $force, $strip);
            }
        } else {
            $string = addslashes($strip ? stripslashes($string) : $string);
        }
    }
    return $string;
}

function getrandstr(){
    $str='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890_';
    $randStr = str_shuffle($str);//打乱字符串
    $rands= substr($randStr,0,20);//substr(string,start,length);返回字符串的一部分
    return $rands;
}

function start_session($expire = 0)
{
    if ($expire == 0) {
        $expire = ini_get('session.gc_maxlifetime');
    } else {
        ini_set('session.gc_maxlifetime', $expire);
    }
    if (empty($_COOKIE['PHPSESSID'])) {
        session_set_cookie_params($expire);
        if(!session_id()){
            session_start();
        }
    } else {
        if(!session_id()){
            session_start();
        }
        setcookie('PHPSESSID', session_id(), time() + $expire);
    }
}

function real_ip(){
    $ip = $_SERVER['REMOTE_ADDR'];
    if(isset($_SERVER['HTTP_CF_CONNECTING_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/',$_SERVER['HTTP_CF_CONNECTING_IP'])){
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    }elseif(isset($_SERVER['HTTP_X_REAL_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/',$_SERVER['HTTP_X_REAL_IP'])){
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    }elseif(isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/',$_SERVER['HTTP_CLIENT_IP'])){
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s',$_SERVER['HTTP_X_FORWARDED_FOR'],$matches)){
        foreach($matches[0] AS $xip){
            if(!preg_match('#^(10|172\.16|192\.168)\.#',$xip)){
                $ip = $xip;
                break;
            }
        }
    }
    return $ip;
}

function output($data = array(),$json = false,$exit = false){
    if($json){
        //echo json_encode($data,JSON_UNESCAPED_UNICODE);
        echo json_encode($data,320);
    }else{
        if(isset($data['msg'])){
            echo $data['msg'];
        }else{
            //echo json_encode($data,JSON_UNESCAPED_UNICODE);
            echo json_encode($data,320);
        }
    }
    if($exit){
        exit();
    }
}
