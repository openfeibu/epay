<?php
	header("Content-type:text/html;charset=utf-8");
	require_once 'AppConfig.php';
	require_once 'AppUtil.php';
	require_once '../../includes/api/init.php';
$pid="99@".$_GET["pid"];
	$pidx=$_GET["pid"];
	$trxamt=$_GET["money"]*100;
	$goodsid=$_GET["name"];
	
	$sql = "SELECT * FROM `pay_channel` WHERE `appid` = '{$pid}' and `type` = 'tonglian' and `status` = '1' limit 1";
     $userrow = $DB->query($sql)->fetch();
	 $cusid=$userrow["public_key"];
	$appid=$userrow["body"];
	$uukey=$userrow["private_key"];
	
	function pay(){
		$params = array();
		$params["cusid"] = $cusid;
	    $params["appid"] = $appid;
	    $params["version"] = "11";
	    $params["trxamt"] = $trxamt;
	    $params["reqsn"] = $_GET["orderid"];//订单号,自行生成
	    $params["paytype"] = "W01";
	    $params["randomstr"] = "1450432107647";//
	    $params["body"] = $goodsid;
	    $params["remark"] = "备注信息";
	    $params["acct"] = "";
	    $params["limit_pay"] = "no_credit";
		$params["idno"] = "";
		$params["truename"] = "";
		$params["asinfo"] = "";
        $params["notify_url"] = "http://168.tihou.com/api/notify5.php";
	    $params["sign"] = AppUtil::SignArray($params,$uukey);//签名
	    
	    $paramsStr = AppUtil::ToUrlParams($params);
	    $url = AppConfig::APIURL . "/pay";
	    $rsp = request($url, $paramsStr);
	    echo "请求返回:".$rsp;
	    echo "<br/>";
	    $rspArray = json_decode($rsp, true); 
		
	    if(validSign($rspArray)){
	    	echo $rspArray["payinfo"];
	    }
	    
	}
	
	
	
	
	//发送请求操作仅供参考,不为最佳实践
	function request($url,$params){
		$ch = curl_init();
		$this_header = array("content-type: application/x-www-form-urlencoded;charset=UTF-8");
		curl_setopt($ch,CURLOPT_HTTPHEADER,$this_header);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		 
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);//如果不加验证,就设false,商户自行处理
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		 
		$output = curl_exec($ch);
		curl_close($ch);
		return  $output;
	}
	
	//验签
	function validSign($array){
		if("SUCCESS"==$array["retcode"]){
			$signRsp = strtolower($array["sign"]);
			$array["sign"] = "";
			$sign =  strtolower(AppUtil::SignArray($array, AppConfig::APPKEY));
			if($sign==$signRsp){
				return TRUE;
			}
			else {
				echo "验签失败:".$signRsp."--".$sign;
			}
		}
		else{
			echo $array["retmsg"];
		}
		
		return FALSE;
	}
	
	pay();
	//cancel();
	//refund();
	//query();
?>