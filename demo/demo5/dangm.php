<html>
<head> <link href="./css/pay.css" rel="stylesheet" media="screen">
<title>微信当面付</title>
</head>
<body>
<center>
 <span class="ico_logico"></span><br><br>
<?php
	header("Content-type:text/html;charset=utf-8");
	
	require_once 'AppUtil.php';
	require_once '../../includes/api/init.php';

	$pidx=$_GET["pid"];
	$trxamt=$_GET["money"]*100;
	$pid="99@".$_GET["pid"];
	//$goodsid=$_GET["name"];
		$aa=rand(0,1);
		if($aa==1){
		$sql = "SELECT * FROM `pay_channel` WHERE `appid` = '{$pid}' and `type` = 'tonglian' and `status` = '1' order by id";
		}else{
		$sql = "SELECT * FROM `pay_channel` WHERE `appid` = '{$pid}' and `type` = 'tonglian' and `status` = '1' order by id DESC";	
		}
     $userrow = $DB->query($sql)->fetch();
	 $pidy=$userrow["id"]. rand(111,999);
	
		$params = array();
	    $params["appid"] = $userrow["body"];
	    $params["amt"] = $trxamt;
	    $params["c"] = $userrow["subject"];
	    $params["oid"] = $_GET["tradeno"];//订单号,自行生成
	    $params["trxreserve"] = $pidy;
        $params["returl"] = "http://168.tihou.com/api/notify6.php";
		
		$qianmc="amt=" .$trxamt . "&appid=" .$userrow["body"]. "&c=" .$userrow["subject"]. "&key=" .$userrow["private_key"]. "&oid=" .$params["oid"]. "&returl=" .$params["returl"]. "&trxreserve=" .$pidy;
		//echo $qianmc;
		$qianming=md5($qianmc);
		
	    $params["sign"] = strtoupper($qianming);//签名
	    
	    $paramsStr = AppUtil::ToUrlParams($params);
	    $url = "https://syb.allinpay.com/sappweb/usertrans/cuspay";
	    $rsp = $url ."?" . $paramsStr;
		$wangzhi=urlencode($rsp);
	    echo "<img src='http://pay.weimifu.net/api/qrcode.php?data=" .$wangzhi. "'>";
		echo '<br>金额:<font size="5" color="red">'  .$_GET["money"]. '元</font><br>';
		echo "<br><h3>请使用微信扫一扫扫码支付</h3>";
		echo '<br>如果你是手机页面打开，保存二维码到手机然后在微信扫一扫中选择“相册”即可支付<br><span style="color:rgba(255,0,0,1.00)">（每个二维码只能支付一次，二维码大约需要两秒钟加载）</span>';
	    
	
?>
</center>
<script language="javascript" type="text/javascript"> 



setTimeout("javascript:location.href='https://pay.weixin.qq.com'", 50000); 
</script>
</body></html>