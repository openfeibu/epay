<?php
exit();
/* *
 * 功能：零度云支付2.0支付异步通知页面
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。


 *************************页面功能说明*************************
 * 创建该页面文件时，请留心该页面文件中无任何HTML代码及空格。
 * 该页面不能在本机电脑测试，请到服务器上做测试。请确保外部可以访问该页面。
 * 该页面调试工具请使用写文本函数logResult，该函数已被默认关闭，见alipay_notify_class.php中的函数verifyNotify
 */

require_once('./includes/common.php');
require_once(SYSTEM_ROOT."zeropay/zero.config.php");
require_once(SYSTEM_ROOT."zeropay/Zero_notify.class.php");
//计算得出通知验证结果
$ZeropayNotify = new ZeropayNotify($zeropay_config);
$verify_result = $ZeropayNotify->verifyNotify();

if($verify_result){//验证成功
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //请在这里加上商户的业务逻辑程序代

    //商户订单号
    $out_trade_no = $_GET['out_trade_no'];

    //用户uid  ip
    $pay_id = $_GET['pay_id'];

    //商品名称
    $name = $_GET['name'];

    //商品价格
    $money = $_GET['money'];

    //支付方式
    $type = $_GET['type'];


    //付款完成后，支付宝系统发送该交易状态通知
    $srow = $DB->query("SELECT * FROM pay_order WHERE trade_no='{$out_trade_no}' limit 1")->fetch();
    if($srow['status'] == 0){
        $DB->query("update `pay_order` set `status` ='1',`endtime` ='$date' where `trade_no`='$out_trade_no'");
        $addmoney = round($srow['money'] * $conf['money_rate'] / 100,2);
        $DB->query("update pay_user set money=money+{$addmoney} where id='{$srow['pid']}'");
        $url = creat_callback($srow);
        curl_get($url['notify']);
        proxy_get($url['notify']);
    }

    //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——

    echo "success";        //请不要修改或删除

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}else{
    //验证失败
    echo "fail";
}
?>
