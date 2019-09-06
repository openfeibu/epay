<?php
namespace alipays;
include 'Base2.php';

class Alipay extends Base
{
    // public function __construct($type) {
     
         
    //     if($type == 'MD5'){
    //         $this->md5Pay();
    //     }elseif ($type == 'RSA') {
    //         $this->rsaPay();
    //     }elseif($type == 'RSA2'){
    //         $this->newPay();
    //     }
    // }
    
    // public function md5Pay(){
    //     $params = [
    //         'service' => 'create_direct_pay_by_user', //接口名称 固定值
    //         'partner' => self::PID, //合作伙伴身份ID
    //         '_input_charset' => 'UTF-8', //商城网站编码格式
    //         'sign_type' => 'MD5',// 签名方式
    //         'sign' => '',// 需要根据其他参数生成
    //         'notify_url' => self::NOURL ,//异步通知地址 可空
    //         'return_url' => self::REURL,//同步通知地址 可空
    //         'out_trade_no' => date('YmdHis') ,//商城网站唯一订单号
    //         'subject' => '支付宝测试',//订单标题最长128汉字
    //         'payment_type' => 1,//支付类型 只取值为1(商品购买) 固定值
    //         'total_fee' => 0.02,//交易金额 单位为元
    //         'seller_id' => self::PID,//支付宝用户号 seller_id、seller_email、seller_account_name至少传一个
    //         'body' => '测试的商品',//商品描述 可空
    //     ];
    //    // echo '<pre>';
    //     $params = $this->setSign($params);
    //     // print_r($params);
    //    // exit;
    //     $url = self::PAYGAGEWAY . '?' . $this->getUrl($params);
    //      header("location:" . $url);
    // }
    // public function rsaPay(){
    //     $params = [
    //         'service' => 'create_direct_pay_by_user', //接口名称 固定值
    //         'partner' => self::PID, //合作伙伴身份ID
    //         '_input_charset' => 'UTF-8', //商城网站编码格式
    //         'sign_type' => 'RSA',// 签名方式
    //         'sign' => '',// 需要根据其他参数生成
    //         'notify_url' => self::NOURL ,//异步通知地址 可空
    //         'return_url' => self::REURL,//同步通知地址 可空
    //         'out_trade_no' => date('YmdHis') ,//商城网站唯一订单号
    //         'subject' => '支付宝测试',//订单标题最长128汉字
    //         'payment_type' => 1,//支付类型 只取值为1(商品购买) 固定值
    //         'total_fee' => 0.02,//交易金额 单位为元
    //         'seller_id' => self::PID,//支付宝用户号 seller_id、seller_email、seller_account_name至少传一个
    //         'body' => '测试的商品',//商品描述 可空
    //     ];
    //     // echo '<pre>';
    //     $params = $this->setRsaSign($params);
    //    // print_r($params);
    //     //exit;
    //      $url = self::PAYGAGEWAY . '?' . $this->getUrl($params);
    //      header("location:" . $url);
    // }
    public function newPay($return,$out_trade_no,$money,$name){
        //公共参数
        $pub_params = [
            'app_id'    => self::APPID,
            'method'    =>  'alipay.trade.page.pay', //接口名称 应填写固定值alipay.trade.page.pay
            'format'    =>  'JSON', //目前仅支持JSON
            'return_url'    => $return, //同步返回地址
            'charset'    =>  'UTF-8',
            'sign_type'    =>  'RSA2',//签名方式
            'sign'    =>  '', //签名
            'timestamp'    => date('Y-m-d H:i:s'), //发送时间 格式0000-00-00 00:00:00
            'version'    =>  '1.0', //固定为1.0
            'notify_url'    => self::NOURL, //异步通知地址
            'biz_content'    =>  '', //业务请求参数的集合
        ];
        
        //业务参数
        $api_params = [
            'out_trade_no'  => $out_trade_no,//商户订单号
            'product_code'  => 'FAST_INSTANT_TRADE_PAY', //销售产品码 固定值
            'total_amount'  => $money, //总价 单位为元
            'subject'  => $name, //订单标题
        ];
        $pub_params['biz_content'] = json_encode($api_params,JSON_UNESCAPED_UNICODE);
       // echo '<pre>';
        
      $pub_params =  $this->setRsa2Sign($pub_params);
      //print_r($pub_params);
       $url = self::NEW_PAYGATEWAY . '?' . $this->getUrl($pub_params);
       echo '<script>window.location.href="'.$url.'"</script>';
       // header("location:" . $url);
    }
}

//构建支付请求 可以传递MD5 RSA RSA2三种参数
// $obj = new Alipay('RSA2');