<?php
namespace epay;
//随机轮询
function lunxun_sj($mobile_url,$pointer = 0){
    static $i = 0;
    $i ++;
    $result = array();

    if(is_string($mobile_url)){
        $url_arr = explode(PHP_EOL,$mobile_url);
    }else{
        $url_arr = $mobile_url;
    }

    //去掉数组中的空元素
    foreach ($url_arr as $key => $value){
        $value2 = str_replace(array("\r\n", "\r", "\n"," "),'',$value);
        if($value2 == ''){
            unset($url_arr[$key]);
        }elseif($value2 != $value){
            $url_arr[$key] = $value2;
        }
    }
    //重置下标为0开始
    $url_arr = array_values($url_arr);
    $numbers = count($url_arr);

    if($numbers == 0){
        if($i == 1){
            $result['code'] = 0;
            $result['msg'] = "通道关闭";
            $result['url'] = "";
        }else{
            $result['code'] = 0;
            $result['msg'] = "通道故障，请联系管理员解决。";
            $result['url'] = "";
        }
    }elseif($numbers == 1){
        if(httpcode($url_arr[0]) == 200){
            $result['code'] = 1;
            $result['msg'] = "0";
            $result['url'] = $url_arr[0];
        }else{
            $result['code'] = 0;
            $result['msg'] = "通道故障，请联系管理员解决。";
            $result['url'] = $url_arr[$pointer];
        }
    }else{
        $key = $numbers - 1;
        $key = rand(0,$key);
//        var_dump($key);
        if(httpcode($url_arr[$key]) == 200){
            $result['code'] = 1;
            $result['msg'] = $key;
            $result['url'] = $url_arr[$key];
        }else{
            //删除不通的隧道，根据剩余的隧道随机轮询
//            echo $i."BEFORE:";
//            var_dump($url_arr);
            unset($url_arr[$key]);
            $url_arr = array_values($url_arr);
//            echo $i."AFTER:";
//            var_dump($url_arr);
//            echo "<br>";
            $funcation = __FUNCTION__;
            return $funcation($url_arr);
        }
    }
    return $result;
}

//依次轮询
function lunxun_yc($mobile_url,$pointer = 0){
    static $i = 0;
    $i ++;
    $result = array();

    if(is_string($mobile_url)){
        $url_arr = explode(PHP_EOL,$mobile_url);
    }else{
        $url_arr = $mobile_url;
    }

    //去掉数组中的空元素
    foreach ($url_arr as $key => $value){
        $value2 = str_replace(array("\r\n", "\r", "\n"," "),'',$value);
        if($value2 == ''){
            unset($url_arr[$key]);
        }elseif($value2 != $value){
            $url_arr[$key] = $value2;
        }
    }
    //重置下标为0开始
    $url_arr = array_values($url_arr);

    $numbers = count($url_arr);

    $pointer = intval($pointer);
    $pointer = $pointer + 1;
    if($pointer >= $numbers){
        $pointer = 0;
    }

    if($i > $numbers && $numbers >=2){
        $result['code'] = 0;
        $result['msg'] = "通道故障，请联系管理员解决。或者<a href='javascript:location.reload();'>刷新此页面</a>。<span style='color: white;'>{$pointer}</span>";
        $result['url'] = "";
        return $result;
    }

    if($numbers == 0){
        if($i == 1){
            $result['code'] = 0;
            $result['msg'] = "通道关闭";
            $result['url'] = "";
        }else{
            $result['code'] = 0;
            $result['msg'] = "通道故障，请联系管理员解决。";
            $result['url'] = "";
        }
    }elseif($numbers == 1){
        if(httpcode($url_arr[0]) == 200){
            $result['code'] = 1;
            $result['msg'] = "0";
            $result['url'] = $url_arr[0];
        }else{
            $result['code'] = 0;
            $result['msg'] = "通道故障，请联系管理员解决。或者<a href='javascript:location.reload();'>刷新此页面</a>。";
            $result['url'] = "";
        }
    }else{
        $url = trim($url_arr[$pointer]);
        if(httpcode($url) == 200){
            $result['code'] = 1;
            $result['msg'] = $pointer;
            $result['url'] = $url_arr[$pointer];
        }else{

            //删除不通的隧道，根据剩余的隧道随机轮询
            //unset($url_arr[$key]);

            $funcation = __FUNCTION__;
            return $funcation($url_arr,$pointer);
        }
    }
    return $result;
}

/*
 * Created on 2016-9-4
 *
 */
function httpcode($url){
    $ch = curl_init();
    $timeout = 3;
    curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_exec($ch);
    return $httpcode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
    curl_close($ch);
}

