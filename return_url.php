<?php
exit();
/* *
 * 功能：零度云支付2.0页面跳转同步通知页面
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。

 *************************页面功能说明*************************
 * 该页面可在本机电脑测试
 * 可放入HTML等美化页面的代码、商户业务逻辑程序代码
 * 该页面可以使用PHP开发工具调试，也可以使用写文本函数logResult，该函数已被默认关闭，见epay_notify_class.php中的函数verifyReturn
 */

require_once('./includes/common.php');
require_once(SYSTEM_ROOT."zeropay/zero.config.php");
require_once(SYSTEM_ROOT."zeropay/Zero_notify.class.php");
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <?php
    //计算得出通知验证结果
    $ZeropayNotify = new ZeropayNotify($zeropay_config);
    $verify_result = $ZeropayNotify->verifyNotify();
    if($verify_result){//验证成功
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //请在这里加上商户的业务逻辑程序代码

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


        $srow = $DB->query("SELECT * FROM pay_order WHERE trade_no='{$out_trade_no}' limit 1")->fetch();
        $url = creat_callback($srow);
        if($srow['status'] == 0){
            $DB->query("update `pay_order` set `status` ='1',`endtime` ='$date' where `trade_no`='$out_trade_no'");
            $addmoney = round($srow['money'] * $conf['money_rate'] / 100,2);
            $DB->query("update pay_user set money=money+{$addmoney} where id='{$srow['pid']}'");
            echo '<script>window.location.href="'.$url['return'].'";</script>';
        }else{
            echo '<script>window.location.href="'.$url['return'].'";</script>';
        }

        echo "验证成功<br />";

        //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——

        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    }else{
        //验证失败
        //如要调试，请看alipay_notify.php页面的verifyReturn函数
        echo "验证失败";
    }
    ?>
    <title>零度云支付即时到账交易接口</title>
</head>
<body>
</body>
</html>
