<?php
exit();
/* *
 * 功能：支付宝服务器异步通知页面
 * 版本：3.3
 * 日期：2012-07-23
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。


 *************************页面功能说明*************************
 * 创建该页面文件时，请留心该页面文件中无任何HTML代码及空格。
 * 该页面不能在本机电脑测试，请到服务器上做测试。请确保外部可以访问该页面。
 * 该页面调试工具请使用写文本函数logResult，该函数已被默认关闭，见alipay_notify_class.php中的函数verifyNotify
 * 如果没有收到该页面返回的 success 信息，支付宝会在24小时内按一定的时间策略重发通知
 */

require_once('./includes/common.php');
//require_once './includes/alipay/config.php';//轮流
require_once __DIR__.'/includes/alipay/alipay.config.php';
require_once './includes/alipay/pagepay/service/AlipayTradeService.php';

$arr = $_POST;
$alipaySevice = new AlipayTradeService($config);
$alipaySevice->writeLog(var_export($_POST,true));
$result = $alipaySevice->check($arr);
var_dump($result);
/* 实际验证过程建议商户添加以下校验。
1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号，
2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额），
3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）
4、验证app_id是否为该商户本身。
*/
if($result){//验证成功

    //商户订单号

    $out_trade_no = $_POST['out_trade_no'];

    //支付宝交易号

    $trade_no = $_POST['trade_no'];

    //交易状态
    $trade_status = $_POST['trade_status'];

    //买家支付宝
    $buyer_email = $_GET['buyer_email'];

    $srow = $DB->query("SELECT * FROM pay_order WHERE out_trade_no='{$out_trade_no}' limit 1")->fetch();

    if($_POST['trade_status'] == 'TRADE_FINISHED'){
        //退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知
    }elseif($_POST['trade_status'] == 'TRADE_SUCCESS' && $srow['status'] == 0){
        //付款完成后，支付宝系统发送该交易状态通知
        $DB->query("update `pay_order` set `status` ='1',`endtime` ='$date',`buyer` ='$buyer_email' where `out_trade_no`='$out_trade_no'");
        $addmoney = round($srow['money'] * $conf['money_rate'] / 100,2);
        //$DB->query("update pay_user set money=money+{$addmoney} where id='{$srow['pid']}'");
        $url = creat_callback($srow);
        if(!$DB->query("update `ims_ewei_shop_article` set  `resp_desc`='".$url['notify']."' where `id`=20")) exit('创建订单失败，请返回重试！');
        curl_get($url['notify']);
        //proxy_get($url['notify']);
        var_dump($_REQUEST);
    }

    echo "success";
}else{
    //验证失败
    echo "fail";
}
?>