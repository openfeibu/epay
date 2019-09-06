<?php
exit();
/* *
 * 功能：支付异步通知页面
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
mch_id
out_trade_no
service
tradeStatus
sign


 *************************页面功能说明*************************
 * 创建该页面文件时，请留心该页面文件中无任何HTML代码及空格。
 * 该页面不能在本机电脑测试，请到服务器上做测试。请确保外部可以访问该页面。
 * 该页面调试工具请使用写文本函数logResult，该函数已被默认关闭，见alipay_notify_class.php中的函数verifyNotify
 */
require_once('./includes/common.php');//获取核心数据
//require_once(SYSTEM_ROOT."epay/epay.config.php");
require_once(SYSTEM_ROOT."cspay/epay.config.php");

require_once(SYSTEM_ROOT."cspay/epay_submit.class.php");

/**************************请求参数**************************/


//构造要请求的参数数组，无需改动
$parameter = array(
    "mch_id"       => trim($alipay_config['partner']),
    "out_trade_no" => time(),
);

//建立请求
$alipaySubmit = new AlipaySubmit($alipay_config);
$html_text = $alipaySubmit->buildRequestbalanceRequestCurl($parameter);

echo $html_text."<br/>";
print_r($html_text);


//print_r($data);	

//echo "QR is ".$html_text["payinfo"];	
?>