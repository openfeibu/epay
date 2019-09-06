<?php
namespace epay;

class cron{
    //商户上分
    public static function order_pid($trade_no){
        global $DB;
        var_dump($trade_no);
        $now = date("Y-m-d H:i:s");
        try{
            $DB->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
            $DB->beginTransaction();

            //查询订单
            $sql1 = "SELECT * FROM `pay_order` WHERE `trade_no` = :trade_no AND `status` = '1' AND `cashstatus` = '0' LIMIT 1; ";
            $result = $DB->query($sql1)->fetch();

        }catch(\Exception $e){

        }
    }
    //代理上分
    function order_uid($trade_no){
        global $DB;
        var_dump($trade_no);
        $now = date("Y-m-d H:i:s");

        //给代理补余额
        try{
            $DB->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $DB->beginTransaction();

            //查询订单
            $sql1 = "SELECT * FROM `pay_order` WHERE `trade_no` = '{$trade_no}' AND `status` = '1' AND `cashstatus` = '0' LIMIT 1; ";
            $result = $DB->query($sql1)->fetch();

            $pid = $result['pid'];
            $money = round($result['money'] * 100,0);
            $order_type = $result['type'];

            //查询商户费率
            $sql2 = "SELECT * FROM `pay_user` WHERE `id` = '{$pid}';";
            $user = $DB->query($sql2)->fetch();
            switch($order_type){
                case 'alipay2':
                case 'alipay2qr':
                case 'alipay2_url':
                    $fee = $user['alipay_fee'];
                    break;
                case 'wechat2':
                case 'wechat2qr':
                case 'wechat2_url':
                    $fee = $user['wxpay_fee'];
                    break;
                case 'qqpay2':
                case 'qqpay2qr':
                case 'qqpay2_url':
                case 'alipay_py1':
                case 'alipay_py2':
                case 'alipay_py3':
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
            $uid = $user['uid'];
            $money = round($result['money'] * 100,0);




            $sql2_uid = "SELECT * FROM `pay_user` WHERE `id` = '{$uid}';";
            $user_uid = $DB->query($sql2_uid)->fetch();
            $fee_uid = $user_uid['fee'];
            //差价
            $fee_uid = $fee - $fee_uid;//费率差价
            $fee_uid = 0.01;
            $money_uid = round($money * $fee_uid,0);

            //如果代理收入大于0分
            if($money_uid > 0){
                var_dump($money_uid);
                //初始化
                $sql2 = "INSERT IGNORE INTO `pay_balance` (`id`, `balance`, `createtime`, `update`, `income`, `payment`) VALUES ('{$uid}', '0', '{$now}', CURRENT_TIMESTAMP, '0', '0');";
                $DB->exec($sql2);

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
                $DB->exec($sql7);
                $DB->exec($sql8);
            }

            //修改订单
            $sql = "UPDATE `pay_order` SET `uid` = '210' WHERE `trade_no` = '{$trade_no}'";
            var_dump($sql);
            $DB->query($sql);

            $DB->commit();
        }catch (Exception $e){
            $DB->rollBack();
            echo "Failed: " . $e->getMessage();
        }
    }


    //管理员上分
    public static function order_admin($trade_no){
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
            $money = round($result['money'] * 100,0);
            $order_type = $result['type'];

            //查询商户费率
            $sql2 = "SELECT * FROM `pay_user` WHERE `id` = '{$pid}';";
            $user = $DB->query($sql2)->fetch();
            switch($order_type){
                case 'alipay2':
                case 'alipay2qr':
                case 'alipay2_url':
                    $fee = $user['alipay_fee'];
                    break;
                case 'wechat2':
                case 'wechat2qr':
                case 'wechat2_url':
                    $fee = $user['wxpay_fee'];
                    break;
                case 'qqpay2':
                case 'qqpay2qr':
                case 'qqpay2_url':
                case 'alipay_py1':
                case 'alipay_py2':
                case 'alipay_py3':
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
            }
            $money_fee = round($money * $fee,0);
            $money_fee_admin = round($money * $admin_fee,0);
            $cashstatus = 1;

            //管理员扣除充值余额
            if($money_fee_admin > 0){
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
            }


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
                    case 'alipay_py1':
                    case 'alipay_py2':
                    case 'alipay_py3':
                        $fee_uid = $user_uid['qqpay_fee'];
                        break;
                    case 'alipayh5':
                    case 'alipayh5_url':
                    case 'alipay_mch5':
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
                $fee_uid = $fee - $fee_uid;//费率差价
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
            $DB->commit();
        }catch(Exception $e){
            $DB->rollBack();
            echo "Failed: ".$e->getMessage();
        }
    }
}