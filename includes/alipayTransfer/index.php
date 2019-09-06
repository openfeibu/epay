<?php
namespace alipayTransfer;
include 'Base.php';


class Alipay extends Base
{
    const TRANSFER = 'https://openapi.alipay.com/gateway.do';

    public function __construct() {
     
    
    }
    //查询转账
    public function searchPay($out_trade_no){
        //公共请求参数
         $pub_params = [
            'app_id'    => self::APPID,
            'method'    =>  'alipay.fund.trans.order.query', //接口名称 应填写固定值alipay.fund.trans.toaccount.transfer
            'format'    =>  'JSON', //目前仅支持JSON
            'charset'    =>  'UTF-8',
            'sign_type'    =>  'RSA2',//签名方式
            'sign'    =>  '', //签名
            'timestamp'    => date('Y-m-d H:i:s'), //发送时间 格式0000-00-00 00:00:00
            'version'    =>  '1.0', //固定为1.0
            'biz_content'    =>  '', //业务请求参数的集合
        ];
        
        //请求参数
        $api_params = [
            'out_biz_no'  => $out_trade_no,//商户转账订单号
        ];
        $pub_params['biz_content'] = json_encode($api_params,JSON_UNESCAPED_UNICODE);
        $pub_params =  $this->setRsa2Sign($pub_params);
      
       return $this->curlRequest(self::TRANSFER, $pub_params);
    }
	//转账
    public function transfer($account,$money,$out_trade_no,$payee_real_name){
        //公共请求参数
         $pub_params = [
            'app_id'    => self::APPID,
            'method'    =>  'alipay.fund.trans.toaccount.transfer', //接口名称 应填写固定值alipay.fund.trans.toaccount.transfer
            'format'    =>  'JSON', //目前仅支持JSON
            'charset'    =>  'UTF-8',
            'sign_type'    =>  'RSA2',//签名方式
            'sign'    =>  '', //签名
            'timestamp'    => date('Y-m-d H:i:s'), //发送时间 格式0000-00-00 00:00:00
            'version'    =>  '1.0', //固定为1.0
            'biz_content'    =>  '', //业务请求参数的集合
        ];
        
        //请求参数
        $api_params = [
            'out_biz_no'  => $out_trade_no,//商户转账订单号
            'payee_type'  => 'ALIPAY_LOGONID', //收款方账户类型 
            'payee_account'  => $account, //收款方账户
            'amount'  => $money, //金额
            'payee_real_name' => $payee_real_name, //收款方真实姓名校对
        ];
        $pub_params['biz_content'] = json_encode($api_params,JSON_UNESCAPED_UNICODE);
        $pub_params =  $this->setRsa2Sign($pub_params);
      
       return $this->curlRequest(self::TRANSFER, $pub_params);
    }
}

//构建支付请求 可以传递MD5 RSA RSA2三种参数
// $obj = new Alipay();

// $data = [
//      'payee_account'  => '13428055376', //收款方账户
//      'amount'  => '1', //金额
// ];

// //UTF-8格式的json数据
// $res = iconv('gbk','utf-8',$obj->transfer($data));


// echo '<pre>';
// //转换为数组
// $res = json_decode($res,true); 

// print_r($res);

/*
$res = $obj->searchPay();
print_r(json_decode($res,true));
*/