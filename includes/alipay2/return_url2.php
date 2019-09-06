<!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>支付宝电脑网站支付return_url</title>
</head>
<body>
<?php
/* *
 * 功能：支付宝页面跳转同步通知页面
 * 版本：2.0
 * 修改日期：2017-05-01
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。

 *************************页面功能说明*************************
 * 该页面可在本机电脑测试
 * 可放入HTML等美化页面的代码、商户业务逻辑程序代码
 */

require_once __DIR__.DIRECTORY_SEPARATOR."config.php";
require_once 'pagepay/service/AlipayTradeService.php';

require_once __DIR__.DIRECTORY_SEPARATOR."../../includes/api/init.php";
//require_once __DIR__.DIRECTORY_SEPARATOR."../../includes/api/debug.php";

$arr = $_REQUEST;
$alipaySevice = new AlipayTradeService($config);
//$alipaySevice->writeLog(var_export($_REQUEST,true));
$log_file = __DIR__.DIRECTORY_SEPARATOR."../../etc/log/includes_alipay2.log.php";
$log["request"] = $arr;
$log['file'] = __FILE__;
\epay\log::writeLog($log_file,$log);
if(!isset($_REQUEST['sign'])){
    echo "fail:100";
    return;
}

$result = $alipaySevice->check($arr);

/* 实际验证过程建议商户添加以下校验。
1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号，
2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额），
3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）
4、验证app_id是否为该商户本身。
*/
if($result || false){//验证成功
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //请在这里加上商户的业务逻辑程序代码


    //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——

    //获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表

    //商户订单号
    $out_trade_no = \epay\daddslashes($_REQUEST['out_trade_no']);

    //支付宝交易号
    $trade_no = \epay\daddslashes($_REQUEST['trade_no']);

    //交易状态
    //$trade_status = $_REQUEST['trade_status'];//同步无此参数
    $_REQUEST['trade_status'] = "";

    //买家ID
    //$buyer_id = \epay\daddslashes($_REQUEST['buyer_id']);//同步无此参数
    $buyer_id = "";

    //交易金额(单位为元，转化为分)
    $money = \epay\daddslashes($_REQUEST['total_amount']) * 100;


    if($_REQUEST['trade_status'] == 'TRADE_FINISHED'){

        //判断该笔订单是否在商户网站中已经做过处理
        //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
        //请务必判断请求时的total_amount与通知时获取的total_fee为一致的
        //如果有做过处理，不执行商户的业务程序

        //注意：
        //退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知
    }elseif($_REQUEST['trade_status'] == 'TRADE_SUCCESS'){
        //判断该笔订单是否在商户网站中已经做过处理
        //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
        //请务必判断请求时的total_amount与通知时获取的total_fee为一致的
        //如果有做过处理，不执行商户的业务程序
        //注意：
        //付款完成后，支付宝系统发送该交易状态通知
        //业务处理代码
        $now = date("Y-m-d H:i:s");
        //根据out_trade_no，查找出商户ID
        $sql1 = "SELECT * FROM `pay_recharge_record` WHERE `out_trade_no` = '{$out_trade_no}' LIMIT 1";
        $row = $DB->query($sql1)->fetch();
        if(!$row){
            exit("找不到订单号");
        }
        if($row['status'] == '0'){
            $pid = $row['pid'];

            //初始化pay_recharge表
            $sql2 = "INSERT IGNORE INTO `pay_recharge` (`id`, `balance`, `createtime`, `update`, `income`, `payment`) VALUES ('{$pid}', '0', '{$now}', CURRENT_TIMESTAMP, '0', '0');";
            $DB->query($sql2);

            //更新数据库
            $sql3 = "UPDATE `pay_recharge_record` SET `trade_no` = '{$trade_no}', `status` ='1',`endtime` ='{$now}', `buyer` = '{$buyer_id}' WHERE `out_trade_no` = '{$out_trade_no}' AND (`status` = '0' OR `endtime` = '' OR `endtime` IS NULL )";
            $DB->query($sql3);

            //添加到余额并记录
            try{
                $DB->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
                $DB->beginTransaction();
                //查询当前余额
                $sql4 = "SELECT * FROM `pay_recharge` WHERE `id` = '{$pid}' FOR UPDATE ";
                $row2 = $DB->query($sql4)->fetch();
                $balance_before = round($row2['balance'],0);
                $income = $row2['income'];

                //插入充值记录
                $money = round($money,0);
                $balance = $balance_before + $money;
                $income = $income + $money;

                $sql5 = "INSERT INTO `pay_recharge_history` (`id`, `userid`, `trade_no`, `type`, `balance_before`, `money`, `balance`, `createtime`, `update`, `ip`, `note1`, `note2`) VALUES (NULL, '{$pid}', '{$out_trade_no}', '1', '{$balance_before}', '{$money}', '{$balance}', '{$now}', CURRENT_TIMESTAMP, '0.0.0.0', NULL, NULL);";

                //更新充值余额
                $sql6 = "UPDATE `pay_recharge` SET `balance` = '{$balance}', `income` = '{$income}' WHERE `id` = '{$pid}';";

                $DB->query($sql5);
                $DB->query($sql6);
                $DB->commit();
            }catch(Exception $e){
                $DB->rollBack();
                //echo "Failed: " . $e->getMessage();
            }
        }
    }
    //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    require_once __DIR__."/../../config_base.php";
    header("Location: {$website_urls}admin/");
    echo "验证成功<br />支付宝交易号：".$trade_no;
}else{
    //验证失败
    echo "验证失败";
}
?>
</body>
</html>