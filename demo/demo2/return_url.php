<?php
/* *
 * 功能：服务器同步通知页面
 * 版本：1.0
 * 修改日期：2017-05-01
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。

 *************************页面功能说明*************************
 * 该页面不能在本机电脑测试，请到服务器上做测试。请确保外部可以访问该页面。
 */
header("Content-Type: text/html;charset=utf-8");
require_once __DIR__.DIRECTORY_SEPARATOR."config.php";
require_once __DIR__.DIRECTORY_SEPARATOR."libs/function.php";
$getSign = \epay2\getSign($_REQUEST,$config['key']);
$sign = strtolower($_REQUEST['sign']);
if($getSign != $_REQUEST['sign']){
    echo '支付成功';
    exit();
}else{
    if($_REQUEST['code'] == '1' && $_REQUEST['status'] == '1'){


        //请在这里加上商户的业务逻辑程序代码
        //商户订单号
        $out_trade_no = $_REQUEST['out_trade_no'];

        //交易号
        $trade_no = $_REQUEST['trade_no'];

        //交易状态
        $status = $_REQUEST['status'];

        //完成时间
        $endtime = $_REQUEST['endtime'];

        //支付方式
        $type = $_REQUEST['type'];

        //交易金额
        //请务必判断请求时的money与通知时获取的money为一致的
        $money = $_REQUEST['money'];

        //判断该笔订单是否在商户网站中已经做过处理
        //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
        //请务必判断请求时的money与通知时获取的money为一致的
        //如果有做过处理，不执行商户的业务程序

        //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
        echo '支付成功';
    }else{
        echo '支付失败，请联系客服处理。';
    }

}
