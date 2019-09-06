<?php
namespace epay;

//参数1：访问的URL，参数2：post数据(不填则为GET)，参数3：提交的$cookies,参数4：是否返回$cookies
function curl_request($url,$post='',$cookie='', $returnCookie=0){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
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

//账户相关信息
function find_user($userid = ''){
//    $DB = $GLOBALS['DB'];
    global $DB;
    if($userid == '') return false;
    $sql = "SELECT * FROM `pay_user` WHERE `id` = '{$userid}' limit 1;";
    $result = $DB->query($sql);
    if(!$result) return false; //找不到商户ID
    $row = $result->fetch();
    while ($row){
        $row2 = $row;
        $row = $result->fetch();
    }
    return $row2;
}

//签名
function getSign($array,$key,$type = 'md5'){
    //排序数组
    ksort($array);

    //去掉空值为空的键
    foreach ($array as $key => $value){
        if($array[$key] == ''){
            unset($array[$key]);
        }
    }

    //拼接
    $str = http_build_query($array);

    //签名
    switch ($type){
        case 'md5':
            $sign = md5($str.$key);
            break;
        default:
            $sign = md5($str.$key);
            break;
    }

    return $sign;
}

function getSign_person($array,$key,$type = 'md5'){
/*
 * 异步通知参数说明：
 * type:支付类型
 * no：订单号
 * money：金额
 * mark：备注
 * dt：时间
 * version：版本号（现在为1）
 * userids：商户ID(需要事先在平台定义好，并告诉商户，商户在收费助手的程序配置里面录入)
 * sign：签名
 * sign签名方法
 * dt+mark+money+no+type+signkey+userids+version这几个参数拼接然后md5，signkey是商户在收费助手的程序配置里面设置的的signkey，userids是商户在收费助手的程序配置里面设置的商户ID[需要事先在平台里面创建好]
 */
  $str = $array['dt'].$array['mark'].$array['money'].$array['no'].$array['type'].$key.$array['userids'].$array['version'];
//  var_dump($key);
//  var_dump($str);
  return md5($str);
}

//用户余额操作
function balance_add($userid){
    global $DB;
    $sql = "";
}
