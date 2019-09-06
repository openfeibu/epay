<?php
require_once __DIR__.DIRECTORY_SEPARATOR."../api/init.php";
   $returnArray = array( // 返回字段
            "memberid" => $_REQUEST["memberid"], // 商户ID
            "orderid" =>  $_REQUEST["orderid"], // 订单号
            "amount" =>  $_REQUEST["amount"], // 交易金额
            "datetime" =>  $_REQUEST["datetime"], // 交易时间
            "transaction_id" =>  $_REQUEST["transaction_id"], // 支付流水号
            "returncode" => $_REQUEST["returncode"],
        );
        $md5key = "8get99fuxfowtnt1lt3gglo95x3gz24g";
        ksort($returnArray);
        reset($returnArray);
        $md5str = "";
        foreach ($returnArray as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }
        $sign = strtoupper(md5($md5str . "key=" . $md5key));
        if ($sign == $_REQUEST["sign"]) {
            if ($_REQUEST["returncode"] == "00") {
                   $str = "交易成功！订单号：".$_REQUEST["orderid"];
                   //file_put_contents("../../config/cache/success.txt",serialize($_REQUEST)."\n", FILE_APPEND);
                   //写入库，订单完成
                   $now = date("Y-m-d H:i;s");
                   $sql = "update `pay_order` set `status` ='1',`endtime` ='{$now}', `buyer` = '{$returnArray["transaction_id"]}@{$returnArray["memberid"]}@{$returnArray["amount"]}' where `trade_no`='{$returnArray["orderid"]}';";
                   if($DB->query($sql)){
                       exit("ok");
                   };


            }
        }
?>