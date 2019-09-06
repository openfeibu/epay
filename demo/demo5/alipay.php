<html>
<head> <link href="./css/pay.css" rel="stylesheet" media="screen">
</head>
<body class="body">
<center>
 <span class="ico_logico2"><img src="./Images/logo_alipay.jpg"></span><br><br>
<?php
	header("Content-type:text/html;charset=utf-8");
	
	require_once 'AppUtil.php';
	require_once '../../includes/api/init.php';

	$pidx=$_GET["pid"];
	$trxamt=$_GET["money"]*100;
	$goodsid=$_GET["name"];
	if(!isset($trxamt)){
		echo "信息不完整";
	}
	
	//file_put_contents("../../config/cache/22.txt",$appid);
	
		$pid="99@".$_GET["pid"];
		$sql = "SELECT * FROM `pay_channel` WHERE `appid` = '{$pid}' and `type` = 'tonglian' and `status` = '1' limit 1";
     $userrow = $DB->query($sql)->fetch();
	
		$params = array();
		$params["cusid"] = $userrow["public_key"];
	    $params["appid"] = $userrow["body"];
	    $params["version"] = "11";
	    $params["trxamt"] = $trxamt;
	    $params["reqsn"] = $_GET["tradeno"];
	    $params["paytype"] = "A01";
	    $params["randomstr"] = "1450432107647";
	    $params["body"] = $goodsid;
	    $params["remark"] = $pidx;
	    $params["acct"] = "";
	    $params["limit_pay"] = "no_credit";
		$params["idno"] = "";
		$params["truename"] = "";
		$params["asinfo"] = "";
        $params["notify_url"] = "http://168.tihou.com/api/notify5.php";
	    $params["sign"] = AppUtil::SignArray($params,$userrow["private_key"]);//签名
	    
	    $paramsStr = AppUtil::ToUrlParams($params);
	    $url = "https://vsp.allinpay.com/apiweb/unitorder/pay";
	    $rsp = request($url, $paramsStr);
	    
	    $rspArray = json_decode($rsp, true); 
		
	    //var_dump($rspArray);
	    	echo '<img src="http://pay.weimifu.net/api/qrcode.php?data=' . $rspArray["payinfo"] . '"><br>金额:<font size="5" color="red">'  .$_GET["money"]. '元</font><br><h3>请使用支付宝扫一扫支付</h3><br>如果你是手机页面打开，保存二维码到手机然后在支付宝扫一扫中选择“相册”即可支付<br><span style="color:rgba(255,0,0,1.00)">（每个二维码只能支付一次,二维码大约需要两秒钟加载）</span>';
	   

	
	
	
	
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
	

?>
</center>
<script language="javascript" type="text/javascript"> 



setTimeout("javascript:location.href='https://www.alipay.com/'", 50000); 
</script>
</body></html>
