<?php
namespace epay;

class recharge{
    //初始化pay_recharge表
    public static function create($data = array()){
        global $DB;
        $sql2 = "INSERT IGNORE INTO `pay_recharge` (`id`,`uuid`, `balance`, `createtime`, `update`, `income`, `payment`) VALUES (:userid,:uuid, 0, :now, CURRENT_TIMESTAMP, 0, 0);";
        $re = $DB->prepare($sql2);
        $re->execute($data);
        return $re;
    }
    /*
     * 添加充值金额
     */
    /*
 * 扣除充值余额
 */
    public static function add2($trade_no,$userid,$money,$note1){
        global $DB;
        $now = date("Y-m-d H:i:s");
        try{
            $DB->setAttribute(\PDO::ATTR_ERRMODE,\PDO::ERRMODE_EXCEPTION);
            $DB->setAttribute(\PDO::ATTR_EMULATE_PREPARES,false);
            $DB->beginTransaction();

            //查询管理员充值余额
            $sql4 = "SELECT * FROM `pay_recharge` WHERE `id` = :userid FOR UPDATE ";
            $re = $DB->prepare($sql4);
            $re->execute([":userid" => $userid]);
            $row = $re->fetch();
            $balance_before = round($row['balance'],0);
            $balance = $balance_before + $money;

            //插入充值金额消费记录
            $sql5 = "INSERT INTO `pay_recharge_history` (`id`, `userid`, `trade_no`, `type`, `balance_before`, `money`, `balance`, `createtime`, `update`, `ip`, `note1`, `note2`) VALUES (NULL, '0', :trade_no, '1', '{$balance_before}', :money, '{$balance}', '{$now}', CURRENT_TIMESTAMP, '0.0.0.0', :note1, NULL);";
            //更新充值余额
            $sql6 = "UPDATE `pay_recharge` SET `balance` = '{$balance}' WHERE `id` = :userid;";
            var_dump($sql6);
            //执行
            $re = $DB->prepare($sql5);
            $re->execute(["trade_no" => $trade_no,"money" => $money, "note1" => $note1]);
            $re = $DB->prepare($sql6);
            $re->execute(["userid" => $userid]);
        }catch(Exception $e){
            $DB->rollBack();
            echo "Failed: ".$e->getMessage();
        }
    }

    public static function add($trade_no){
        global $DB;
        $now = date("Y-m-d H:i:s");
        //根据out_trade_no，查找出商户ID
        $sql1 = "SELECT * FROM `pay_recharge_record` WHERE `out_trade_no` = '{$out_trade_no}' LIMIT 1";
        $row = $DB->query($sql1)->fetch();
        if(!$row){
            exit("fail");
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

    /*
     * 扣除充值余额
     */
    public static function minus($trade_no,$userid,$money,$note1){
        global $DB;
        $now = date("Y-m-d H:i:s");
        try{
            $DB->setAttribute(\PDO::ATTR_ERRMODE,\PDO::ERRMODE_EXCEPTION);
            $DB->setAttribute(\PDO::ATTR_EMULATE_PREPARES,false);
            $DB->beginTransaction();

            //查询管理员充值余额
            $sql4 = "SELECT * FROM `pay_recharge` WHERE `id` = :userid FOR UPDATE ";
            $re = $DB->prepare($sql4);
            $re->execute([":userid" => $userid]);
            $row = $re->fetch();
            $balance_before = round($row['balance'],0);
            $balance = $balance_before - $money;
            //var_dump($balance);

            //插入充值金额消费记录
            $sql5 = "INSERT INTO `pay_recharge_history` (`id`, `userid`, `trade_no`, `type`, `balance_before`, `money`, `balance`, `createtime`, `update`, `ip`, `note1`, `note2`) VALUES (NULL, '0', :trade_no, '0', '{$balance_before}', :money, '{$balance}', '{$now}', CURRENT_TIMESTAMP, '0.0.0.0', :note1, NULL);";
            //var_dump($sql5);
            //更新充值余额
            $sql6 = "UPDATE `pay_recharge` SET `balance` = '{$balance}' WHERE `id` = :userid;";
            //var_dump($sql6);
            //执行
            $re = $DB->prepare($sql5);
            $re->execute([":trade_no" => $trade_no,":money" => $money, ":note1" => $note1]);
            $re = $DB->prepare($sql6);
            $re->execute(["userid" => $userid]);
            $DB->commit();
            return json_encode(array("balance_before"=>$balance_before,"balance"=>$balance));
        }catch(Exception $e){
            $DB->rollBack();
            echo "Failed: ".$e->getMessage();
        }
    }

}