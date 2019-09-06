<?php
 require_once __DIR__.DIRECTORY_SEPARATOR.'../../includes/api/init.php';
//file_put_contents("../../config/cache/22.txt",json_encode( $_REQUEST, JSON_UNESCAPED_UNICODE ));
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
   $trade_no=$_REQUEST["orderid"];
   $buyer=$_REQUEST["returncode"];
  
		$now=date("Y-m-d H:i:s");
	
		ksort($ReturnArray);
		reset($ReturnArray);
        $md5str = "";
        foreach ($ReturnArray as $key => $val) {
            $md5str = $md5str . $key . "=" . $val . "&";
        }
        $sign = strtoupper(md5($md5str . "key=" . $Md5key)); 
		
        if ($sign == $_REQUEST["sign"]) {
            if ($_REQUEST["returncode"] == "00") {
				$sql = "update `pay_order` set `status` ='1',`endtime` ='{$now}', `buyer` = '{$buyer}' where `pid` = '{$pid}' and `trade_no`='{$trade_no}'";
                $DB->query($sql)->fetch();
				  
				   exit("OK");
            }
        }else{
			
			echo "加密错误";
		}

?>