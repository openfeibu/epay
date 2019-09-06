<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>云支付即时到账交易接口</title>
</head>
<?php
require './includes/common.php';
$out_trade_no=daddslashes($_GET['out_trade_no']);
$sitename=daddslashes($_GET['sitename']);
$row=$DB->query("SELECT * FROM pay_order WHERE out_trade_no='{$out_trade_no}' limit 1")->fetch();
require_once(SYSTEM_ROOT."epay/epay.config.php");
require_once(SYSTEM_ROOT."epay/epay_submit.class.php");

/**************************请求参数**************************/


//构造要请求的参数数组，无需改动
$parameter = array(
		"pid" => trim($alipay_config['partner']),
		"type" => $row['type'],
		"notify_url"	=> 'http://'.$_SERVER['HTTP_HOST'].'/epay_notify.php',
		"return_url"	=> 'http://'.$_SERVER['HTTP_HOST'].'/epay_return.php',
		"out_trade_no"	=> $out_trade_no,
		"name"	=> $row['name'],
		"money"	=> $row['money'],
		"sitename"	=> $sitename
);

//建立请求
$alipaySubmit = new AlipaySubmit($alipay_config);
$html_text = $alipaySubmit->buildRequestForm($parameter);
echo $html_text;

?>
</body>
</html>