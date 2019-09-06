<?php
//异步消息通知测试URL
//http://71cn.com/easypay/SDK/fourpayurlback.php?money=0.02&name=%E6%B5%8B%E8%AF%95%E5%95%86%E5%93%81&out_trade_no=20180404104120140&pid=10000&trade_no=2018040410412433130&trade_status=TRADE_SUCCESS&type=alipay&sign=de8d180fed77acfa7239ce166a1e744b&sign_type=MD5

//ini_set('display_errors','On');
//error_reporting(E_ALL);


require_once("epay.config.php");
require_once("lib/epay_submit.class.php");


$file = fopen("test.txt","w");


$rec_getstr=$_REQUEST['out_trade_no']."是商户订单号; ".$_REQUEST['trade_no']."是支付平台返回的订单号; ".$_REQUEST['money']."是订单金额; ".$_REQUEST['pid']."是商户ID;  ".$_REQUEST['trade_status']."是订单状态; ".$_REQUEST['type']."是支付平台代码alipay/wxpay/QQpay; ".$_REQUEST['sign']."是签名.";

//$out_trade_no=isset($_REQUEST['out_trade_no'])?daddslashes($_REQUEST['out_trade_no']):exit('No out_trade_no!');
echo "平台将在交易成功后发送异步消息通知商户，异步通知消息携带以下参数：<br/>money=0.02&name=%E6%B5%8B%E8%AF%95%E5%95%86%E5%93%81&out_trade_no=20180404104120140&pid=10000&trade_no=2018040410412433130&trade_status=TRADE_SUCCESS&type=alipay&sign=de8d180fed77acfa7239ce166a1e744b&sign_type=MD5<br/><br/>其中<br/>";
echo $_REQUEST['out_trade_no']."是商户订单号<br/>";
echo $_REQUEST['trade_no']."是支付平台返回的订单号<br/>";
echo $_REQUEST['money']."是订单金额<br/>";
echo $_REQUEST['pid']."是商户ID<br/>";
echo $_REQUEST['trade_status']."是订单状态<br/>";
echo $_REQUEST['type']."是支付平台代码alipay/wxpay/QQpay<br/>";
echo $_REQUEST['sign']."是签名<br/>";
//注：以上显示的参数并未经过签名检验，有可能被伪造。需要在最下方签名成功后，才能确保是真实。

//获取所有参数
$queryArr=$_REQUEST;

//过滤掉签名和签名类型参数后按字母排序并拼接成字符串
/**
 * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
 * @param $para 需要拼接的数组
 * return 拼接完成以后的字符串
 */
//createLinkstring()函数在lib/epay_core.function.php定义
$prestr=createLinkstring(argSort(paraFilter($queryArr)));

//判断是否返回了商户ID
//$pid=intval($queryArr['pid']);
//if(empty($pid))sysmsg('PID不存在');

//此处还可以加上商户平台自己的业务逻辑；
//.....................................
//.....................................
//.....................................

//此处定义商户密钥
$userkey=$alipay_config['key'];

//判断对各参数的签名是否合法
echo "<br/>md5Verify()是验证签名函数；md5Sign()是签名函数；两者均在lib/epay_md5.function.php中定义";
if(!md5Verify($prestr, $queryArr['sign'], $userkey)){
	echo '<br/>签名MD5校验失败，请返回重试！原始信息prestr:'.$prestr.' sign:'.$queryArr['sign'].'key:'.$userkey;
	$mysign = md5Sign($prestr, $userkey);
	echo '<br/>正确的签名应该是'.$mysign;
	echo fwrite($file,"MD5 sign error!".$rec_getstr);	
}else{
	echo fwrite($file,"MD5 sign succeed!".$_REQUEST['sign'].$rec_getstr);		
	echo '<br/>签名成功<br/>您可以试着修改一下地址栏中的某个参数值，再访问本程序，看看签名是否还是成功的。';
}

fclose($file);

?>