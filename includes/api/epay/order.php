<?php
namespace epay;

//订单处理类

class order{
    //创建订单
    public static function create($order = array()){
        global $DB;
        $sql = "INSERT INTO `pay_order` (`trade_no`,`out_trade_no`,`notify_url`,`return_url`,`type`,`pid`,`uid`,`addtime`,`name`,`money`,`status`, `data`, `attach`) 
VALUES (:trade_no,:out_trade_no,:notify_url,:return_url,:type,:pid,:uid,:now,:name,:money,'0', :data, :attach )";
        try{
            $re = $DB->prepare($sql);
            $re->execute($order);
            $result['code'] = 1;
            $result['msg'] = "创建成功";
        }catch(\Exception $e){
            $result['code'] = 0;
            $result['msg'] = "创建订单失败，请返回重试！{$e->errorInfo[2]}";
            $result['error'] = $e->errorInfo;
        }
        return $result;
    }

    //创建订单（使用uuid）
    public static function create2($order = array()){
        global $DB;
        //查询是否有订单
        $sql = "SELECT count(*) FROM `pay_order` WHERE `trade_no` = :trade_no ;";
        $re = $DB->prepare($sql);
        $re->execute(["trade_no" => $order['trade_no']]);
        $count = $re->fetch();
        if($count[0] > 0){
            //平台订单重复，有此订单，直接返回null;
            $result['code'] = 0;
            $result['msg'] = "平台订单号重复".$order['trade_no'];
            $result['error'] = "平台订单号重复";
            return $result;
        }

        $sql = "SELECT count(*) FROM `pay_order` WHERE `pid` = :pid AND `out_trade_no` = :out_trade_no ;";
        $re = $DB->prepare($sql);
        $re->execute(["pid" => $order['pid'],"out_trade_no" => $order['out_trade_no']]);
        $count = $re->fetch();
        if($count[0] > 0){
            //商户订单号重复，有此订单，直接返回null;
            return 1;
        }

        $sql = "INSERT INTO `pay_order` (`trade_no`,`out_trade_no`,`notify_url`,`return_url`,`type`,`pid`,`uuid`,`agentuuid`,`uid`,`addtime`,`name`,`money`,`status`, `data`, `attach`) 
VALUES (:trade_no,:out_trade_no,:notify_url,:return_url,:type,:pid,:uuid,:agentuuid,:uid,:now,:name,:money,'0', :data, :attach )";
        try{
            $re = $DB->prepare($sql);
            $re->execute($order);
            $result['code'] = 1;
            $result['msg'] = "创建成功";
        }catch(\Exception $e){
            //var_dump($e);
            $result['code'] = 0;
            $result['msg'] = "创建订单失败，请返回重试！{$e->errorInfo[2]}";
            $result['error'] = $e->errorInfo;
        }
        return $result;
    }

    public static function delete($trade_no){
        return false;
    }

    //修改订单
    public static function paid($trade_no,$money,$endtime,$buyer,$userid = ''){
        global $DB;
        $result = array();
        $result['code'] = 0;
        //查找订单信息
        $sql = "SELECT * FROM `pay_order` WHERE `trade_no` = :trade_no LIMIT 1";
        $re = $DB->prepare($sql);
        $re->execute(["trade_no"=>$trade_no]);
        //$row = $re->fetch(\PDO::FETCH_ASSOC);
        $row = $re->fetch();
        if(!$row){
            //找不到订单号
            $result['code'] = 0;
            $result['trade_no'] = $trade_no;
            $result['msg'] = "找不到订单号";
            return $result;
        }

        //验证付款金额
        if($row['money'] != $money){
            $result['code'] = 0;
            $result['trade_no'] = $trade_no;
            $result['msg'] = "金额不对";
            return $result;
        }

        //查看是否已完成
        if($row['status'] == '1'){
            $result['code'] = 1;
            $result['trade_no'] = $trade_no;
            $result['msg'] = "该订单已完成";
            return $result;
        }

        //订单未完成，执行更新操作
        if($row['status'] == '0'){
            if($userid == ''){
                $userid = $row['pid'];
            }
            //付款完成后，支付系统发送该交易状态通知
            $sql = "UPDATE `pay_order` SET `status` = :status,`endtime` = :endtime, `buyer` = :buyer WHERE `pid` = :userid AND `trade_no`= :trade_no;";
            $re = $DB->prepare($sql);
            $data = array(
                "status" =>  "1",
                "endtime" => $endtime,
                "buyer" => $buyer,
                "userid" => $userid,
                "trade_no" => $trade_no,
            );
            $re->execute($data);
            $result['code'] = 1;
            $result['trade_no'] = $trade_no;
            $result['msg'] = "操作成功";
        }else{
            $result['code'] = 0;
            $result['trade_no'] = $trade_no;
            $result['msg'] = "订单已关闭，或其他错误，订单标识为：{$row['status']}";
        }
        return $result;
    }

    //查询订单
    public static function find($trade_no){
        global $DB;
        $data["trade_no"] = $trade_no;
        $sql = "SELECT * FROM `pay_order` WHERE `trade_no` = :trade_no; ";
        try{
            $re = $DB->prepare($sql);
            $re->execute($data);
            $row = $re->fetch();
            if($row){
                return $row;
            }else{
                return false;
            }
        }catch(\Exception $e){
            $result['code'] = 0;
            $result['msg'] = "查询失败！{$e->errorInfo[2]}";
            return false;
        }
    }

    //查询完成订单
    public static function findFinish($trade_no){
        global $DB;
        $data["trade_no"] = $trade_no;
        $sql = "SELECT * FROM `pay_order` WHERE `status`='1' AND buyer<>'' AND endtime<>'0000-00-00 00:00:00' AND `trade_no` = :trade_no; ";
        try{
            $re = $DB->prepare($sql);
            $re->execute($data);
            $row = $re->fetch();
            if($row){
                return $row;
            }else{
                return false;
            }
        }catch(\Exception $e){
            $result['code'] = 0;
            $result['msg'] = "查询失败！{$e->errorInfo[2]}";
            return false;
        }
    }

	//查询十分钟之内订单
    public static function findtenminute($trade_no){
        global $DB;
        $data["trade_no"] = $trade_no;
        $sql = "SELECT * FROM `pay_order` WHERE addtime>=DATE_SUB(NOW(),INTERVAL 10 MINUTE) AND `trade_no` = :trade_no; ";
        try{
            $re = $DB->prepare($sql);
            $re->execute($data);
            $row = $re->fetch();
            if($row){
                return $row;
            }else{
                return false;
            }
        }catch(\Exception $e){
            $result['code'] = 0;
            $result['msg'] = "查询失败！{$e->errorInfo[2]}";
            return false;
        }
    }

    //查询五分钟之内订单
    public static function findtenminute1($trade_no){
        global $DB;
        $data["trade_no"] = $trade_no;
        $sql = "SELECT * FROM `pay_order` WHERE addtime>=DATE_SUB(NOW(),INTERVAL 5 MINUTE) AND `trade_no` = :trade_no; ";
        try{
            $re = $DB->prepare($sql);
            $re->execute($data);
            $row = $re->fetch();
            if($row){
                return $row;
            }else{
                return false;
            }
        }catch(\Exception $e){
            $result['code'] = 0;
            $result['msg'] = "查询失败！{$e->errorInfo[2]}";
            return false;
        }
    }
}