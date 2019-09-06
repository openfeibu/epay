<?php
/**
 * 定时结算任务，1分钟运行1次。
 */
header("Content-Type: text/html; charset=utf-8");
?>
<html>
<head>
<!--    <meta http-equiv="refresh" content="30">-->
</head>
<body>
<?php
set_time_limit(0);
require_once __DIR__."/../includes/api/init.php";
require_once __DIR__.DIRECTORY_SEPARATOR."../config_base.php";

//是否是迁移模式
if(isset($migrate_db) && $migrate_db){
    exit();//迁移模式，停止上分
}
//require_once __DIR__."/../includes/api/debug.php";
//为了安全，加入其它条件判断
$sql = "SELECT * FROM `pay_order` WHERE `cashstatus` = '0' AND `status` = '1' AND buyer<>'' AND endtime<>'0000-00-00 00:00:00'  AND ADDTIME>=DATE_SUB(ENDTIME,INTERVAL 10 MINUTE) LIMIT 30000; ";
$results = $DB->query($sql)->fetchAll();
foreach($results as $result){
    //var_dump($result);
    //echo "<br>";
    $trade_no = $result['trade_no'];
    add_balance($trade_no);
    echo "<br>";
    //exit();
}

function add_balance($trade_no){
    global $DB;
    var_dump($trade_no);
    $now = date("Y-m-d H:i:s");

    //添加到余额并记录
    try{
        $DB->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        $DB->beginTransaction();

        //查询订单
        $sql1 = "SELECT * FROM `pay_order` WHERE `trade_no` = '{$trade_no}' AND `status` = '1' AND `cashstatus` = '0' LIMIT 1; ";
        $result = $DB->query($sql1)->fetch();

        $pid = $result['pid'];
        $uid = $result['uid'];
        $channel_id = $result['mobile_url'];
        $money = round($result['money'] * 100,0);
        $order_type = $result['type'];

        //查询商户费率
        $sql2 = "SELECT * FROM `pay_user` WHERE `id` = '{$pid}';";
        $user = $DB->query($sql2)->fetch();
        switch($order_type){
            case 'alipay2':
            case 'alipay2qr':
            case 'alipay2_url':
            case 'tonglian3':
                $fee = $user['alipay_fee'];
                break;
            case 'wechat2':
            case 'wechat2qr':
            case 'wechat2_url':
            case 'tonglian2':
                $fee = $user['wxpay_fee'];
                break;
            case 'qqpay2':
            case 'qqpay2qr':
            case 'qqpay2_url':
                $fee = $user['qqpay_fee'];
                break;
            case 'alipayh5':
            case 'alipayh5_url':
            case 'alipay_mch5':
                $fee = $user['alipayh5_fee'];
                //$admin_fee = 0.0005;
                break;
            case 'wechath5':
                $fee = $user['wxpayh5_fee'];
                break;
            default:
                $fee = $user['fee'];
                break;
        }
        if(!isset($admin_fee)){
            $admin_fee = $fee - 0.015;
            $admin_fee = 0.006;
            if($order_type=="alipay" || $order_type=="alipayh5"){                           //原生账号0.0005服务费
                $admin_fee = 0.0005;
            }
            if(substr($result['buyer'],-4)=="@pdd"){                           //拚多多0.006
                $admin_fee = 0.006;
            }
        }
        $money_fee = round($money * $fee,0);
        //这里加入逻辑判断，如果不到1分，也要扣1分
        $money_fee_admin = round($money * $admin_fee,0);
		$money_fee_admin = $money_fee_admin==0?1:$money_fee_admin;
        $cashstatus = 1;

        //管理员扣除充值余额
        //if($money_fee_admin > 0){
            //初始化pay_recharge表
            $sql2 = "INSERT IGNORE INTO `pay_recharge` (`id`, `balance`, `createtime`, `update`, `income`, `payment`) VALUES ('0', '0', '{$now}', CURRENT_TIMESTAMP, '0', '0');";
            $DB->query($sql2);

            //查询管理员充值余额
            $sql4 = "SELECT * FROM `pay_recharge` WHERE `id` = '0' FOR UPDATE ";
            $row2 = $DB->query($sql4)->fetch();
            $balance_before = round($row2['balance'],0);
            $balance = $balance_before - $money_fee_admin;

            //插入充值金额消费记录
            $note1 = "扣管理员手续费($admin_fee)";
            $sql5 = "INSERT INTO `pay_recharge_history` (`id`, `userid`, `trade_no`, `type`, `balance_before`, `money`, `balance`, `createtime`, `update`, `ip`, `note1`, `note2`) VALUES (NULL, '0', '{$trade_no}@order@admin', '0', '{$balance_before}', '{$money_fee_admin}', '{$balance}', '{$now}', CURRENT_TIMESTAMP, '0.0.0.0', '{$note1}', NULL);";
            //更新充值余额
            $sql6 = "UPDATE `pay_recharge` SET `balance` = '{$balance}' WHERE `pay_recharge`.`id` = 0;";
            var_dump($sql6);
            //执行
            $DB->query($sql5);
            $DB->query($sql6);
            $cashstatus = 2;
        //}


        //根据是否是结算用户，计算余额或扣除充值金额
        if($user['type'] == 1){
            //结算用户
            //商户实入账金额
            $money_pid = $money - $money_fee;
            var_dump($money_pid);

            //初始化pay_balance表
            $sql2 = "INSERT IGNORE INTO `pay_balance` (`id`, `balance`, `createtime`, `update`, `income`, `payment`) VALUES ('{$pid}', '0', '{$now}', CURRENT_TIMESTAMP, '0', '0');";
            $DB->query($sql2);

            //查询当前余额
            $sql4 = "SELECT * FROM `pay_balance` WHERE `id` = '{$pid}' FOR UPDATE ";
            $row2 = $DB->query($sql4)->fetch();
            $balance_before = round($row2['balance'],0);
            $income = $row2['income'];
            $balance = $balance_before + $money_pid;

            //插入增加余额记录
            $income = $income + $money_pid;
            $sql5 = "INSERT INTO `pay_balance_history` (`id`, `userid`, `trade_no`, `type`, `balance_before`, `money`, `balance`, `createtime`, `update`, `ip`, `note1`, `note2`) VALUES (NULL, '{$pid}', '{$trade_no}', '1', '{$balance_before}', '{$money_pid}', '{$balance}', '{$now}', CURRENT_TIMESTAMP, '0.0.0.0', NULL, NULL);";
            //更新余额
            $sql6 = "UPDATE `pay_balance` SET `balance` = '{$balance}', `income` = '{$income}' WHERE `id` = '{$pid}';";
            var_dump($sql6);
            $DB->query($sql5);
            $DB->query($sql6);
        }elseif($user['type'] == 2){
            //非结算用户

            //初始化pay_recharge表
            $sql2 = "INSERT IGNORE INTO `pay_recharge` (`id`, `balance`, `createtime`, `update`, `income`, `payment`) VALUES ('{$pid}', '0', '{$now}', CURRENT_TIMESTAMP, '0', '0');";
            $DB->query($sql2);

            //查询充值余额
            $sql4 = "SELECT * FROM `pay_recharge` WHERE `id` = '{$pid}' FOR UPDATE ";
            $row2 = $DB->query($sql4)->fetch();
            $balance_before = round($row2['balance'],0);
            $income = $row2['income'];
            $balance = $balance_before - $money_fee;

            //插入充值金额消费记录
            $sql5 = "INSERT INTO `pay_recharge_history` (`id`, `userid`, `trade_no`, `type`, `balance_before`, `money`, `balance`, `createtime`, `update`, `ip`, `note1`, `note2`) VALUES (NULL, '{$pid}', '{$trade_no}@order', '0', '{$balance_before}', '{$money_fee}', '{$balance}', '{$now}', CURRENT_TIMESTAMP, '0.0.0.0', '扣费', NULL);";

            //更新充值余额
            $sql6 = "UPDATE `pay_recharge` SET `balance` = '{$balance}' WHERE `pay_recharge`.`id` = '{$pid}';";
            var_dump($sql6);
            $DB->query($sql5);
            $DB->query($sql6);
        }


        //是否存在代理，如果存在，则计算代理佣金
        if($uid != 0 || $uid != null){
            //代理商费率
            $sql2_uid = "SELECT * FROM `pay_user` WHERE `id` = '{$uid}';";
            $user_uid = $DB->query($sql2_uid)->fetch();
            switch($order_type){
                case 'alipay2':
                case 'alipay2qr':
                case 'alipay2_url':
                    $fee_uid = $user_uid['alipay_fee'];
                    break;
                case 'wechat2':
                case 'wechat2qr':
                case 'wechat2_url':
                    $fee_uid = $user_uid['wxpay_fee'];
                    break;
                case 'qqpay2':
                case 'qqpay2qr':
                case 'qqpay2_url':
                    $fee_uid = $user_uid['qqpay_fee'];
                    break;
                case 'alipayh5':
                case 'alipayh5_url':
                    $fee_uid = $user_uid['alipayh5_fee'];
                    //$admin_fee = 0.0005;
                    break;
                case 'wechath5':
                    $fee_uid = $user_uid['wxpayh5_fee'];
                    break;
                default:
                    $fee_uid = $user_uid['fee'];
                    break;
            }
            //差价
            //$fee_uid = $fee - $fee_uid;//费率差价     2018-12-12 注释
            $money_uid = round($money * $fee_uid,0);

            //如果代理收入大于0分
            if($money_uid > 0){
                var_dump($money_uid);
                //初始化
                $sql2 = "INSERT IGNORE INTO `pay_balance` (`id`, `balance`, `createtime`, `update`, `income`, `payment`) VALUES ('{$uid}', '0', '{$now}', CURRENT_TIMESTAMP, '0', '0');";
                $DB->query($sql2);

                //查询当前余额
                $sql4 = "SELECT * FROM `pay_balance` WHERE `id` = '{$uid}' FOR UPDATE ";
                $row3 = $DB->query($sql4)->fetch();
                $balance_before2 = round($row3['balance'],0);
                $income2 = $row3['income'] + $money_uid;
                $balance2 = $balance_before2 + $money_uid;

                //插入增加余额记录
                $sql7 = "INSERT INTO `pay_balance_history` (`id`, `userid`, `trade_no`, `type`, `balance_before`, `money`, `balance`, `createtime`, `update`, `ip`, `note1`, `note2`) VALUES (NULL, '{$uid}', 'fee@{$trade_no}', '1', '{$balance_before2}', '{$money_uid}', '{$balance2}', '{$now}', CURRENT_TIMESTAMP, '0.0.0.0', NULL, NULL);";
                //更新余额
                $sql8 = "UPDATE `pay_balance` SET `balance` = '{$balance2}', `income` = '{$income2}' WHERE `pay_balance`.`id` = '{$uid}';";
                $DB->query($sql7);
                $DB->query($sql8);
            }
        }

        //更新订单
        $sql10 = "UPDATE `pay_order` SET `cashstatus` = '{$cashstatus}', `payee_money` = '{$money_fee}', `is_jiesuan` = '{$user['type']}' WHERE `trade_no` = '{$trade_no}' AND `status` = '1' AND `cashstatus` = '0';";
        $DB->query($sql10);
        //更新通道的使用余额
        $row = $DB->query("SELECT `used_amount` FROM `pay_channel` WHERE `id`='{$channel_id}'")->fetch();
//        var_dump($row);
//        var_dump($money);
        $used_amount = $row["used_amount"] + $money;
        $sql11 = "UPDATE `pay_channel` SET `used_amount` = '{$used_amount}' WHERE `id`='{$channel_id}';";
        $DB->query($sql11);
        $DB->commit();
    }catch(Exception $e){
        $DB->rollBack();
        echo "Failed: ".$e->getMessage();
    }
}
?>
</body>
</html>