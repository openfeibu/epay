	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>

		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<meta http-equiv="Content-Language" content="zh-CN"/>
		<meta http-equiv="Expires" CONTENT="0">        
		<meta http-equiv="Cache-Control" CONTENT="no-cache">        
		<meta http-equiv="Pragma" CONTENT="no-cache">
		<title>通联收银宝网关支付-商户接口范例-支付请求信息签名</title>
		<link href="css.css" rel="stylesheet" type="text/css">
	</head>
	<body onload="sub();">	
	<?PHP
	header("Content-type:text/html;charset=utf-8");
	require_once '../../includes/api/init.php';
	require_once 'AppConfig.php';
	require_once 'AppUtil.php';
	//页面编码要与参数inputCharset一致，否则服务器收到参数值中的汉字为乱码而导致验证签名失败。	
	$pid="99@".$_GET["pid"];
	$pidx=$_GET["pid"];
	$sql = "SELECT * FROM `pay_channel` WHERE `appid` = '{$pid}' and `type` = 'tonglian' and `status` = '1' limit 1";
     $userrow = $DB->query($sql)->fetch();
	$orderid=$_GET["orderid"];
	$trxamt=$_GET["money"]*100;
	$goodsid=$_GET["name"];
	$returl=$_GET["returl"];
	$notifyurl="http://168.tihou.com/api/notify5.php";
	$validtime=5;

	$cusid=$userrow["public_key"];
	$appid=$userrow["body"];
	$uukey=$userrow["private_key"];
	
	$charset=AppConfig::CHARSET;
	$randomstr="123456789";

	$params = array();
	$params["cusid"] = $cusid;
	$params["appid"] = $appid;
	$params["charset"] = $charset;
	$params["returl"] = $returl;
	$params["notify_url"] = $notifyurl;
	$params["body"] = $goodsid;
	$params["remark"] = $pidx;
	$params["trxamt"] = $trxamt;
	$params["reqsn"] = $orderid;
	$params["randomstr"] = $randomstr;
	$params["validtime"] =$validtime;
	$params["version"] ="12";
	
	//签名，设为signMsg字段值。
	$signMsg = AppUtil::SignArray($params,$uukey);//签名
	
	
	$params['key'] = AppConfig::APPKEY;// 将key放到数组中一起进行排序和组装
	ksort($params);
	$bufSignSrc = AppUtil::ToUrlParams($params);
	
	?>
	
	<!--
		1、订单可以通过post方式或get方式提交，建议使用post方式；
		   提交支付请求可以使用http或https方式，建议使用https方式。
		2、通联收银宝网关地址、商户号及key值，在接入测试时由通联提供；
		   通联收银宝网关地址、商户号，在接入生产时由通联提供，key值在通联收银宝商服服务平台上设置,https://vsp.allinpay.com。
	-->
	<!--================= post 方式提交支付请求 start =====================-->
	<!--================= 测试地址,生产地址请参考在线接口文档https://aipboss.allinpay.com/know/devhelp/main.php?pid=13=====================-->
	<!--=================  =====================-->
	<form name="form2" action="https://syb.allinpay.com/apiweb/h5unionpay/unionorder" id="form2" method="post">
		<input type="hidden" name="cusid" id="cusid" value="<?=$cusid?>" />
		<input type="hidden" name="appid" id="appid" value="<?=$appid?>"/>
		<input type="hidden" name="version" id="version" value="12"/>
		<input type="hidden" name="charset" id="charset" value="<?=$charset?>" />
		<input type="hidden" name="returl" id="returl" value="<?=$returl?>"/>
		<input type="hidden" name="notify_url" id="notify_url" value="<?=$notifyurl?>" />
		<input type="hidden" name="body" id="body" value="<?=$goodsid?>"/>
		<input type="hidden" name="remark" id="remark" value="<?=$pidx?>"/>
		<input type="hidden" name="trxamt" id="trxamt" value="<?=$trxamt?>"/>
		<input type="hidden" name="reqsn" id="reqsn" value="<?=$orderid?>" />
		<input type="hidden" name="randomstr" id="randomstr" value="<?=$randomstr ?>" />
		<input type="hidden" name="validtime" id="validtime" value="<?=$validtime?>"/>
	<input type="hidden" name="sign" id="sign" value="<?=$signMsg?>" />
	<!--================= post 方式提交支付请求 end =====================-->
	</form>
<script>

     function sub(){ 
         var a = document.getElementById('form2');
        
         a.submit();
    }
setTimeout(sub,500);//以毫秒为单位的.1000代表一秒钟.根据你需要修改这个时间. 

</script>
	</body>
	</html>