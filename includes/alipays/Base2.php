<?php

namespace alipays;
include 'Rsa.php';

class Base extends RSA{
    /**
     * 以下为商户配置信息
     */
    const PID = '';//合作伙伴ID
    const NOURL = "http://pay.daztai.com/wmf_epay/public/index.php/epayapi/alipaynotify/index";//异步通知地址
    const KEY = '支付宝后台获取';
    const PAYGAGEWAY = 'https://mapi.alipay.com/gateway.do';
    const CHECKURL = 'https://mapi.alipay.com/gateway.do?service=notify_verify&partner='.self::PID.'&notify_id=';
    const APPPRIKEY = '';
    const ALIPUBKEY = '';
    const APPID = '';
    const NEW_ALIPUBKE = '';
    const NEW_PAYGATEWAY = 'https://openapi.alipay.com/gateway.do';

    public function getStr($arr,$type = 'RSA'){
        //筛选  
        if(isset($arr['sign'])){
            unset($arr['sign']);
        }
        if(isset($arr['sign_type']) && $type == 'RSA'){
            unset($arr['sign_type']);
        }
        //排序  
        ksort($arr);
        //拼接
        return $this->getUrl($arr,false);
    }

    //将数组转换为url格式的字符串
    public function getUrl($arr,$encode = true){
        if($encode){
            return http_build_query($arr);
        }else{
            return urldecode(http_build_query($arr));
        }
    }

    //获取签名MD5
    public function getSign($arr){
        return md5($this->getStr($arr).self::KEY);
    }

    //获取含有签名的数组MD5
    public function setSign($arr){
        $arr['sign'] = $this->getSign($arr);
        return $arr;
    }

    //获取签名RSA
    public function getRsaSign($arr){
        return $this->rsaSign($this->getStr($arr),self::APPPRIKEY);
    }

    //获取含有签名的数组RSA
    public function setRsaSign($arr){
        $arr['sign'] = $this->getRsaSign($arr);
        return $arr;
    }

    //获取签名RSA2
    public function getRsa2Sign($arr){
        return $this->rsaSign($this->getStr($arr,'RSA2'),self::APPPRIKEY,'RSA2');
    }

    //获取含有签名的数组RSA
    public function setRsa2Sign($arr){
        $arr['sign'] = $this->getRsa2Sign($arr);
        return $arr;
    }

    //记录日志
    public function logs($filename,$data){
        file_put_contents('./logs/'.$filename,$data."\r\n",FILE_APPEND);
    }

    //2.验证签名
    public function checkSign($arr){
        $sign = $this->getSign($arr);
        if($sign == $arr['sign']){
            return true;
        }else{
            return false;
        }
    }

    //验证是否来之支付宝的通知
    public function isAlipay($arr){
        $str = file_get_contents(self::CHECKURL.$arr['notify_id']);
        if($str == 'true'){
            return true;
        }else{
            return false;
        }
    }

    // 4.验证交易状态
    public function checkOrderStatus($arr){
        if($arr['trade_status'] == 'TRADE_SUCCESS' || $arr['trade_status'] == 'TRADE_FINISHED'){
            return true;
        }else{
            return false;
        }
    }
}