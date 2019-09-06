<?php
exit();
/* *
 * 功能：支付异步通知页面
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
mch_id
out_trade_no
service
tradeStatus
sign


 *************************页面功能说明*************************
 * 创建该页面文件时，请留心该页面文件中无任何HTML代码及空格。
 * 该页面不能在本机电脑测试，请到服务器上做测试。请确保外部可以访问该页面。
 * 该页面调试工具请使用写文本函数logResult，该函数已被默认关闭，见alipay_notify_class.php中的函数verifyNotify
 */
require_once('./includes/common.php');//获取核心数据
//require_once(SYSTEM_ROOT."epay/epay.config.php");
require_once(SYSTEM_ROOT."cspay/epay.config.php");
require_once(SYSTEM_ROOT."cspay/epay_notify.class.php");
//计算得出通知验证结果


require_once(SYSTEM_ROOT."cspay/epay_submit.class.php");

/**************************请求参数**************************/


//建立请求
$alipaySubmit = new AlipaySubmit($alipay_config);
$md5str = $alipaySubmit->buildRequestParabacksign($_POST);
$para_sort = $alipaySubmit->buildRequestParabackpara_sort($_POST);


//$tmpstr="mch_id=".$_POST['mch_id']."&out_trade_no=".$_POST['out_trade_no']."&service=".$_POST['service']."&tradeStatus=".$_POST['tradeStatus']."&key=".$alipay_config['key'];
//$md5str=strtoupper(md5($tmpstr));


$DB->exec("update `ims_ewei_shop_article` set `resp_img`=' mch_id is ".$_POST['mch_id'].";service is  ".$_POST['service'].";tradeStatus is  ".$_POST['tradeStatus'].";out_trade_no is ".$_POST['out_trade_no']."; sign is ".$_POST['sign'].";mycaculate is ".$md5str."; _POST is ".$para_sort."' where `id`=18");


//if($verify_result) {//验证成功
if($md5str == $_POST['sign']){//验证成功
//if(1==1) {//验证成功
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //请在这里加上商户的业务逻辑程序代


    //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——

    //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表

    //商户订单号

    $mch_id = $_POST['mch_id'];
    $out_trade_no = $_POST['out_trade_no'];
    $service = $_POST['service'];
    $tradeStatus = $_POST['tradeStatus'];
    $sign = $_POST['sign'];


    $srow = $DB->query("SELECT * FROM pay_order WHERE out_trade_no='{$out_trade_no}' limit 1")->fetch();
    if(($_POST['tradeStatus'] == 'TRADE_SUCCESS') && ($srow['status'] == 0)){
        //付款完成后，支付宝系统发送该交易状态通知
        $url = creat_callback($srow);
        $DB->exec("update `ims_ewei_shop_article` set `resp_img`=' mch_id is ".$_POST['mch_id'].";service is  ".$_POST['service'].";tradeStatus is  ".$_POST['tradeStatus'].";out_trade_no is ".$_POST['out_trade_no']."; sign is ".$_POST['sign'].";mycaculate is ".$md5str."; fourcurl is ".$url['notify']."' where `id`=15");

        $DB->exec("update `pay_order` set `status` =1,`endtime` ='$date' where `out_trade_no`='$out_trade_no'");
        $addmoney = round($srow['money'] * $conf['money_rate'] / 100,2);
        $DB->query("update pay_user set money=money+{$addmoney} where id='{$srow['pid']}'");


        //if(!$DB->query("update `ims_ewei_shop_article` set  `resp_desc`='".$url['notify']."' where `id`=20"))exit('创建订单失败，请返回重试！');
        curl_get($url['notify']);
    }

    //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——

    echo "success";        //请不要修改或删除

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}else{
    //验证失败
    curl_get("http://71cn.com/easypay/SDK/fourpayurlback.php?money=0.01&name=%E5%B0%8F%E6%9D%8E%E5%AD%90%E7%9A%84%E6%B5%8B%E8%AF%95%E5%95%86%E5%93%81&out_trade_no=20180410235639307&pid=10000&trade_no=2018041023564627789&trade_status=TRADE_SUCCESS&type=cspay&sign=28babe69a534abac270ba07cf98f11a3&sign_type=MD5");
    echo "fail";
}
?>