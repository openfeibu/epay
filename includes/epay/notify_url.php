<?php
/* *
 * 功能：服务器异步通知页面
 * 版本：1.0
 * 修改日期：2017-05-01
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。

 *************************页面功能说明*************************
 * 创建该页面文件时，请留心该页面文件中无任何HTML代码及空格。
 * 该页面不能在本机电脑测试，请到服务器上做测试。请确保外部可以访问该页面。
 * 如果没有收到该页面返回的 success 信息，服务器会在24小时内按一定的时间策略重发通知
 */
header("Content-Type: text/html;charset=utf-8");
require_once __DIR__.DIRECTORY_SEPARATOR."../../includes/api/init.php";
//require_once __DIR__.DIRECTORY_SEPARATOR."../../includes/api/debug.php";
require_once __DIR__.DIRECTORY_SEPARATOR."config.php";
require_once __DIR__.DIRECTORY_SEPARATOR."libs/function.php";
$getSign = \epay2\getSign($_REQUEST,$config['key']);
$sign = strtolower($_REQUEST['sign']);
if($getSign != $_REQUEST['sign']){
    echo 'fail:签名错误！';
    exit();
}else{
    if($_REQUEST['code'] == '1' && $_REQUEST['status'] == '1'){


        //请在这里加上商户的业务逻辑程序代码
        //商户订单号
        $out_trade_no = \epay2\daddslashes($_REQUEST['out_trade_no']);

        //交易号
        $trade_no = \epay2\daddslashes($_REQUEST['trade_no']);

        //交易状态
        $status = $_REQUEST['status'];

        //完成时间
        $endtime = \epay2\daddslashes($_REQUEST['endtime']);

        //支付方式
        $type = $_REQUEST['type'];

        //交易金额
        //请务必判断请求时的money与通知时获取的money为一致的
        $money = $_REQUEST['money'];

        //判断该笔订单是否在商户网站中已经做过处理
        //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
        //请务必判断请求时的money与通知时获取的money为一致的
        //如果有做过处理，不执行商户的业务程序
        if($status != 1){
            echo "未支付，如果已经支付，请联系管理员。";
            exit();
        }
        $now = date("Y-m-d H:i:s");
        //根据out_trade_no，查找出商户ID
        $sql1 = "SELECT * FROM `pay_recharge_record` WHERE `out_trade_no` = '{$out_trade_no}' LIMIT 1";
        $row = $DB->query($sql1)->fetch();
        if(!$row){
            exit("找不到订单号");
        }
        //查看订单是否完成
        if($row['status'] == 1){
            echo "success";
            exit();
        }

        $pid = $row['pid'];

        //对比金额
        $money2 = $row['money'];
        $money2 = round($money2,2);
        if($money != $money2){
            echo "支付金额与订单金额不一样，请联系管理员解决。";
            exit();
        }
        //初始化pay_recharge表
        $sql2 = "INSERT IGNORE INTO `pay_recharge` (`id`, `balance`, `createtime`, `update`, `income`, `payment`) VALUES ('{$pid}', '0', '{$now}', CURRENT_TIMESTAMP, '0', '0');";
        $DB->query($sql2);
        $buyer_id = "{$trade_no}@{$pid}";
        //更新数据库
        $sql3 = "UPDATE `pay_recharge_record` SET `trade_no` = '{$trade_no}', `status` ='1',`endtime` ='{$now}', `buyer` = '{$buyer_id}' WHERE `out_trade_no` = '{$out_trade_no}' AND `status` = '0'";
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
            $money = round($money * 100,0); //单位转为分
            $balance = $balance_before + $money;
            $income = $income + $money;

            $sql5 = "INSERT INTO `pay_recharge_history` (`id`, `userid`, `trade_no`, `type`, `balance_before`, `money`, `balance`, `createtime`, `update`, `ip`, `note1`, `note2`) VALUES (NULL, '{$pid}', '{$trade_no}', '1', '{$balance_before}', '{$money}', '{$balance}', '{$now}', CURRENT_TIMESTAMP, '0.0.0.0', NULL, NULL);";

            //更新充值余额
            $sql6 = "UPDATE `pay_recharge` SET `balance` = '{$balance}', `income` = '{$income}' WHERE `id` = '{$pid}';";

            $DB->query($sql5);
            $DB->query($sql6);
            $DB->commit();
        }catch(Exception $e){
            $DB->rollBack();
            //echo "Failed: " . $e->getMessage();
        }

        //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——

        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        echo "success"; //请不要修改或删除
    }else{
        echo "fail"; //请不要修改或删除
    }

}
