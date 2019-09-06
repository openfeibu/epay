<?php
namespace alipays;
include './Base.php';
/*
 *  1.获取数据
 *  2.验证签名
 *  3.验证是否来自支付宝的请求
 *  4.验证交易状态
 *  5. 验证订单号和金额
 *  6.更改订单状态
 *  
 */

class Notify extends Base
{
    public function __construct() {
        // 1.获取数据
        $postData = $_POST;
        
        //2.验证签名MD5和RSA
        if($postData['sign_type'] == 'MD5'){
            if(!$this->checkSign($postData)){
                $this->logs('log.txt', 'MD5签名失败!');
                exit();
            }else{
                $this->logs('log.txt', 'MD5签名成功!');
            }
        }elseif($postData['sign_type'] == 'RSA'){
            if(!$this->rsaCheck($this->getStr($postData), self::ALIPUBKEY, $postData['sign']) ){
                $this->logs('log.txt', 'RSA签名失败!');
                exit();
            }else{
                $this->logs('log.txt', 'RSA签名成功!');
            }
        }elseif($postData['sign_type'] == 'RSA2'){
            if(!$this->rsaCheck($this->getStr($postData), self::NEW_ALIPUBKE, $postData['sign'],'RSA2') ){
                $this->logs('log.txt', 'RSA2签名失败!');
                exit();
            }else{
                $this->logs('log.txt', 'RSA2签名成功!');
            }
        }else{
            exit('签名方式有误');
        }
        //验证是否来自支付宝的请求
        if(!$this->isAlipay($postData)){
            $this->logs('log.txt', '不是来之支付宝的通知!');
            exit();
        }else{
            $this->logs('log.txt', '是来之支付宝的通知验证通过!');
        }
        // 4.验证交易状态
        if(!$this->checkOrderStatus($postData)){
             $this->logs('log.txt', '交易未完成!');
             exit();
        }else{
             $this->logs('log.txt', '交易成功!');
        }
        //5. 验证订单号和金额
        //获取支付发送过来的订单号  在商户订单表中查询对应的金额 然后和支付宝发送过来的做对比
         $this->logs('log.txt', '订单号:' . $postData['out_trade_no'] . '订单金额:' . $postData['total_amount']);
         
        //更改订单状态
         echo 'success';
    }
}

$obj = new Notify();