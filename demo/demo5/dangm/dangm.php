<?php
	header("Content-type:text/html;charset=utf-8");
	require_once 'AppConfig.php';
	require_once 'AppUtil.php';

	
	function pay(){
		$params = array();
	    $params["appid"] = AppConfig::APPID;
	    $params["amt"] = $_REQUEST["money"];
	    $params["c"] = "FRHHDAfD";
	    $params["oid"] = date("YmdHis").rand(100,999);//订单号,自行生成
	    $params["trxreserve"] = "abc";
        $params["returl"] = "http://172.16.2.46:8080/vo-apidemo/OrderServlet";
		
		$qianmc="amt=" .$params["amt"] . "&appid=" .AppConfig::APPID. "&c=FRHHDAfD&key=" .AppConfig::APPKEY. "&oid=" .$params["oid"]. "&returl=" .$params["returl"]. "&trxreserve=abc";
		$qianming=md5($qianmc);
		
	    $params["sign"] = strtoupper($qianming);//签名
	    
	    $paramsStr = AppUtil::ToUrlParams($params);
	    $url = AppConfig::APIURL ;
	    $rsp = $url ."?" . $paramsStr;
	    header("location: ".$rsp);
	    
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