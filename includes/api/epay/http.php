<?php
namespace epay;

class http
{
    /**
     * URL轮询接口
     * @param $mobile_url url字符串
     */
    public static function lunxun($mobile_url,$used = 0){
        $url_arr = explode(PHP_EOL,$mobile_url);
        $numbers = count($url_arr);
        if($numbers == 1){
            return trim($url_arr[0]);
        }else{
            $numbers--;
            $key = rand(0,$numbers);
            $url = trim($url_arr[$key]);
            if(self::httpcode($url) == 200){
                return $url;
            }else{
                return self::lunxun($mobile_url);
            }
        }
    }
    /**
     * Created on 2016-9-4
     */
    public static function httpcode($url){
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

    public static function is_https() {
        if ( !empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
            return true;
        } elseif ( isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ) {
            return true;
        } elseif ( !empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
            return true;
        }
        return false;
    }
}
