<?php

   $ReturnArray = array( // 返回字段
            "memberid" => $_REQUEST["memberid"], // 商户ID
            "orderid" =>  $_REQUEST["orderid"], // 订单号
            "amount" =>  $_REQUEST["amount"], // 交易金额
            "datetime" =>  $_REQUEST["datetime"], // 交易时间
            "returncode" => $_REQUEST["returncode"],
			"transaction_id" => $_REQUEST["transaction_id"]
        );
       $pid=$_REQUEST["attach"];
	   $sql = "SELECT * FROM `pay_channel` WHERE `appid` = '{$pid}' and `status` ='1' and `type` ='xinyi' limit 1";
$userinfo = $DB->query($sql)->fetch();
        $Md5key = $userinfo["private_key"];
        

		
		///////////////////////////////////////////////////////
		ksort($ReturnArray);
        reset($ReturnArray);
        $md5str = "";
        foreach ($ReturnArray as $key => $val) {
            $md5str = $md5str . $key . "=>" . $val . "&";
        }
        $sign = strtoupper(md5($md5str . "key=" . $Md5key)); 
		///////////////////////////////////////////////////////
        if ($sign == $_REQUEST["sign"]) {
            if ($_REQUEST["returncode"] == "00") {
				   $str = "交易成功！订单号：".$_REQUEST["orderid"];
                  $str.="金额：".$_REQUEST["amount"];
				   exit($str);
            }
        }

?>